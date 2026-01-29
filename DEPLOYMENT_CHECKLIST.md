# Deployment Checklist

## Files Changed (Last 5 Commits)

### Core Application Files (MUST DEPLOY)

1. **app/Providers/ConfigServiceProvider.php**
   - Fixed SMTP configuration with encryption support
   - Location: `app/Providers/ConfigServiceProvider.php`

2. **app/Services/UserResolutionService.php**
   - Added Customer role auto-assignment for new users
   - Location: `app/Services/UserResolutionService.php`

3. **app/Support/Notifications/Debounce.php**
   - Fixed debounce mechanism to ensure notifications are sent
   - Location: `app/Support/Notifications/Debounce.php`

4. **app/Models/User.php**
   - Added `routeNotificationForMail` method for SMS gateway emails
   - Location: `app/Models/User.php`

5. **app/Notifications/TicketCommentCreated.php**
   - Updated to handle @winsms.net emails correctly
   - Location: `app/Notifications/TicketCommentCreated.php`

6. **app/Observers/CommentObserver.php**
   - Enhanced to notify both owner and responsible users
   - Location: `app/Observers/CommentObserver.php`

7. **database/seeders/RoleSeeder.php**
   - Added Customer role to seeder
   - Location: `database/seeders/RoleSeeder.php`

## Deployment Steps

### Option 1: Upload Specific Files (Recommended)

Upload these folders/files to your server:

```
app/Providers/ConfigServiceProvider.php
app/Services/UserResolutionService.php
app/Support/Notifications/Debounce.php
app/Models/User.php
app/Notifications/TicketCommentCreated.php
app/Observers/CommentObserver.php
database/seeders/RoleSeeder.php
```

### Option 2: Upload Entire Folders (Safer)

Upload these entire folders to ensure nothing is missed:

```
app/Providers/
app/Services/
app/Support/
app/Models/
app/Notifications/
app/Observers/
database/seeders/
```

## Post-Deployment Steps

1. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

2. **Run database seeder (if Customer role doesn't exist):**
   ```bash
   php artisan db:seed --class=RoleSeeder
   ```

3. **Assign Customer role to existing users without roles:**
   ```bash
   php artisan tinker
   ```
   Then run:
   ```php
   $users = App\Models\User::whereDoesntHave('roles')->get();
   $customerRole = Spatie\Permission\Models\Role::where('name', 'Customer')->first();
   foreach($users as $user) {
       $user->assignRole($customerRole);
   }
   ```

4. **Update Mail Settings in Admin Panel:**
   - Go to Admin → Settings → Mail
   - Update SMTP settings:
     - Host: `smtp.inkosiconnect.co.za`
     - Port: `465` (or `587` for TLS)
     - Encryption: `ssl` (for port 465) or `tls` (for port 587)
     - Username/Password: Your SMTP credentials
   - Save settings

5. **Restart queue workers (if using supervisor/systemd):**
   ```bash
   php artisan queue:restart
   ```

## Verification

After deployment, test:

1. Create a new ticket via public form → Should auto-assign Customer role
2. Add a comment to a ticket → Owner and responsible should receive emails
3. Check logs: `storage/logs/laravel.log` for any errors

## Important Notes

- **Database changes:** The Customer role assignment happens automatically for new users. Existing users need manual assignment (see step 3 above).
- **Mail configuration:** The mail driver was changed from `log` to `smtp` in the database. Make sure to update SMTP settings in admin panel.
- **Queue workers:** Ensure queue workers are running to process email notifications.
