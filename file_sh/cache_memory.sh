#!/usr/bin/env bash
set -e

LOG="/var/log/clear-cache.log"

echo "===================================" | tee -a "$LOG"
echo "Clear Cache Started: $(date)" | tee -a "$LOG"

echo "[INFO] Memory before:" | tee -a "$LOG"
free -h | tee -a "$LOG"

echo "[INFO] Running sync..." | tee -a "$LOG"
sync

echo "[INFO] Dropping pagecache + dentries + inodes..." | tee -a "$LOG"
echo 3 | sudo tee /proc/sys/vm/drop_caches > /dev/null

sleep 2

echo "[INFO] Memory after:" | tee -a "$LOG"
free -h | tee -a "$LOG"

echo "Clear Cache Finished: $(date)" | tee -a "$LOG"
echo "===================================" | tee -a "$LOG"

