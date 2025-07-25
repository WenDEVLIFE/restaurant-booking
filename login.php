<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Simple validation
    $errors = [];
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        $conn = new mysqli('localhost', 'root', '', 'restaurant');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if users table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'users'");
        if ($table_check->num_rows == 0) {
            // Create users table if it doesn't exist
            $create_table = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'customer'
            )";
            if ($conn->query($create_table)) {
                $errors[] = "Users table created. Please register an account first.";
            } else {
                $errors[] = "Error creating users table: " . $conn->error;
            }
        } else {
            // Prepare statement to find user - FIXED: changed user_id to id
            $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
            if ($stmt === false) {
                $errors[] = "Database prepare error: " . $conn->error;
            } else {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Password is correct, start session
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        // Redirect based on role
                        switch ($user['role']) {
                            case 'admin':
                                header('Location: admin_dashboard.php');
                                break;
                            case 'staff':
                                header('Location: admin_dashboard.php');
                                break;
                            case 'customer':
                                header('Location: index.html');
                                break;
                            default:
                                header('Location: admin_dashboard.php');
                        }
                        exit();
                    } else {
                        $errors[] = "Invalid username or password.";
                    }
                } else {
                    $errors[] = "Invalid username or password.";
                }

                $stmt->close();
            }
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Booking - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
                     background: linear-gradient(to right, #ff5e4d, #ffc371);
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
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #2980b9;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #ff5e4d, #ffc371);
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
        .error {
            background: #ffe0e0;
            color: #d8000c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Sign In</h2>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error) echo "<div>$error</div>"; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" value="<?= htmlspecialchars($username ?? '') ?>">
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