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

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn = new mysqli('localhost', 'root', '', 'restaurant');
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        // Refresh to update user list
        header("Location: user.php");
        exit;
    }
}

// Fetch users from database
$conn = new mysqli('localhost', 'root', '', 'restaurant');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT user_id, username, role FROM users");
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <style>
        table { 
            border-collapse: collapse; 
            width: 70%; 
            margin: 20px auto; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); 
            border-radius: 8px; 
            overflow: hidden; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 12px; 
            text-align: left; 
            font-size: 1rem; 
        }
        th { 
            background: #f5f5f5; 
            font-weight: 600; 
        }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #f9fafb; 
            color: #222; 
        }
        h2 { 
            font-size: 2rem; 
            margin-top: 40px; 
            text-align: center; 
        }
        tr:nth-child(even) { 
            background: #fafafa; 
        }
        .logout {
            margin: 20px auto;
            display: block;
            width: 120px;
            padding: 10px 0;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .logout:hover {
            background: #0056b3;
        }
        .delete-btn {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .actions {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>User Management</h2>
    <a href="?logout=1" class="logout">Logout</a>
    
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
                <a href="?delete=<?= $user['user_id'] ?>" 
                   class="delete-btn" 
                   onclick="return confirm('Are you sure you want to delete this user?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>