# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
