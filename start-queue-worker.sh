#!/bin/bash
# Start Queue Worker Script
# This script starts the queue worker in the background

cd ~/public_html/help.inkosiconnect.co.za

# Check if queue worker is already running
if pgrep -f "artisan queue:work" > /dev/null; then
    echo "Queue worker is already running!"
    ps aux | grep "artisan queue:work" | grep -v grep
    exit 1
fi

# Start queue worker in background
nohup /usr/bin/php8.4 artisan queue:work --sleep=3 --tries=3 --max-time=3600 > storage/logs/queue-worker.log 2>&1 &

# Get the PID
PID=$!
echo "Queue worker started with PID: $PID"
echo "Log file: storage/logs/queue-worker.log"
echo ""
echo "To stop the worker, run:"
echo "  kill $PID"
echo ""
echo "To check if it's running:"
echo "  ps aux | grep 'artisan queue:work'"
