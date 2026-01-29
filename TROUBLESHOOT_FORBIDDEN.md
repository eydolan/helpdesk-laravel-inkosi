# Troubleshooting "Forbidden" Error on Xneelo

## Common Causes and Solutions

### 1. Check Web Root Configuration

**Problem:** Web root might not be pointing to `public/` directory.

**Solution A: Change Document Root in Xneelo Control Panel**
1. Log into Xneelo Control Panel
2. Go to your domain: `help.inkosiconnect.co.za`
3. Look for **"Document Root"** or **"Web Root"** settings
4. Change from: `/public_html/help.inkosiconnect.co.za/`
5. To: `/public_html/help.inkosiconnect.co.za/public/`
6. Save and wait a few minutes for changes to propagate

**Solution B: Use .htaccess Redirect (if you can't change document root)**
The `.htaccess` file should already redirect, but verify it exists and has correct permissions.

### 2. Check Directory Permissions

Run these commands on your server:

```bash
cd ~/public_html/help.inkosiconnect.co.za

# Check current permissions
ls -la

# Set correct permissions
chmod 755 .
chmod 644 .htaccess
chmod 755 public
chmod 644 public/.htaccess
chmod 644 public/index.php
chmod 755 storage
chmod 755 bootstrap
chmod 755 bootstrap/cache

# Set ownership (if you have access)
# chown -R yourusername:yourusername .
```

### 3. Verify .htaccess Files Exist

```bash
# Check if .htaccess files exist
ls -la .htaccess
ls -la public/.htaccess

# If missing, create them (content below)
```

### 4. Test if PHP is Working

Create a test file in `public/`:

```bash
echo "<?php phpinfo(); ?>" > public/test.php
```

Visit: `https://help.inkosiconnect.co.za/test.php`

If this works, PHP is fine. If not, there's a PHP configuration issue.

### 5. Check Apache Error Logs

```bash
# View recent errors
tail -50 ~/logs/error_log

# Or check Laravel logs
tail -50 ~/public_html/help.inkosiconnect.co.za/storage/logs/laravel.log
```

### 6. Verify mod_rewrite is Enabled

Create `public/phpinfo.php`:
```php
<?php phpinfo(); ?>
```

Visit it and search for "mod_rewrite" - it should be listed.

### 7. Alternative: Direct Public Directory Access

If redirects don't work, try accessing directly:
- `https://help.inkosiconnect.co.za/public/`

If this works, the issue is with the root `.htaccess` redirect.

### 8. Check if Index Files are Allowed

Some servers require an `index.php` or `index.html` in the root.

Create `public/index.html` as a test:
```html
<!DOCTYPE html>
<html>
<head><title>Test</title></head>
<body><h1>If you see this, public directory is accessible</h1></body>
</html>
```

Visit: `https://help.inkosiconnect.co.za/public/index.html`

### 9. Xneelo-Specific: Check cPanel/WHM Settings

If you have cPanel access:
1. Go to **File Manager**
2. Navigate to your domain folder
3. Check **"Show Hidden Files"**
4. Verify `.htaccess` exists
5. Right-click `.htaccess` → **Code Edit** → Verify content

### 10. Nuclear Option: Move Everything to Public

If nothing works, you can temporarily move Laravel files:

```bash
# BACKUP FIRST!
# Then create a subdirectory structure
mkdir -p public/laravel
mv app bootstrap config database routes resources storage vendor artisan composer.json composer.lock public/laravel/

# Update public/index.php paths (not recommended, but works)
```

**This is NOT recommended** - better to fix the root cause.

## Quick Diagnostic Script

Create `public/diagnose.php`:

```php
<?php
echo "<h1>Server Diagnostics</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

echo "<h2>mod_rewrite Check:</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo in_array('mod_rewrite', $modules) ? "✅ mod_rewrite is enabled" : "❌ mod_rewrite is NOT enabled";
} else {
    echo "Cannot check (not Apache or function not available)";
}

echo "<h2>Directory Permissions:</h2>";
echo "Current directory: " . getcwd() . "<br>";
echo "Is readable: " . (is_readable('.') ? 'Yes' : 'No') . "<br>";
echo "Is writable: " . (is_writable('.') ? 'Yes' : 'No') . "<br>";

echo "<h2>File Existence:</h2>";
echo ".htaccess exists: " . (file_exists('../.htaccess') ? 'Yes' : 'No') . "<br>";
echo "public/.htaccess exists: " . (file_exists('.htaccess') ? 'Yes' : 'No') . "<br>";
echo "index.php exists: " . (file_exists('index.php') ? 'Yes' : 'No') . "<br>";
?>
```

Visit: `https://help.inkosiconnect.co.za/public/diagnose.php`

This will show you exactly what's wrong.

## Most Likely Solution

**For Xneelo servers, the most common fix is:**

1. **Change Document Root in Control Panel** to point to `public/` directory
2. **OR** ensure the root `.htaccess` file exists and has correct permissions

If you can't change the document root, the `.htaccess` redirect should work, but you need to verify:
- File exists: `~/public_html/help.inkosiconnect.co.za/.htaccess`
- Permissions: `644`
- Content is correct (see below)

## Correct .htaccess Content

**Root `.htaccess`** (`~/public_html/help.inkosiconnect.co.za/.htaccess`):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} !^/\.well-known
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

Options -Indexes

<FilesMatch "^(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|artisan)">
    Order allow,deny
    Deny from all
</FilesMatch>
```

**Public `.htaccess`** (`~/public_html/help.inkosiconnect.co.za/public/.htaccess`):
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```
