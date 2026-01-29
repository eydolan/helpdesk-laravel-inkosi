<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket Submitted Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .success-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .password-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 4px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 1.2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="text-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="green" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 2.604 6.3a.75.75 0 0 0-1.06 1.06l3.25 3.25a.75.75 0 0 0 1.08-.022l3.75-5.5a.75.75 0 0 0-1.08-1.06z"/>
                </svg>
                <h1 class="mt-3">Ticket Submitted Successfully!</h1>
            </div>

            <div class="alert alert-success">
                <strong>Thank you for submitting your ticket.</strong>
                @if($isNewAccount)
                    <p class="mb-0 mt-2">You are now logged in to your account.</p>
                @else
                    <p class="mb-0 mt-2">Your ticket has been received and will be processed shortly.</p>
                @endif
            </div>

            @if($isNewAccount && $temporaryPassword)
                <div class="alert alert-warning">
                    <h5 class="alert-heading">Important: Save Your Password</h5>
                    <p>Your account has been created. Please save this password securely:</p>
                    <div class="password-box">
                        <strong>{{ $temporaryPassword }}</strong>
                    </div>
                    <p class="mb-0">
                        <small>You can use this password to log in with your 
                        @if($ticket->owner->email)
                            email ({{ $ticket->owner->email }}) 
                        @endif
                        @if($ticket->owner->phone)
                            or phone number ({{ $ticket->owner->phone }})
                        @endif
                        </small>
                    </p>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Ticket Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Ticket ID:</strong> #{{ $ticket->id }}</p>
                    <p><strong>Title:</strong> {{ $ticket->title }}</p>
                    <p><strong>Status:</strong> {{ $ticket->ticketStatus->name }}</p>
                    <p><strong>Submitted:</strong> {{ $ticket->created_at->format('F d, Y \a\t g:i A') }}</p>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="btn btn-primary">
                    Go to Dashboard
                </a>
                <a href="{{ route('public.tickets.create') }}" class="btn btn-outline-secondary">
                    Submit Another Ticket
                </a>
            </div>
        </div>
    </div>
    @include('partials.botpress')
</body>
</html>
