# Queue Worker Setup (Without Sudo Access)

## Server Details
- **Path**: `~/public_html/help.inkosiconnect.co.za`
- **PHP**: `/usr/bin/php8.4`

## Solution: Background Process with Auto-Restart

Since you don't have sudo access, use a background process that auto-restarts.

### Option 1: Simple Background Process (Easiest)

**Start the queue worker:**
```bash
cd ~/public_html/help.inkosiconnect.co.za
nohup /usr/bin/php8.4 artisan queue:work --sleep=3 --tries=3 --max-time=3600 > storage/logs/queue-worker.log 2>&1 &
```

**Check if it's running:**
```bash
ps aux | grep "artisan queue:work" | grep -v grep
```

**Stop the queue worker:**
```bash
pkill -f "artisan queue:work"
```

### Option 2: Using Screen (Recommended - Survives SSH Disconnect)

**Start screen session:**
```bash
screen -S queue-worker
cd ~/public_html/help.inkosiconnect.co.za
/usr/bin/php8.4 artisan queue:work --sleep=3 --tries=3
```

**Detach from screen:** Press `Ctrl+A` then `D`

**Reattach to screen:**
```bash
screen -r queue-worker
```

**List screen sessions:**
```bash
screen -ls
```

### Option 3: Using the Auto-Restart Script

**Make scripts executable:**
```bash
cd ~/public_html/help.inkosiconnect.co.za
chmod +x queue-worker.sh start-queue-worker.sh stop-queue-worker.sh
```

**Start queue worker:**
```bash
./start-queue-worker.sh
```

**Stop queue worker:**
```bash
./stop-queue-worker.sh
```

**Or use the auto-restart script (keeps running even if it crashes):**
```bash
nohup ./queue-worker.sh > /dev/null 2>&1 &
```

### Option 4: Add to .bashrc (Auto-start on Login)

Add this to your `~/.bashrc`:
```bash
# Start queue worker if not already running
if ! pgrep -f "artisan queue:work" > /dev/null; then
    cd ~/public_html/help.inkosiconnect.co.za
    nohup /usr/bin/php8.4 artisan queue:work --sleep=3 --tries=3 --max-time=3600 > storage/logs/queue-worker.log 2>&1 &
fi
```

## Verify Queue Worker is Running

```bash
# Check if process is running
ps aux | grep "artisan queue:work" | grep -v grep

# Check queue status
cd ~/public_html/help.inkosiconnect.co.za
/usr/bin/php8.4 artisan queue:work --once

# View logs
tail -f ~/public_html/help.inkosiconnect.co.za/storage/logs/queue-worker.log
```

## Troubleshooting

**If queue worker stops:**
- Check logs: `tail -f storage/logs/queue-worker.log`
- Restart it using one of the methods above

**Check failed jobs:**
```bash
cd ~/public_html/help.inkosiconnect.co.za
/usr/bin/php8.4 artisan queue:failed
```

**Retry failed jobs:**
```bash
/usr/bin/php8.4 artisan queue:retry all
```

## Recommended Setup

1. Use **Option 2 (Screen)** for development/testing
2. Use **Option 1 (Background Process)** for production if you have a stable connection
3. Use **Option 3 (Auto-Restart Script)** for production if you want automatic recovery

The queue worker will process jobs immediately instead of waiting for cron (every 2 hours).
