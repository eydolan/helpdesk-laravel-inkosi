# Deployment Without NPM on Server

## Solution: Build Assets Locally, Upload Built Files

Since npm is not available on your server, you need to build assets locally and upload the `public/build/` directory.

### Step 1: Build Assets Locally (on your development machine)

```bash
cd /path/to/helpdesk-laravel-inkosi

# Install npm dependencies (if not already done)
npm install

# Build production assets
npm run build
```

This will create/update files in `public/build/` directory:
- `public/build/assets/app-*.js`
- `public/build/assets/app-*.css`
- `public/build/manifest.json`

### Step 2: Upload to Server

**IMPORTANT:** Make sure to upload the `public/build/` directory to your server!

```bash
# Via FTP/SFTP, upload:
public/build/          # ← This directory is CRITICAL
```

Or if using Git:
```bash
# Commit the built files (if not already committed)
git add public/build/
git commit -m "Add built assets for production"
git push

# On server, pull the latest
git pull
```

### Step 3: On Server - Skip npm Steps

On the server, you can skip these commands:
```bash
# ❌ DON'T RUN THESE (npm not available)
# npm install
# npm run build
```

Instead, just run:
```bash
# ✅ Run these commands only
composer install --optimize-autoloader --no-dev
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan settings:migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

### Step 4: Verify Assets

After deployment, check that these files exist on server:
- `public/build/assets/app-*.js`
- `public/build/assets/app-*.css`
- `public/build/manifest.json`

### Alternative: Install Node.js on Server (if possible)

If you have SSH access and can install Node.js:

```bash
# Install Node.js via nvm (Node Version Manager)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 18
nvm use 18

# Then run npm commands
npm install
npm run build
```

But the **recommended approach** is to build locally and upload the `public/build/` directory.

### Quick Checklist

- [ ] Build assets locally: `npm run build`
- [ ] Verify `public/build/` directory exists locally
- [ ] Upload `public/build/` directory to server
- [ ] On server: Skip `npm install` and `npm run build`
- [ ] On server: Run composer and artisan commands only
- [ ] Test website loads correctly
