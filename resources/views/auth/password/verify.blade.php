<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Reset Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .verify-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-container">
            <h1 class="mb-4 text-center">Verify Reset Code</h1>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset.verify') }}">
                @csrf

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" 
                           id="phone" name="phone" value="{{ $phone ?? old('phone') }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="code" class="form-label">Reset Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                           id="code" name="code" value="{{ old('code') }}" required 
                           placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Enter the 6-digit code sent to your phone number.
                    </small>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Verify Code</button>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}">Request New Code</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
