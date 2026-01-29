<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
</head>
<body>
    <h2>Password Reset Code</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>You have requested to reset your password. Use the following code to reset your password:</p>
    
    <div style="background-color: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px 0; font-family: monospace; font-size: 24px; text-align: center;">
        <strong>{{ $code }}</strong>
    </div>
    
    <p>This code will expire in 15 minutes.</p>
    
    <p style="margin: 20px 0;">
        <a href="{{ url('/password/reset/verify?email=' . urlencode($user->email)) }}" style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Enter Reset Code
        </a>
    </p>
    
    <p>Or copy and paste this URL into your browser:<br>
    <a href="{{ url('/password/reset/verify?email=' . urlencode($user->email)) }}">{{ url('/password/reset/verify?email=' . urlencode($user->email)) }}</a></p>
    
    <p>If you did not request a password reset, please ignore this email.</p>
    
    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
