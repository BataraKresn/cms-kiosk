import requests
import time
import logging
from tenacity import retry, stop_after_attempt, wait_fixed

# Configure logging
logging.basicConfig(
    level=logging.INFO, format="%(asctime)s - %(levelname)s - %(message)s"
)

# URL to hit
url = "http://127.0.0.1:3001/status_device"

# Interval (in seconds) between requests
interval = 5  # Change to your preferred interval


# Retry configuration: Max 3 attempts, 5 seconds wait between retries
@retry(stop=stop_after_attempt(3), wait=wait_fixed(5))
def make_request():
    response = requests.get(url, timeout=10)  # Timeout set to 10 seconds
    if response.status_code == 200:
        logging.info("Successfully hit the URL.")
    else:
        logging.warning(f"Failed to hit the URL. Status Code: {response.status_code}")
    return response


def main():
    while True:
        try:
            make_request()
        except requests.exceptions.RequestException as e:
            logging.error(f"Request error: {str(e)}")
        except KeyboardInterrupt:
            logging.info("Process interrupted by user. Exiting...")
            break
        except Exception as e:
            logging.error(f"Unexpected error: {str(e)}")

        # Wait for the next hit
        time.sleep(interval)


if __name__ == "__main__":
    main()
