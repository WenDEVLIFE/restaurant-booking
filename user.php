<?php
session_start();

// Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Logout function
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'restaurant');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success_message = '';
$error_message = '';

// Handle add account
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (!empty($username) && !empty($password)) {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        } else {
            // Username doesn't exist, proceed with insertion
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $success_message = "User added successfully!";
            } else {
                $error_message = "Error adding user: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}

// Handle edit account
if (isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    if (!empty($username)) {
        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $username, $hashed_password, $role, $user_id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $username, $role, $user_id);
        }
        
        if ($stmt->execute()) {
            $success_message = "User updated successfully!";
        } else {
            $error_message = "Error updating user: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "Username is required.";
    }
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $success_message = "User deleted successfully!";
    } else {
        $error_message = "Error deleting user: " . $conn->error;
    }
    $stmt->close();
}

// Fetch users from database
$result = $conn->query("SELECT user_id, username, role FROM users");
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get user for editing
$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT user_id, username, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management - Restaurant Booking</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; 
            background: #f7f7f7; 
        }
        
        /* Navigation Styles */
        .navbar {
              background: linear-gradient(to right, #ff5e4d, #ffc371);
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .nav-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 600;
            text-decoration: none;
        }
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }
        .nav-item {
            margin: 0;
        }
        .nav-link {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 15px 20px;
            transition: background 0.2s;
        }
        .nav-link:hover {
              background: linear-gradient(to right, #ff5e4d, #ffc371);
        }
        .nav-link.active {
            background: #007bff;
        }
        .logout-link {
            background: #dc3545;
        }
        .logout-link:hover {
            background: #c82333;
        }
        .user-info {
            color: #fff;
            padding: 15px 20px;
            white-space: nowrap;
            display: flex;
            align-items: center;
        }
        
        /* Content Styles */
        .content {
            padding: 30px 20px;
        }
        .header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        h1 { 
            color: #333; 
            margin: 0;
            text-align: center;
        }
        
        /* Form Styles */
        .form-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        
        /* Message Styles */
        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Table Styles */
        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            background: #fff; 
        }
        th, td { 
            border: 1px solid #dee2e6; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: #f8f9fa; 
            font-weight: 600;
        }
        tr:nth-child(even) { 
            background: #f8f9fa; 
        }
        .actions {
            text-align: center;
            white-space: nowrap;
        }
        .actions .btn {
            margin: 0 2px;
            padding: 4px 8px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="admin_dashboard.php" class="nav-brand">Restaurant</a>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="user.php" class="nav-link active">User Management</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                </li>
                <li class="nav-item">
                    <a href="?logout=1" class="nav-link logout-link" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <h1>User Management</h1>
        </div>

        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Add/Edit User Form -->
        <div class="form-container">
            <h3><?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h3>
            <form method="POST">
                <?php if ($edit_user): ?>
                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo $edit_user && $edit_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo $edit_user && $edit_user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password"><?php echo $edit_user ? 'New Password (leave blank to keep current)' : 'Password'; ?></label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-actions" style="text-align: right;">
                    <a href="user.php" class="btn btn-secondary" style="margin-right: 10px;">Cancel</a>
                    <button type="submit" name="<?php echo $edit_user ? 'edit_user' : 'add_user'; ?>" class="btn btn-primary">
                        <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- User Table -->
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td class="actions">
                        <a href="?edit=<?= $user['user_id'] ?>" class="btn btn-warning" title="Edit User">Edit</a>
                        <a href="?delete=<?= $user['user_id'] ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this user?')" 
                           title="Delete User">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>



</html></body>    </div>    </div>
</body>
</html>