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
    
    <p>If you did not request a password reset, please ignore this email.</p>
    
    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
