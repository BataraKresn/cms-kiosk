#!/usr/bin/env python3
"""
CMS-Coordinated Device Ping Service

This is an UPDATED version of the ping service that coordinates with the CMS backend
to prevent race conditions and status flapping.

Key improvements:
1. Uses CMS API instead of direct DB writes
2. Respects CMS as the authoritative source of device state
3. Batch processing for efficiency
4. Only pings devices that need it (based on CMS)

Installation:
    pip install requests

Configuration:
    Set environment variables:
    - CMS_URL: Base URL of CMS (e.g., http://localhost)
    - CMS_API_TOKEN: Admin API token for authentication
    
Usage:
    python ping_service_coordinated.py
"""

import os
import sys
import time
import logging
import requests
from concurrent.futures import ThreadPoolExecutor, as_completed
from typing import List, Dict, Tuple

# Configuration
CMS_URL = os.getenv('CMS_URL', 'http://localhost')
CMS_API_TOKEN = os.getenv('CMS_API_TOKEN', '')
PING_INTERVAL = int(os.getenv('PING_INTERVAL', '30'))  # seconds
PING_TIMEOUT = int(os.getenv('PING_TIMEOUT', '5'))  # seconds
MAX_WORKERS = int(os.getenv('MAX_WORKERS', '20'))
BATCH_SIZE = int(os.getenv('BATCH_SIZE', '100'))

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class CMSCoordinatedPingService:
    """Ping service that coordinates with CMS backend"""
    
    def __init__(self):
        if not CMS_API_TOKEN:
            logger.error("CMS_API_TOKEN environment variable not set")
            sys.exit(1)
        
        self.cms_url = CMS_URL.rstrip('/')
        self.headers = {
            'Authorization': f'Bearer {CMS_API_TOKEN}',
            'Content-Type': 'application/json'
        }
        
        logger.info(f"CMS URL: {self.cms_url}")
        logger.info(f"Ping interval: {PING_INTERVAL}s")
        logger.info(f"Ping timeout: {PING_TIMEOUT}s")
        logger.info(f"Max workers: {MAX_WORKERS}")
    
    def get_devices_needing_ping(self) -> List[Dict]:
        """Get list of devices that need external ping from CMS"""
        try:
            url = f"{self.cms_url}/api/admin/external-service/devices/needs-ping"
            params = {'interval_seconds': PING_INTERVAL}
            
            response = requests.get(
                url,
                headers=self.headers,
                params=params,
                timeout=10
            )
            
            if response.status_code == 200:
                data = response.json()
                devices = data.get('data', {}).get('devices', [])
                logger.info(f"Retrieved {len(devices)} devices needing ping")
                return devices
            else:
                logger.error(f"Failed to get devices: HTTP {response.status_code}")
                return []
        
        except Exception as e:
            logger.error(f"Error getting devices from CMS: {e}")
            return []
    
    def ping_device(self, device: Dict) -> Tuple[int, bool, str, int]:
        """
        Ping a single device
        
        Returns: (device_id, success, status_message, response_time_ms)
        """
        device_id = device['id']
        ping_url = device['ping_url']
        
        try:
            start_time = time.time()
            response = requests.get(ping_url, timeout=PING_TIMEOUT)
            response_time_ms = int((time.time() - start_time) * 1000)
            
            if response.status_code == 200 or response.status_code == 204:
                return (device_id, True, f"HTTP {response.status_code} OK", response_time_ms)
            else:
                return (device_id, False, f"HTTP {response.status_code}", response_time_ms)
        
        except requests.Timeout:
            return (device_id, False, "Timeout", PING_TIMEOUT * 1000)
        
        except requests.ConnectionError:
            return (device_id, False, "Connection refused", 0)
        
        except Exception as e:
            return (device_id, False, f"Error: {str(e)}", 0)
    
    def submit_ping_results_batch(self, results: List[Dict]) -> bool:
        """Submit ping results to CMS in batch"""
        try:
            url = f"{self.cms_url}/api/admin/external-service/devices/ping-batch"
            payload = {'results': results}
            
            response = requests.post(
                url,
                headers=self.headers,
                json=payload,
                timeout=30
            )
            
            if response.status_code == 200:
                data = response.json()
                summary = data.get('data', {})
                logger.info(
                    f"Batch submitted: {summary.get('processed')}/{summary.get('total_submitted')} processed, "
                    f"{summary.get('status_changes')} status changes"
                )
                return True
            else:
                logger.error(f"Failed to submit batch: HTTP {response.status_code}")
                return False
        
        except Exception as e:
            logger.error(f"Error submitting batch to CMS: {e}")
            return False
    
    def run_ping_cycle(self):
        """Run a single ping cycle"""
        logger.info("=== Starting ping cycle ===")
        
        # Get devices from CMS
        devices = self.get_devices_needing_ping()
        
        if not devices:
            logger.info("No devices need ping at this time")
            return
        
        # Ping devices concurrently
        results = []
        
        with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
            future_to_device = {
                executor.submit(self.ping_device, device): device
                for device in devices
            }
            
            for future in as_completed(future_to_device):
                device = future_to_device[future]
                try:
                    device_id, success, status, response_time = future.result()
                    
                    results.append({
                        'device_id': device_id,
                        'ping_successful': success,
                        'ping_status': status
                    })
                    
                    log_msg = f"Device {device['name']} (ID: {device_id}): {status}"
                    if success:
                        log_msg += f" ({response_time}ms)"
                        logger.debug(log_msg)
                    else:
                        logger.warning(log_msg)
                
                except Exception as e:
                    logger.error(f"Error processing device {device['name']}: {e}")
        
        # Submit results to CMS in batches
        if results:
            for i in range(0, len(results), BATCH_SIZE):
                batch = results[i:i + BATCH_SIZE]
                self.submit_ping_results_batch(batch)
        
        logger.info(f"=== Ping cycle complete: {len(results)} devices processed ===")
    
    def run(self):
        """Main loop"""
        logger.info("Starting CMS-coordinated ping service")
        
        while True:
            try:
                self.run_ping_cycle()
            except KeyboardInterrupt:
                logger.info("Shutting down...")
                break
            except Exception as e:
                logger.error(f"Unexpected error in ping cycle: {e}")
            
            # Wait for next cycle
            logger.info(f"Waiting {PING_INTERVAL} seconds until next cycle...")
            time.sleep(PING_INTERVAL)


if __name__ == '__main__':
    service = CMSCoordinatedPingService()
    service.run()
