# Running Specific PHP Versions on Xneelo Server

## Finding Available PHP Versions

First, check what PHP versions are available on your server:

```bash
# Check default PHP version
php -v

# List all available PHP binaries
ls -la /usr/bin/php*

# Or check common locations
ls -la /opt/cpanel/ea-php*/root/usr/bin/php 2>/dev/null
ls -la /opt/alt/php*/usr/bin/php 2>/dev/null
which -a php
```

## Common PHP Binary Locations on Xneelo

Xneelo servers typically have PHP versions in these locations:

### Standard Linux Paths:
```bash
/usr/bin/php8.2
/usr/bin/php8.1
/usr/bin/php8.0
/usr/bin/php7.4
```

### cPanel/WHM Paths (if applicable):
```bash
/opt/cpanel/ea-php82/root/usr/bin/php
/opt/cpanel/ea-php81/root/usr/bin/php
/opt/cpanel/ea-php80/root/usr/bin/php
```

### Alternative Paths:
```bash
/opt/alt/php82/usr/bin/php
/opt/alt/php81/usr/bin/php
/opt/alt/php80/usr/bin/php
```

## Using Specific PHP Versions

### Method 1: Direct Path (Recommended)
```bash
# Use PHP 8.2
/usr/bin/php8.2 artisan migrate

# Use PHP 8.1
/usr/bin/php8.1 artisan migrate

# Use PHP 8.0
/usr/bin/php8.0 artisan migrate
```

### Method 2: Create Aliases (Add to ~/.bashrc)
```bash
# Edit your bashrc
nano ~/.bashrc

# Add these lines:
alias php82='/usr/bin/php8.2'
alias php81='/usr/bin/php8.1'
alias php80='/usr/bin/php8.0'

# Reload
source ~/.bashrc

# Then use:
php82 artisan migrate
php81 composer install
```

### Method 3: Check Which PHP Version Your Domain Uses
```bash
# Check PHP version for your domain (if using cPanel)
/usr/local/cpanel/bin/whmapi1 php_get_vhost_versions domain=help.inkosiconnect.co.za

# Or check via PHP config
/usr/local/cpanel/bin/php_get_vhost_versions
```

## For Laravel/Composer Commands

### Using Composer with Specific PHP Version:
```bash
# Method 1: Use full path
/usr/bin/php8.2 /usr/local/bin/composer install

# Method 2: Set PHP_BIN environment variable
PHP_BIN=/usr/bin/php8.2 /usr/local/bin/composer install

# Method 3: Use composer's --php flag (if supported)
composer install --php=/usr/bin/php8.2
```

### Using Artisan with Specific PHP Version:
```bash
# Direct path
/usr/bin/php8.2 artisan migrate
/usr/bin/php8.2 artisan config:cache
/usr/bin/php8.2 artisan route:cache

# Or create a helper script
echo '#!/bin/bash
/usr/bin/php8.2 artisan "$@"' > artisan82
chmod +x artisan82
./artisan82 migrate
```

## Quick Discovery Commands

Run these to find your PHP versions:

```bash
# Find all PHP executables
find /usr -name "php*" -type f -executable 2>/dev/null | grep -E "(php8|php7)" | head -20

# Check which PHP composer uses
composer --version

# Check which PHP is default
which php
php -v

# List all PHP versions (if update-alternatives is used)
update-alternatives --list php 2>/dev/null
```

## Example: Full Deployment with PHP 8.2

```bash
# Set PHP version variable (adjust path as needed)
PHP82=/usr/bin/php8.2

# Run commands with specific version
$PHP82 /usr/local/bin/composer install --optimize-autoloader --no-dev
$PHP82 artisan migrate --force
$PHP82 artisan settings:migrate
$PHP82 artisan config:cache
$PHP82 artisan route:cache
$PHP82 artisan view:cache
```

## Troubleshooting

### If you get "command not found":
```bash
# Check if PHP exists
ls -la /usr/bin/php*

# Check your PATH
echo $PATH

# Try finding PHP
find / -name "php8.2" 2>/dev/null
```

### If composer uses wrong PHP version:
```bash
# Check composer's PHP
composer --version

# Override with environment variable
PHP_BIN=/usr/bin/php8.2 composer install
```

### Check PHP modules/extensions:
```bash
# Check loaded extensions for specific version
/usr/bin/php8.2 -m

# Check if required extensions are available
/usr/bin/php8.2 -m | grep -E "(pdo|mysql|mbstring|xml|curl|zip)"
```

## Quick Reference

```bash
# Replace X with version number (e.g., 82, 81, 80)
/usr/bin/phpX.Y artisan [command]
/usr/bin/phpX.Y /path/to/composer [command]

# Common versions:
/usr/bin/php8.2    # PHP 8.2
/usr/bin/php8.1    # PHP 8.1
/usr/bin/php8.0    # PHP 8.0
/usr/bin/php7.4    # PHP 7.4
```
