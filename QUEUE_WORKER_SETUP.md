# Queue Worker Setup for Production

## Server Details
- **Path**: `/public_html/help.inkosiconnect.co.za`
- **PHP**: `/usr/bin/php8.4`

## Commands

### 1. Test Queue Processing (One-time)
```bash
cd /public_html/help.inkosiconnect.co.za && /usr/bin/php8.4 artisan queue:work --once
```

### 2. Run Queue Worker in Background
```bash
cd /public_html/help.inkosiconnect.co.za && nohup /usr/bin/php8.4 artisan queue:work --sleep=3 --tries=3 > /dev/null 2>&1 &
```

### 3. Supervisor Configuration (Recommended)

Create file: `/etc/supervisor/conf.d/laravel-queue-worker.conf`

```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php8.4 /public_html/help.inkosiconnect.co.za/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/public_html/help.inkosiconnect.co.za/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue-worker:*
```

### 4. Laravel Scheduler (Alternative)

If you prefer using the scheduler, add to crontab:
```bash
* * * * * cd /public_html/help.inkosiconnect.co.za && /usr/bin/php8.4 artisan schedule:run >> /dev/null 2>&1
```

The scheduler will run `queue:work --stop-when-empty` every minute (already configured in `routes/console.php`).

## Verify Queue Worker is Running

```bash
# Check if queue worker process is running
ps aux | grep "queue:work"

# Check queue status
cd /public_html/help.inkosiconnect.co.za && /usr/bin/php8.4 artisan queue:work --once
```

## Troubleshooting

- **Check logs**: `tail -f /public_html/help.inkosiconnect.co.za/storage/logs/laravel.log`
- **Check failed jobs**: `cd /public_html/help.inkosiconnect.co.za && /usr/bin/php8.4 artisan queue:failed`
- **Retry failed jobs**: `cd /public_html/help.inkosiconnect.co.za && /usr/bin/php8.4 artisan queue:retry all`
