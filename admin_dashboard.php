<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle delete booking
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    $conn = new mysqli("localhost", "root", "", "restaurant");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $delete_sql = "DELETE FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $success_message = "Booking deleted successfully!";
    } else {
        $error_message = "Error deleting booking: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "restaurant";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch booking stats
$stats_sql = "SELECT COUNT(*) AS total_bookings, SUM(guests) AS total_guests FROM reservations";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Fetch all bookings
$bookings_sql = "SELECT id, name, guests, time, table_name, date FROM reservations ORDER BY date DESC, time DESC";
$bookings_result = $conn->query($bookings_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Restaurant Booking</title>
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
        .stats { 
            margin-bottom: 30px; 
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats div { 
            display: inline-block; 
            margin-right: 40px; 
            font-size: 1.2em; 
        }
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
        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
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
        .delete-btn {
            background: #dc3545;
            color: white;
            padding: 4px 8px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .actions-column {
            width: 100px;
            text-align: center;
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
                    <a href="admin_dashboard.php" class="nav-link active">Dashboard</a>
                </li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="user.php" class="nav-link">User Management</a>
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
            <h1>Dashboard</h1>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="stats">
            <div><strong>Total Bookings:</strong> <?php echo $stats['total_bookings']; ?></div>
            <div><strong>Total Guests Booked:</strong> <?php echo $stats['total_guests'] ?? 0; ?></div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2 style="margin: 0;">Bookings Management</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Guests</th>
                        <th>Time</th>
                        <th>Table</th>
                        <th>Date</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings_result->num_rows > 0): ?>
                        <?php while($row = $bookings_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['guests']); ?></td>
                                <td><?php echo htmlspecialchars($row['time']); ?></td>
                                <td><?php echo htmlspecialchars($row['table_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td class="actions-column">
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this booking?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No bookings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>