<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="filament dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticket Submitted Successfully - inkosiConnect Helpdesk</title>
    <!-- Include Filament CSS files for exact styling -->
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}" data-navigate-track>
    <link rel="stylesheet" href="{{ asset('css/filament/forms/forms.css') }}" data-navigate-track>
    <link rel="stylesheet" href="{{ asset('css/filament/support/support.css') }}" data-navigate-track>
    <link rel="stylesheet" href="{{ asset('css/Joaopaulolndev/filament-edit-profile/filament-edit-profile-styles.css') }}" data-navigate-track>
    <link rel="stylesheet" href="{{ asset('css/rmsramos/activitylog/activitylog-styles.css') }}" data-navigate-track>
    <style>
        /* CSS Variables for Filament primary color (Amber) - RGB format for Tailwind */
        :root {
            --primary-50: 255 251 235;
            --primary-100: 254 243 199;
            --primary-200: 253 230 138;
            --primary-300: 252 211 77;
            --primary-400: 251 191 36;
            --primary-500: 245 158 11;
            --primary-600: 217 119 6;
            --primary-700: 180 83 9;
            --primary-800: 146 64 14;
            --primary-900: 120 53 15;
            --primary-950: 69 26 3;
        }
        /* Minimal custom styles only for page-specific layout elements */
        .navbar {
            background-color: rgb(9, 9, 11);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.25rem;
            text-decoration: none;
        }
        .navbar-brand img {
            height: 2rem;
            width: auto;
        }
        /* Navigation links spacing */
        .navbar .flex.items-center > * + * {
            margin-left: 1rem;
        }
        .navbar .fi-link {
            padding: 0.5rem 0.75rem;
        }
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        /* Button styling to match Filament */
        .fi-btn.fi-color-primary {
            --c-400: var(--primary-400);
            --c-500: var(--primary-500);
            --c-600: var(--primary-600);
            background-color: rgb(217, 119, 6);
            color: rgb(255, 255, 255);
        }
        .fi-btn.fi-color-primary:hover {
            background-color: rgb(245, 158, 11);
        }
        .fi-btn.fi-color-primary:focus-visible {
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.5);
        }
        .dark .fi-btn.fi-color-primary {
            background-color: rgb(245, 158, 11);
        }
        .dark .fi-btn.fi-color-primary:hover {
            background-color: rgb(251, 191, 36);
        }
        /* Link styling to ensure yellow/primary color */
        .fi-link span.text-custom-600,
        .fi-link .text-custom-600 {
            color: rgb(217, 119, 6) !important;
        }
        .dark .fi-link span.text-custom-400,
        .dark .fi-link .text-custom-400 {
            color: rgb(245, 158, 11) !important;
        }
        /* Dark mode link colors */
        .dark .fi-link span[style*="color:rgb(217,119,6)"] {
            color: rgb(245, 158, 11) !important;
        }
        /* Ensure links are visible and styled correctly */
        .fi-link span {
            transition: color 0.15s ease-in-out;
        }
        .fi-link:hover span {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img src="https://inkosiconnect.co.za/assets/content/images/logo/w-0001ink-logo-2025-2.svg" alt="inkosiConnect Helpdesk" />
                    <span>Helpdesk</span>
                </a>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary">
                            <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--primary-400);--c-600:var(--primary-600);color:rgb(217,119,6);">
                                Dashboard
                            </span>
                        </a>
                        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary" style="background: none; border: none; cursor: pointer;">
                                <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--primary-400);--c-600:var(--primary-600);color:rgb(217,119,6);">
                                    Logout
                                </span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('filament.admin.auth.login') }}" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary">
                            <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--primary-400);--c-600:var(--primary-600);color:rgb(217,119,6);">
                                Login
                            </span>
                        </a>
                        @php
                            $accountSettings = app(\App\Settings\AccountSettings::class);
                        @endphp
                        @if($accountSettings->user_registration ?? false)
                            <a href="{{ route('filament.admin.auth.register') }}" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary">
                                <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--primary-400);--c-600:var(--primary-600);color:rgb(217,119,6);">
                                    Register
                                </span>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="success-container">
            <!-- Success Icon and Title -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/20 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Ticket Submitted Successfully!</h1>
            </div>

            <!-- Success Message -->
            <div class="fi-fo-field-wrp mb-6 p-4 rounded-lg bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-success-600 dark:text-success-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            Thank you for submitting your ticket.
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @if($isNewAccount)
                                You are now logged in to your account.
                            @else
                                Your ticket has been received and will be processed shortly.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Password Warning (if new account) -->
            @if($isNewAccount && $temporaryPassword)
                <div class="fi-fo-field-wrp mb-6 p-4 rounded-lg bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-warning-600 dark:text-warning-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Important: Save Your Password</h5>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Your account has been created. Please save this password securely:</p>
                            <div class="bg-white dark:bg-gray-800 border-2 border-warning-400 dark:border-warning-500 rounded-lg p-4 mb-3">
                                <p class="text-lg font-mono font-bold text-center text-gray-900 dark:text-white">{{ $temporaryPassword }}</p>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                You can use this password to log in with your 
                                @if($ticket->owner->email && !str_ends_with($ticket->owner->email, '@winsms.net'))
                                    email ({{ $ticket->owner->email }})
                                @endif
                                @if($ticket->owner->phone)
                                    @if($ticket->owner->email && !str_ends_with($ticket->owner->email, '@winsms.net')) or @endif
                                    phone number ({{ $ticket->owner->phone }})
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Ticket Details Card -->
            <div class="fi-fo-field-wrp mb-6 rounded-lg bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-white/10">
                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">Ticket Details</h5>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ticket ID:</span>
                        <span class="text-sm text-gray-900 dark:text-white font-semibold">#{{ $ticket->id }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Title:</span>
                        <span class="text-sm text-gray-900 dark:text-white text-right">{{ $ticket->title }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $ticket->ticketStatus->name }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted:</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $ticket->created_at->format('F d, Y \a\t g:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="fi-ac gap-3 flex flex-wrap items-center justify-start">
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="fi-btn fi-color-primary fi-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm text-white fi-ac-action fi-ac-btn-action">
                    <span class="fi-btn-label">Go to Dashboard</span>
                </a>
                <a href="{{ route('public.tickets.create') }}" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 ring-1 ring-gray-950/10 dark:ring-white/20">
                    <span class="fi-btn-label">Submit Another Ticket</span>
                </a>
            </div>
        </div>
    </div>

    @include('partials.botpress')
</body>
</html>
