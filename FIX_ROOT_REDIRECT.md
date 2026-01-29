# Fix Root Redirect - Quick Solution

Since `https://help.inkosiconnect.co.za/public/` works, the issue is the root `.htaccess` redirect.

## Solution 1: Update Root .htaccess (Recommended)

The root `.htaccess` file has been updated. Upload it to your server:

**File location:** `~/public_html/help.inkosiconnect.co.za/.htaccess`

**Content:**
```apache
# Redirect all requests to the public directory
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Don't rewrite if already in public directory
    RewriteCond %{REQUEST_URI} ^/public/
    RewriteRule ^(.*)$ - [L]
    
    # Don't rewrite requests for .well-known (Let's Encrypt, etc.)
    RewriteCond %{REQUEST_URI} !^/\.well-known
    
    # Don't rewrite if file exists in root (for compatibility)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Redirect everything else to public directory
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Prevent directory listing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "^(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|artisan|README\.md|DEPLOYMENT\.md|DEPLOY_CHECKLIST\.txt|XNEELO_PHP_COMMANDS\.md|DEPLOY_NO_NPM\.md)">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Solution 2: Change Document Root in Xneelo (Best Long-term)

**This is the BEST solution** - it removes `/public/` from URLs entirely:

1. Log into **Xneelo Control Panel**
2. Go to your domain: `help.inkosiconnect.co.za`
3. Find **"Document Root"** or **"Web Root"** settings
   - Usually under: **Hosting Tools** → **Domain Settings** or **File Manager**
4. Change from: `/public_html/help.inkosiconnect.co.za/`
5. To: `/public_html/help.inkosiconnect.co.za/public/`
6. **Save** and wait 2-5 minutes

**After this change:**
- `https://help.inkosiconnect.co.za/` will work directly
- No need for `.htaccess` redirect
- Cleaner URLs
- Better performance

## Quick Test After Fix

1. Upload the updated `.htaccess` file
2. Visit: `https://help.inkosiconnect.co.za/`
3. Should redirect to: `https://help.inkosiconnect.co.za/public/` (or work directly if document root changed)

## If .htaccess Still Doesn't Work

Some Xneelo servers have restrictions on `.htaccess` in the root. In that case:

**Option A:** Change document root (Solution 2 above) - **RECOMMENDED**

**Option B:** Create `index.php` in root that redirects:
```php
<?php
header('Location: /public/');
exit;
```

**Option C:** Contact Xneelo support to:
- Enable mod_rewrite for your domain
- Or change document root to `public/` directory

## Verify .htaccess is Working

After uploading, test:

```bash
# On server, check if .htaccess exists
ls -la ~/public_html/help.inkosiconnect.co.za/.htaccess

# Check permissions
chmod 644 ~/public_html/help.inkosiconnect.co.za/.htaccess

# Test redirect
curl -I https://help.inkosiconnect.co.za/
# Should show redirect to /public/
```
