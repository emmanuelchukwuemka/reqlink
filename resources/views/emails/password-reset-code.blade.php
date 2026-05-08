<!DOCTYPE html>
<html>
<head>
    <style>
        .code-container {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #e50914; /* ResQLink Red */
        }
        .footer {
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Hello, {{ $name }}!</h2>
    <p>You requested a password reset. Please use the following code to reset your password:</p>
    
    <div class="code-container">
        <span class="code">{{ $code }}</span>
    </div>

    <p>This code will expire in 60 minutes.</p>
    <p>If you did not request this, please ignore this email.</p>
    
    <div class="footer">
        © {{ date('Y') }} ResQLink. All rights reserved.
    </div>
</body>
</html>
