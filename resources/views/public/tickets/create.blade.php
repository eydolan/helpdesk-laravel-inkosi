<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="filament dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submit a Ticket - inkosiConnect Helpdesk</title>
    <link rel="stylesheet" href="{{ asset('build/assets/app-fef9c196.css') }}">
    <style>
        :root {
            --primary-50: #fffbeb;
            --primary-100: #fef3c7;
            --primary-200: #fde68a;
            --primary-300: #fcd34d;
            --primary-400: #fbbf24;
            --primary-500: #f59e0b;
            --primary-600: #d97706;
            --primary-700: #b45309;
            --primary-800: #92400e;
            --primary-900: #78350f;
        }
        body {
            background-color: #111827;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            min-height: 100vh;
        }
        .navbar {
            background-color: #1f2937;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
        }
        .navbar-brand {
            font-weight: 600;
            color: var(--primary-500);
            font-size: 1.25rem;
        }
        .navbar-brand:hover {
            color: var(--primary-400);
        }
        .nav-link {
            color: #d1d5db;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .nav-link:hover {
            color: var(--primary-500);
            background-color: rgba(245, 158, 11, 0.1);
        }
        .ticket-form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #1f2937;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        .ticket-form-container h1 {
            color: white;
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #e5e7eb;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5rem;
            color: #f9fafb;
            background-color: #374151;
            border: 1px solid #4b5563;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
            background-color: #4b5563;
        }
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #ef4444;
        }
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #fca5a5;
        }
        .btn-primary {
            background-color: var(--primary-500);
            color: white;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            border-radius: 0.375rem;
            border: none;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: var(--primary-600);
        }
        .btn-primary:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .alert-danger {
            background-color: #7f1d1d;
            border: 1px solid #991b1b;
            color: #fca5a5;
        }
        .alert-info {
            background-color: #1e3a8a;
            border: 1px solid #1e40af;
            color: #93c5fd;
        }
        .text-danger {
            color: #fca5a5;
        }
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
        .mb-3 {
            margin-bottom: 1rem;
        }
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        .mb-0 {
            margin-bottom: 0;
        }
        .mt-1 {
            margin-top: 0.25rem;
        }
        .small {
            font-size: 0.875rem;
        }
        #mtcaptcha-container {
            min-height: 60px;
            margin-top: 0.5rem;
        }
        #mtcaptcha-container iframe {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a class="navbar-brand" href="{{ route('home') }}">
                    inkosiConnect Helpdesk
                </a>
                <div class="flex items-center space-x-4">
                    @auth
                        <a class="nav-link" href="{{ route('filament.admin.pages.dashboard') }}">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="nav-link" style="background: none; border: none; cursor: pointer;">
                                Logout
                            </button>
                        </form>
                    @else
                        <a class="nav-link" href="{{ route('filament.admin.auth.login') }}">
                            Login
                        </a>
                        @if($accountSettings->user_registration ?? false)
                            <a class="nav-link" href="{{ route('filament.admin.auth.register') }}">
                                Register
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

            <form method="POST" action="{{ route('public.tickets.store') }}">
                @csrf

                @if(!auth()->check())
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email (Optional)</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})
                    </div>
                @endif

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="voucher_number" class="form-label">Voucher Number (Optional)</label>
                    <input type="text" class="form-control @error('voucher_number') is-invalid @enderror" 
                           id="voucher_number" name="voucher_number" value="{{ old('voucher_number') }}">
                    @error('voucher_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Hidden work unit field, defaults to 1 -->
                <input type="hidden" name="unit_id" id="unit_id" value="1">

                <div class="mb-3">
                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" 
                            id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title') }}" required maxlength="255">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if(!auth()->check())
                    @php
                        $mtcaptchaServiceView = app(\App\Services\MTCaptchaService::class);
                    @endphp
                    @if($mtcaptchaServiceView->shouldShow())
                        <div class="mb-3">
                            <label class="form-label">Security Verification <span class="text-danger">*</span></label>
                            <div id="mtcaptcha-container">
                                <div style="color: #9ca3af; font-size: 0.875rem;">Loading security verification...</div>
                            </div>
                            <input type="hidden" name="mtcaptcha_token" id="mtcaptcha-verifiedtoken" />
                            @error('mtcaptcha_token')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                @endif

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 0.75rem 1.5rem; font-size: 1rem;">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Load categories on page load for unit_id = 1
        document.addEventListener('DOMContentLoaded', function() {
            const unitId = 1; // Default unit ID
            const categorySelect = document.getElementById('category_id');
            
            categorySelect.innerHTML = '<option value="">Loading...</option>';
            
            fetch(`/api/categories?unit_id=${unitId}`)
                .then(response => response.json())
                .then(data => {
                    categorySelect.innerHTML = '<option value="">Select a category</option>';
                    data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                });
        });

        // MTCaptcha integration
        @if(!auth()->check())
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
                        script.src = 'https://service.mtcaptcha.com/mtcv1/clientapi/mtcaptcha.min.js';
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
        @endif
    </script>
</body>
</html>
