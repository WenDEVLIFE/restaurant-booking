<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Booking - Login</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: linear-gradient(135deg, #27ae60 0%, #2980b9 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            padding: 40px 32px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.15);
            width: 100%;
            max-width: 380px;
        }
        h2 {
            text-align: center;
            margin-bottom: 32px;
            font-weight: 600;
            color: #2980b9;
        }
        .form-group {
            margin-bottom: 22px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 15px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
            outline: none;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #2980b9;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #27ae60 0%, #2980b9 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.08);
            transition: background 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #2980b9 0%, #27ae60 100%);
        }
        .register-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #2980b9;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.2s;
        }
        .register-link:hover {
            color: #27ae60;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Sign In</h2>
        <form action="authenticate.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit">Login</button>
        </form>
        <a class="register-link" href="register.php">Don't have an account? Register</a>
    </div>
</body>
</html>