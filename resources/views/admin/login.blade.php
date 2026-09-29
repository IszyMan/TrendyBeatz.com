<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | TrendyBeatz</title>
    <link rel="icon" type="image/png" href="{{ asset('images/faviconn.png') }}">

    <style>
        body {
            display: grid;
            min-height: 100vh;
            place-items: center;
            margin: 0;
            background: #f1f5f2;
            font: 15px Arial, sans-serif;
        }

        .login-card {
            width: min(100% - 30px, 400px);
            padding: 28px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 25px #0002;
        }

        .login-card h1 {
            margin-top: 0;
        }

        .login-card label {
            display: block;
            margin: 14px 0;
            font-weight: 700;
        }

        .login-card input {
            width: 100%;
            margin-top: 6px;
            padding: 11px;
            border: 1px solid #bbb;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .login-card button {
            width: 100%;
            padding: 11px;
            border: 0;
            border-radius: 6px;
            color: #fff;
            background: #16803d;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .login-error {
            color: #ad2424;
        }
        .login-logo {
            display: block;
            width: 50px;
            height: 50px;
            margin: 0 auto 18px;
            object-fit: contain;
        }

        .login-card h1 {
            margin: 0 0 20px;
            text-align: center;
        }

        
    </style>
</head>
<body>
    <form class="login-card" method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <img
            class="login-logo"
            src="{{ asset('images/faviconn.png') }}"
            alt="TrendyBeatz"
        >

        <h1>TrendyBeatz Admin</h1>

        @error('email')
            <p class="login-error">{{ $message }}</p>
        @enderror

        <label>
            Email
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
            >
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <button type="submit">Login</button>
    </form>
</body>
</html>