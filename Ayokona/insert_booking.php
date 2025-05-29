<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "file";

$conn = new mysqli("localhost", "root", "", "ticket", 3306,"/data/data/com.termux/files/usr/var/run/mysqld.sock");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from POST
$movie_title = $_POST['movie_title'];
$selected_seats = $_POST['selected_seats'];
$selected_date = $_POST['selected_date'];
$selected_time = $_POST['selected_time'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO bookings (movie_title, selected_seats, selected_date, selected_time) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $movie_title, $selected_seats, $selected_date, $selected_time);

// Execute
if ($stmt->execute()) {
    echo "Booking confirmed!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
