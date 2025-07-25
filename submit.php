<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "restaurant";

// Connect to MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$guests = $_POST['guestCount'];
$time = $_POST['selectedTime'];
$table = $_POST['selectedTable'];
$date = $_POST['date'];
$phone = $_POST['phone'] ?? ''; // Optional field

// Use prepared statements for security
$sql = "INSERT INTO reservations (name, guests, time, table_name, date, phone_number) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sissss", $name, $guests, $time, $table, $date, $phone);

if ($stmt->execute()) {
  echo "success";  // JS checks for "success"
} else {
  echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
