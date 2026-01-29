# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2026-01-29] (Continued)

### Fixed
- Fixed password reset verification form to properly handle email OR phone (not both required)
- Fixed login form validation to accept phone numbers without email format errors
- Fixed phone number authentication by implementing phone number normalization
- Improved phone number matching to handle different formats (spaces, dashes, etc.)
- Fixed password reset verification page to show correct identifier field based on context
- Removed hardcoded client secret default value from config/services.php for security

### Changed
- Updated login form component from email input to text input to support phone number login
- Enhanced CustomUserProvider with phone number normalization for flexible matching
- Improved password reset verification to automatically detect email vs phone from identifier field

## [2026-01-29]

### Added
- Botpress chatbot integration on all pages (public and admin)
- Email-to-SMS functionality using `phonenumber@winsms.net` format
- Public ticket submission page with Filament styling
- Registration recommendation banner on public ticket page
- Navigation bar with logo and consistent styling across public pages
- Success page with Filament styling matching admin pages
- Automatic email-to-SMS fallback when WinSMS API key is not configured
- Migration to make email column nullable in users table

### Changed
- Updated public ticket submission page to use Filament CSS and component structure
- Restyled success page to match Filament admin pages design
- Updated all navigation links to use Filament link component styling
- Changed email-to-SMS domain from `winsms.co.za` to `winsms.net`
- Category field set as hidden with default value of 5
- Improved form spacing and button styling to match Filament admin pages
- Updated UserResolutionService to use `phonenumber@winsms.net` for users without email
- Enhanced PasswordService to fallback to email-to-SMS when API key is missing
- Updated password reset flow to use email-to-SMS via winsms.net

### Fixed
- Fixed email field constraint violation when creating users without email
- Fixed form spacing inconsistencies on public ticket page
- Fixed button styling to match Filament admin pages exactly
- Fixed link colors to use primary yellow/amber color consistently
- Fixed input focus ring colors to use primary yellow/amber
- Fixed navigation link spacing issues
- Fixed password reset SMS delivery when API key is not configured

### Added
- Dual authentication support: Login with either email address or phone number
- Password reset via email with 6-digit verification code
- Password reset via SMS (phone) using WinSMS gateway integration
- Custom Filament login page with "Back to Home" button
- Custom Filament password reset pages matching Filament design system
- Case-insensitive email lookup for authentication
- Password reset code verification flow with email/phone support
- WinSMS service integration for SMS-based password resets
- Custom Filament password reset request page that redirects to unified reset flow
- Migration to add email column to password_reset_codes table

### Changed
- Updated login authentication to support both email and phone number
- Enhanced password reset flow to support both email and SMS methods
- Improved password reset email template with direct verification link
- Updated password reset views to match Filament's design system
- Modified User model to use case-insensitive email lookups
- Updated CustomUserProvider to handle email/phone authentication
- Enhanced PasswordService to support both email and SMS password reset methods
- Improved WinSMSService with better error handling and configuration testing
- Updated mail configuration to properly handle SMTP settings
- Modified UserSeeder to ensure seeded users have is_active and email_verified_at set

### Fixed
- Fixed login authentication issues with case-sensitive email matching
- Fixed password reset not working for email-based resets
- Fixed password reset code verification flow
- Fixed Filament login page styling consistency
- Fixed user account activation check during login
- Fixed password reset email not being sent (mail configuration issues)
- Fixed password reset views not matching Filament design system
- Fixed "Forgot password?" link in Filament login to use custom dual-method reset

### Security
- Improved password reset security with time-limited verification codes
- Enhanced user authentication validation

## [Previous Versions]

Previous changelog entries are not available. This changelog starts from the current development cycle.
