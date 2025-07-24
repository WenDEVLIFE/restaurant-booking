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


// Insert into reservations table (without dish)
$sql = "INSERT INTO reservations (name, guests, time, table_name, date)
        VALUES ('$name', '$guests', '$time', '$table', '$date')";

if ($conn->query($sql) === TRUE) {
  echo "success";  // JS checks for "success"
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
