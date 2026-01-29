#!/bin/bash
# Queue Worker Script - Auto-restart if it crashes
# Run this script to keep queue worker running continuously

cd ~/public_html/help.inkosiconnect.co.za

# Log file
LOG_FILE="storage/logs/queue-worker.log"

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log "Queue worker script started"

# Keep running the queue worker
while true; do
    log "Starting queue worker..."
    /usr/bin/php8.4 artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> "$LOG_FILE" 2>&1
    EXIT_CODE=$?
    
    if [ $EXIT_CODE -eq 0 ]; then
        log "Queue worker exited normally (no jobs)"
    else
        log "Queue worker crashed with exit code $EXIT_CODE, restarting in 5 seconds..."
    fi
    
    sleep 5
done
