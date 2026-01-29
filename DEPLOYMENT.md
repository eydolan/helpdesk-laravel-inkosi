# Deployment Guide for help.inkosiconnect.co.za

## Pre-Deployment Checklist

### 1. Files to Upload (via Git or FTP)

**Upload ALL files EXCEPT:**
- `.env` (create new on server)
- `node_modules/` (will be installed via npm)
- `vendor/` (will be installed via composer)
- `.git/` (if using FTP, exclude this)
- `.phpunit.cache/`
- `storage/*.key` (sensitive files)

**Required Files/Folders:**
```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
vendor/ (or install via composer)
composer.json
composer.lock
package.json
package-lock.json
artisan
```

### 2. Server Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM (for asset compilation)
- MySQL/MariaDB (same database as local)
- Web server (Apache/Nginx)

### 3. Deployment Steps

#### Step 1: Upload Files
```bash
# Option A: Git (Recommended)
git clone git@github.com:eydolan/helpdesk-laravel-inkosi.git
cd helpdesk-laravel-inkosi

# Option B: Upload via FTP/SFTP
# Upload all files except those listed above
```

#### Step 2: Install Dependencies

**Option A: If npm is available on server:**
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install

# Build assets
npm run build
```

**Option B: If npm is NOT available (RECOMMENDED):**
```bash
# Build assets LOCALLY on your development machine first:
npm install
npm run build

# Then upload the public/build/ directory to server
# On server, skip npm commands and just run:
composer install --optimize-autoloader --no-dev
```

#### Step 3: Configure Environment
```bash
# Copy .env.example to .env
cp .env.example .env

# Generate application key (if not exists)
php artisan key:generate
```

#### Step 4: Update .env File
Edit `.env` with production values:

```env
APP_NAME="inkosiConnect Helpdesk"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://help.inkosiconnect.co.za

# Database (same as local)
DB_CONNECTION=mysql
DB_HOST=dedi84.cpt4.host-h.net
DB_PORT=3306
DB_DATABASE=inkosi_ticket
DB_USERNAME=wpbqw_zg5ln
DB_PASSWORD=28r1i9355Y298B

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.inkosiconnect.co.za
MAIL_PORT=465
MAIL_USERNAME=help@inkosiconnect.co.za
MAIL_PASSWORD=asdasdfds22sDDDF1!
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="help@inkosiconnect.co.za"
MAIL_FROM_NAME="inkosiConnect Helpdesk"

# WinSMS Configuration (if using)
WINSMS_API_KEY=
WINSMS_USERNAME=
WINSMS_SENDER_ID=

# MTCaptcha Configuration
MTCAPTCHA_ENABLED=true
MTCAPTCHA_SITE_KEY=MTPublic-K1NW2ePLW
MTCAPTCHA_PRIVATE_KEY=MTPrivat-K1NW2ePLW-le92mM7ykcwYwsTuiJ3HGsHneF6kWLkddKnc06YK9cud4yDkEx
```

#### Step 5: Run Migrations
```bash
# Run database migrations
php artisan migrate --force

# Run settings migrations
php artisan settings:migrate
```

#### Step 6: Set Permissions
```bash
# Set storage and cache permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Step 7: Create Storage Link
```bash
# Create symbolic link for storage
php artisan storage:link
```

#### Step 8: Clear and Cache
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 9: Configure Web Server

**Apache (.htaccess should be in public/)**
```apache
<VirtualHost *:80>
    ServerName help.inkosiconnect.co.za
    DocumentRoot /path/to/helpdesk-laravel-inkosi/public
    
    <Directory /path/to/helpdesk-laravel-inkosi/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**
```nginx
server {
    listen 80;
    server_name help.inkosiconnect.co.za;
    root /path/to/helpdesk-laravel-inkosi/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Step 10: SSL Certificate (HTTPS)
Ensure SSL certificate is configured for `https://help.inkosiconnect.co.za`

### 4. Post-Deployment

1. **Update Admin Settings:**
   - Go to: `https://help.inkosiconnect.co.za/admin/settings/account`
   - Verify MTCaptcha settings match .env file
   - Update any other settings as needed

2. **Test:**
   - Visit: `https://help.inkosiconnect.co.za/`
   - Test ticket submission
   - Test admin login
   - Verify MTCaptcha works

3. **Monitor Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### 5. Quick Deployment Script

Save as `deploy.sh`:

```bash
#!/bin/bash
set -e

echo "Deploying to production..."

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force
php artisan settings:migrate

# Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 775 storage bootstrap/cache

echo "Deployment complete!"
```

Make executable: `chmod +x deploy.sh`
Run: `./deploy.sh`

### 6. Important Notes

- **Database stays the same** - No need to export/import database
- **MTCaptcha will work** - Since it's not localhost, validation will be active
- **Environment variables** - Make sure `.env` is properly configured
- **File permissions** - Storage and cache directories must be writable
- **SSL Required** - For production, HTTPS is recommended

### 7. Troubleshooting

**If you see "500 Internal Server Error":**
- Check file permissions
- Check `.env` file exists and is configured
- Check `storage/logs/laravel.log` for errors
- Ensure `APP_DEBUG=false` in production

**If MTCaptcha doesn't work:**
- Verify MTCAPTCHA_ENABLED=true in .env
- Check site key matches in admin settings
- Ensure you're accessing via HTTPS (not localhost)

**If assets don't load:**
- Run `npm run build`
- Check `public/build/` directory exists
- Verify web server can access `public/` directory
