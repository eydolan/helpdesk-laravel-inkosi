 <img src="screenshot/create-ticket.png" width="100%"></img> 
## Helpdesk Laravel

This is a fork of the original [ruswan/laravel_helpdesk](https://github.com/ruswan/laravel_helpdesk) system, adapted to our specific needs. The original project provides a web-based helpdesk system using the [**Laravel Framework v10**](https://laravel.com) and [**Filament v2**](https://github.com/filamentphp).

### Additional Features

This fork includes the following additional features and enhancements:

1. **Dual Authentication**: Users can login using either their email address or phone number
2. **Password Reset via Email**: Support for password reset via email with 6-digit verification code
3. **Password Reset via SMS**: Password reset via SMS (phone) using WinSMS gateway integration
4. **Email-to-SMS Functionality**: Automatic SMS delivery via email-to-SMS gateway (`phonenumber@winsms.net`) when API is not configured
5. **Public Ticket Submission**: Beautiful public-facing ticket submission form matching Filament admin panel styling
6. **Botpress Chatbot Integration**: Chatbot available on all pages (public and admin) for customer support
7. **Registration Recommendation**: Banner on public ticket page encouraging user registration for better ticket tracking
8. **Consistent Filament Styling**: All public pages (ticket submission, success page, password reset) styled to match Filament admin pages
9. **Navigation Bar**: Logo and consistent navigation styling across all public pages
10. **Flexible User Creation**: Support for users without email addresses (uses `phonenumber@winsms.net` format)
11. **Case-Insensitive Email Lookup**: Improved authentication with case-insensitive email matching
12. **Enhanced Password Reset Flow**: Unified password reset flow supporting both email and SMS methods
13. **Custom Filament Password Reset Pages**: Password reset pages matching Filament design system
14. **WinSMS Service Integration**: Full integration with WinSMS API for SMS-based operations
15. **Automatic Fallback Mechanisms**: Email-to-SMS fallback when WinSMS API key is not configured
16. **Improved Form Styling**: Form elements, buttons, and links styled to match Filament admin pages exactly
17. **Enhanced User Resolution Service**: Smart user creation/resolution with email-to-SMS support
18. **Security Improvements**: Time-limited verification codes for password resets
19. **Database Schema Enhancements**: Email column made nullable to support phone-only users
20. **Password Reset Code Verification**: Secure code-based password reset verification flow

This Laravel Helpdesk repository will provide a solid foundation for building a customizable and extensible helpdesk system according to your specific needs. By utilizing Laravel as the main framework, this project offers user-friendliness, flexibility, and good performance.


<hr/>

## Database Design
 <img src="screenshot/database-design.png" width="100%"></img> 

<hr/>

## Unified Modeling Language (UML)
<img src="screenshot/uml.png" width="100%"></img> 
<hr/>

## Requirements
* PHP 8.1 or higher
* Database (eg: MySQL, PostgreSQL, SQLite)
* Web Server (eg: Apache, Nginx, IIS)

<hr/>


## Installation

* Install [Composer](https://getcomposer.org/download)
* Clone the repository: `git clone https://github.com/ruswan/laravel_helpdesk.git`
* Install PHP dependencies: `composer install`
* Setup configuration: `cp .env.example .env`
* Generate application key: `php artisan key:generate`
* Create a database and update your configuration.
* Configure mail settings in `.env` (for email-based password resets):
  ```
  MAIL_MAILER=smtp
  MAIL_HOST=your-smtp-host
  MAIL_PORT=587
  MAIL_USERNAME=your-email
  MAIL_PASSWORD=your-password
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=noreply@example.com
  MAIL_FROM_NAME="${APP_NAME}"
  ```
* Configure WinSMS settings in `.env` (optional, for SMS-based password resets):
  ```
  WINSMS_API_KEY=your-winsms-api-key
  WINSMS_USERNAME=your-winsms-username
  WINSMS_SENDER_ID=your-sender-id
  WINSMS_API_URL=https://www.winsms.co.za/api/batchmessage.asp
  ```
  
  **Note:** If WinSMS API key is not configured, the system will automatically use email-to-SMS functionality by sending emails to `phonenumber@winsms.net`. These emails are automatically converted to SMS by WinSMS and delivered to the phone number.
* Run database migration: `php artisan migrate`
* Create a symlink to the storage: `php artisan storage:link`
* Run the dev server: `php artisan serve`

**Note:** This fork does not include dummy accounts. You will need to create your own admin user after running migrations.

## Super Admin Preview
 <img src="screenshot/super-admin.png" width="100%"></img> 
