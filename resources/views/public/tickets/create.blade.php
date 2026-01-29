<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="filament dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submit a Ticket - inkosiConnect Helpdesk</title>
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
        .ticket-form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        /* Grid System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -0.75rem;
            margin-right: -0.75rem;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        @media (max-width: 768px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        /* MTCaptcha */
        #mtcaptcha-container {
            min-height: 60px;
            margin-top: 0.5rem;
        }
        #mtcaptcha-container iframe {
            display: block;
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
        /* Input focus ring styling to ensure yellow/primary color */
        .fi-input-wrp:focus-within {
            --tw-ring-color: rgb(217, 119, 6) !important;
            box-shadow: 0 0 0 1px rgb(217, 119, 6), 0 0 0 1px rgb(217, 119, 6) !important;
        }
        .dark .fi-input-wrp:focus-within {
            --tw-ring-color: rgb(245, 158, 11) !important;
            box-shadow: 0 0 0 1px rgb(245, 158, 11), 0 0 0 1px rgb(245, 158, 11) !important;
        }
        /* Override any gray ring colors on focus */
        .fi-input-wrp:focus-within.ring-gray-950\/10,
        .fi-input-wrp:focus-within.ring-white\/20 {
            --tw-ring-color: rgb(217, 119, 6) !important;
            box-shadow: 0 0 0 1px rgb(217, 119, 6) !important;
        }
        .dark .fi-input-wrp:focus-within.ring-gray-950\/10,
        .dark .fi-input-wrp:focus-within.ring-white\/20 {
            --tw-ring-color: rgb(245, 158, 11) !important;
            box-shadow: 0 0 0 1px rgb(245, 158, 11) !important;
        }
        /* Ensure primary color utilities work for Tailwind */
        .ring-primary-600 {
            --tw-ring-color: rgb(217, 119, 6);
        }
        .ring-primary-500 {
            --tw-ring-color: rgb(245, 158, 11);
        }
        .text-primary-600 {
            color: rgb(217, 119, 6);
        }
        .text-primary-500 {
            color: rgb(245, 158, 11);
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
        <div class="ticket-form-container">
            <h1 class="mb-4">Submit a Ticket</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('public.tickets.store') }}" class="space-y-6">
                @csrf

                @if(!auth()->check())
                    <div class="fi-fo-field-wrp mb-4 p-4 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                    We recommend registering an account
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Create an account to easily track your past and future tickets, receive updates, and manage your support requests in one place.
                                </p>
                                @if($accountSettings->user_registration ?? false)
                                    <a href="{{ route('filament.admin.auth.register') }}" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary mt-2">
                                        <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--primary-400);--c-600:var(--primary-600);color:rgb(217,119,6);">
                                            Register now →
                                        </span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if(!auth()->check())
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fi-fo-field-wrp" data-field-wrapper>
                                <div class="grid gap-y-2">
                                    <div class="flex items-center gap-x-3 justify-between">
                                        <label for="name" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                Name<sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="grid auto-cols-fr gap-y-2">
                                        <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 @error('name') fi-invalid ring-danger-600 dark:ring-danger-500 @enderror [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                                            <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                                                <input type="text" class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                                                       id="name" name="name" value="{{ old('name') }}" placeholder="Enter your name" required>
                                            </div>
                                        </div>
                                        @error('name')
                                            <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fi-fo-field-wrp" data-field-wrapper>
                                <div class="grid gap-y-2">
                                    <div class="flex items-center gap-x-3 justify-between">
                                        <label for="email" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                Email (Optional)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="grid auto-cols-fr gap-y-2">
                                        <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 @error('email') fi-invalid ring-danger-600 dark:ring-danger-500 @enderror [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                                            <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                                                <input type="email" class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                                                       id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email">
                                            </div>
                                        </div>
                                        @error('email')
                                            <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})
                    </div>
                @endif

                <div class="fi-fo-field-wrp" data-field-wrapper>
                    <div class="grid gap-y-2">
                        <div class="flex items-center gap-x-3 justify-between">
                            <label for="phone" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Phone Number<sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                </span>
                            </label>
                        </div>
                        <div class="grid auto-cols-fr gap-y-2">
                            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 @error('phone') fi-invalid ring-danger-600 dark:ring-danger-500 @enderror [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                                <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                                    <input type="text" class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                                           id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="Enter your phone number" required>
                                </div>
                            </div>
                            @error('phone')
                                <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="fi-fo-field-wrp" data-field-wrapper>
                    <div class="grid gap-y-2">
                        <div class="flex items-center gap-x-3 justify-between">
                            <label for="voucher_number" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Voucher Number (Optional)
                                </span>
                            </label>
                        </div>
                        <div class="grid auto-cols-fr gap-y-2">
                            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 @error('voucher_number') fi-invalid ring-danger-600 dark:ring-danger-500 @enderror [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                                <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                                    <input type="text" class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                                           id="voucher_number" name="voucher_number" value="{{ old('voucher_number') }}" placeholder="Enter voucher number">
                                </div>
                            </div>
                            @error('voucher_number')
                                <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Hidden work unit field, defaults to 1 -->
                <input type="hidden" name="unit_id" id="unit_id" value="1">

                <!-- Hidden category field, always set to 5 -->
                <input type="hidden" name="category_id" id="category_id" value="5">

                <div class="fi-fo-field-wrp" data-field-wrapper>
                    <div class="grid gap-y-2">
                        <div class="flex items-center gap-x-3 justify-between">
                            <label for="title" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Title<sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                </span>
                            </label>
                        </div>
                        <div class="grid auto-cols-fr gap-y-2">
                            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 @error('title') fi-invalid ring-danger-600 dark:ring-danger-500 @enderror [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                                <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                                    <input type="text" class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                                           id="title" name="title" value="{{ old('title') }}" placeholder="Enter ticket title" required maxlength="255">
                                </div>
                            </div>
                            @error('title')
                                <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="fi-fo-field-wrp" data-field-wrapper>
                    <div class="grid gap-y-2">
                        <div class="flex items-center gap-x-3 justify-between">
                            <label for="description" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Description<sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                </span>
                            </label>
                        </div>
                        <div class="grid auto-cols-fr gap-y-2">
                            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 @error('description') fi-invalid ring-danger-600 dark:ring-danger-500 @enderror [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                                <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                                    <textarea class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                                              id="description" name="description" rows="5" placeholder="Enter ticket description" required>{{ old('description') }}</textarea>
                                </div>
                            </div>
                            @error('description')
                                <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- MTCaptcha disabled temporarily --}}
                {{-- @if(!auth()->check())
                    @php
                        $mtcaptchaServiceView = app(\App\Services\MTCaptchaService::class);
                    @endphp
                    @if($mtcaptchaServiceView->shouldShow())
                        <div class="fi-fo-field-wrp" data-field-wrapper>
                            <div class="grid gap-y-2">
                                <div class="flex items-center gap-x-3 justify-between">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            Security Verification<sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                        </span>
                                    </label>
                                </div>
                                <div class="grid auto-cols-fr gap-y-2">
                                    <div id="mtcaptcha-container">
                                        <div style="color: rgb(156, 163, 175); font-size: 0.875rem;">Loading security verification...</div>
                                    </div>
                                    <input type="hidden" name="mtcaptcha_token" id="mtcaptcha-verifiedtoken" />
                                    @error('mtcaptcha_token')
                                        <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                @endif --}}

                <div class="fi-ac gap-3 flex flex-wrap items-center justify-start">
                    <button 
                        x-data="{
                            form: null,
                            isProcessing: false,
                            processingMessage: null,
                        }"
                        x-init="
                            form = $el.closest('form')

                            form?.addEventListener('form-processing-started', (event) => {
                                isProcessing = true
                                processingMessage = event.detail.message
                            })

                            form?.addEventListener('form-processing-finished', () => {
                                isProcessing = false
                            })
                        "
                        x-bind:class="{ 'enabled:opacity-70 enabled:cursor-wait': isProcessing }"
                        type="submit"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm text-white fi-ac-action fi-ac-btn-action w-full"
                        style="--c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600);"
                        data-has-alpine-state="true"
                    >
                        <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="animate-spin fi-btn-icon transition duration-75 h-5 w-5 text-white" x-show="isProcessing" style="display: none;">
                            <path clip-rule="evenodd" d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>
                            <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>
                        </svg>
                        <span x-show="! isProcessing" class="fi-btn-label">Submit Ticket</span>
                        <span x-show="isProcessing" x-text="processingMessage" class="fi-btn-label" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Category is now a hidden field set to 5, no need to load categories

        {{-- MTCaptcha integration disabled temporarily --}}
        {{-- @if(!auth()->check())
            @php
                $mtcaptchaService = app(\App\Services\MTCaptchaService::class);
            @endphp
            @if($mtcaptchaService->shouldShow())
                (function() {
                    // Wait for DOM to be ready
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initMTCaptcha);
                    } else {
                        initMTCaptcha();
                    }
                    
                    function initMTCaptcha() {
                        const container = document.getElementById('mtcaptcha-container');
                        if (!container) {
                            console.error('MTCaptcha container not found');
                            return;
                        }
                        
                        const script = document.createElement('script');
                        script.src = 'https://service.mtcaptcha.com/mtcv1/client/mtcaptcha.min.js';
                        script.async = true;
                        script.defer = true;
                        
                        script.onload = function() {
                            if (typeof mtcaptcha !== 'undefined') {
                                try {
                                    // Clear loading message
                                    container.innerHTML = '';
                                    mtcaptcha.render('mtcaptcha-container', {
                                        sitekey: '{{ $mtcaptchaService->getSiteKey() }}',
                                        theme: 'dark',
                                        callback: function(token) {
                                            const tokenInput = document.getElementById('mtcaptcha-verifiedtoken');
                                            if (tokenInput) {
                                                tokenInput.value = token;
                                            }
                                        },
                                        'error-callback': function() {
                                            const tokenInput = document.getElementById('mtcaptcha-verifiedtoken');
                                            if (tokenInput) {
                                                tokenInput.value = '';
                                            }
                                        }
                                    });
                                } catch (error) {
                                    console.error('Error rendering MTCaptcha:', error);
                                    container.innerHTML = '<div style="color: #ef4444; font-size: 0.875rem;">Failed to load security verification. Please refresh the page.</div>';
                                }
                            } else {
                                console.error('MTCaptcha library not loaded');
                                container.innerHTML = '<div style="color: #ef4444; font-size: 0.875rem;">Failed to load security verification. Please refresh the page.</div>';
                            }
                        };
                        
                        script.onerror = function() {
                            console.error('Failed to load MTCaptcha script');
                            container.innerHTML = '<div style="color: #ef4444; font-size: 0.875rem;">Failed to load security verification. Please check your internet connection and refresh the page.</div>';
                        };
                        
                        document.head.appendChild(script);
                    }
                })();
            @endif
        @endif --}}
    </script>
    <!-- Alpine.js for form processing state -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('partials.botpress')
</body>
</html>
