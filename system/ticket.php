<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ticket_code = $_GET['ticket_code'] ?? '';
if (!$ticket_code) {
    die("No ticket code provided.");
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE ticket_code = ?");
$stmt->bind_param("s", $ticket_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ticket not found.");
}
$ticket = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>ANIMAPLEX Ticket</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .ticket {
            max-width: 500px; margin: auto; background: white;
            padding: 20px; border: 2px dashed #28a745; border-radius: 10px;
        }
        .ticket h2 { text-align: center; margin-bottom: 20px; }
        .ticket p { margin: 5px 0; }
        .ticket .code { font-size: 20px; text-align: center; margin: 20px 0; font-weight: bold; }
        .btn {
            display: block; margin: 20px auto 0;
            padding: 10px; background: #28a745; color: white;
            text-align: center; text-decoration: none; border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="ticket">
    <h2>🎟️ ANIMAPLEX MOVIE TICKET 🎥</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($ticket['username']); ?></p>
    <p><strong>Movie:</strong> <?php echo htmlspecialchars($ticket['title']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($ticket['booking_date']); ?></p>
    <p><strong>Time:</strong> <?php echo htmlspecialchars($ticket['booking_time']); ?></p>
    <p><strong>Seats:</strong> <?php echo htmlspecialchars($ticket['seats']); ?></p>
    <p class="code">🎫 Ticket Code: <?php echo htmlspecialchars($ticket['ticket_code']); ?></p>
    <a href="#" class="btn" onclick="window.print()">🖨️ Print Ticket</a>
    <a href="dashboard.php" class="btn" style="background:#007bff;">Back to Dashboard</a>
</div>

</body>
</html>
