<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "restaurant";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Connection failed"]);
  exit;
}

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';

$sql = "SELECT name, guests, time, date, table_name FROM reservations WHERE date = '$date' AND time = '$time'";
$result = $conn->query($sql);

$reservations = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
  }
}

echo json_encode($reservations);
$conn->close();
?>
