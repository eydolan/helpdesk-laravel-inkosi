# Restructure Laravel to Remove /public/ from URLs

This guide will help you move Laravel's `public/` directory contents to the root, so URLs don't show `/public/`.

## ⚠️ IMPORTANT: Backup First!

**Before starting, backup everything!**

```bash
# On server, create backup
cd ~/public_html/help.inkosiconnect.co.za
tar -czf ../backup-$(date +%Y%m%d).tar.gz .
```

## Method 1: Move Public Contents to Root (Recommended)

### Step 1: Move public/ contents to root

On your server, run:

```bash
cd ~/public_html/help.inkosiconnect.co.za

# Move all files from public/ to root
mv public/* .
mv public/.* . 2>/dev/null || true

# Remove empty public directory
rmdir public
```

### Step 2: Update index.php paths

Edit `index.php` in the root and change:

**OLD (line 19):**
```php
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
```

**NEW:**
```php
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
```

**OLD (line 55):**
```php
require_once __DIR__.'/../vendor/autoload.php';
```

**NEW:**
```php
require_once __DIR__.'/vendor/autoload.php';
```

**OLD (line 56):**
```php
$app = require_once __DIR__.'/../bootstrap/app.php';
```

**NEW:**
```php
$app = require_once __DIR__.'/bootstrap/app.php';
```

### Step 3: Update .htaccess

The root `.htaccess` should now point Laravel files correctly. Update it:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Prevent directory listing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "^(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|artisan|README\.md|app|bootstrap|config|database|resources|routes|storage|tests|vendor)">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Step 4: Protect Laravel Directories

Create `.htaccess` files in sensitive directories:

**`app/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`bootstrap/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`config/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`database/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`resources/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`routes/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`storage/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`tests/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

**`vendor/.htaccess`:**
```apache
Order deny,allow
Deny from all
```

## Method 2: Automated Script (Safer)

Create a script `restructure.sh`:

```bash
#!/bin/bash
set -e

echo "⚠️  BACKUP FIRST! Press Ctrl+C to cancel, or Enter to continue..."
read

cd ~/public_html/help.inkosiconnect.co.za

# Backup
echo "Creating backup..."
tar -czf ../backup-$(date +%Y%m%d-%H%M%S).tar.gz .

# Move public contents
echo "Moving public/ contents to root..."
mv public/* .
mv public/.htaccess . 2>/dev/null || true
rmdir public

# Update index.php
echo "Updating index.php paths..."
sed -i "s|__DIR__.'/../storage|__DIR__.'/storage|g" index.php
sed -i "s|__DIR__.'/../vendor|__DIR__.'/vendor|g" index.php
sed -i "s|__DIR__.'/../bootstrap|__DIR__.'/bootstrap|g" index.php

# Create protection .htaccess files
echo "Creating protection .htaccess files..."
for dir in app bootstrap config database resources routes storage tests vendor; do
    if [ -d "$dir" ]; then
        echo "Order deny,allow" > "$dir/.htaccess"
        echo "Deny from all" >> "$dir/.htaccess"
        echo "Protected $dir/"
    fi
done

echo "✅ Done! Test your site now."
```

Run: `chmod +x restructure.sh && ./restructure.sh`

## After Restructuring

1. **Test the site:** `https://help.inkosiconnect.co.za/`
2. **Remove old redirect files:** Delete `index.php` redirect (the one we created earlier)
3. **Verify paths:** Check that CSS/JS assets load correctly
4. **Update asset paths:** If needed, update any hardcoded `/public/` paths in your code

## Important Notes

- **Asset paths:** Laravel's `asset()` helper will automatically work correctly
- **Storage link:** Run `php artisan storage:link` if you use file storage
- **Build assets:** Your `build/` directory is now in root, which is fine
- **Security:** The `.htaccess` files in Laravel directories protect sensitive files

## If Something Breaks

Restore from backup:
```bash
cd ~/public_html/help.inkosiconnect.co.za
rm -rf *
tar -xzf ../backup-YYYYMMDD.tar.gz
```
