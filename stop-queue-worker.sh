#!/bin/bash
# Stop Queue Worker Script

cd ~/public_html/help.inkosiconnect.co.za

# Find and kill queue worker processes
PIDS=$(pgrep -f "artisan queue:work")

if [ -z "$PIDS" ]; then
    echo "No queue worker processes found running."
    exit 0
fi

echo "Stopping queue worker processes..."
for PID in $PIDS; do
    echo "Killing process $PID"
    kill $PID
done

sleep 2

# Check if any are still running
REMAINING=$(pgrep -f "artisan queue:work")
if [ -n "$REMAINING" ]; then
    echo "Force killing remaining processes..."
    kill -9 $REMAINING
fi

echo "Queue worker stopped."
