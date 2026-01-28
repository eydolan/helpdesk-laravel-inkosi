<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Account Password</title>
</head>
<body>
    <h2>Your Account Password</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Your account has been created. Please save this password securely:</p>
    
    <div style="background-color: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px 0; font-family: monospace; font-size: 18px; text-align: center;">
        <strong>{{ $password }}</strong>
    </div>
    
    <p>You can use this password to log in with your:</p>
    <ul>
        @if($user->email)
            <li>Email: {{ $user->email }}</li>
        @endif
        @if($user->phone)
            <li>Phone: {{ $user->phone }}</li>
        @endif
    </ul>
    
    <p>Please keep this password secure and do not share it with anyone.</p>
    
    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
