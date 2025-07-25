<?php
// register.php

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? '');

    // Simple validation
    $errors = [];
    if (empty($username)) $errors[] = "Username is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if (empty($role)) $errors[] = "Role is required.";

    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $conn = new mysqli('localhost', 'root', '', 'restaurant');
        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

        // Check if username exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errors[] = "Username already exists. Please choose another.";
            $checkStmt->close();
        } else {
            $checkStmt->close();
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                $success = "Registration successful!";
            } else {
                $errors[] = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Restaurant Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f7f7f7; font-family: 'Segoe UI', Arial, sans-serif; }
        .container { max-width: 400px; margin: 50px auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);}
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333;}
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .5rem; color: #555;}
        input[type="text"], input[type="password"], select {
            width: 100%; padding: .75rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem;
        }
        button { width: 100%; padding: .75rem; background: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer;}
        button:hover { background: #0056b3;}
        .error { background: #ffe0e0; color: #d8000c; padding: .75rem; border-radius: 4px; margin-bottom: 1rem;}
        .success { background: #e0ffe0; color: #007b1c; padding: .75rem; border-radius: 4px; margin-bottom: 1rem;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error) echo "<div>$error</div>"; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required value="<?= htmlspecialchars($username ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password (min 6 chars)</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="">Select role</option>
                    <option value="staff" <?= (isset($role) && $role === 'staff') ? 'selected' : '' ?>>Staff</option>
                    <option value="customer" <?= (isset($role) && $role === 'customer') ? 'selected' : '' ?>>Customer</option>
                    <option value="admin" <?= (isset($role) && $role === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>