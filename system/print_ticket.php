<?php
$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['orderid'])) {
  $orderid = $_GET['orderid'];
  $sql = "SELECT * FROM orders WHERE orderid = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $orderid);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
  } else {
    echo "Ticket not found.";
    exit();
  }
} else {
  echo "No ticket ID provided.";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ticket #<?php echo htmlspecialchars($row['ticket_code']); ?></title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    .ticket { border: 2px dashed #333; padding: 20px; width: 300px; margin: auto; }
    h2 { text-align: center; }
    .details { margin-top: 20px; }
    .details p { margin: 5px 0; }
    .print-btn { margin-top: 20px; text-align: center; }
    .print-btn button { padding: 8px 16px; font-size: 16px; }
  </style>
</head>
<body>

<div class="ticket">
  <h2>ANIMAPLEX</h2>
  <div class="details">
    <p><strong>Ticket Code:</strong> <?php echo htmlspecialchars($row['ticket_code']); ?></p>
    <p><strong>Movie Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
    <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($row['booking_date']); ?></p>
    <p><strong>Time:</strong> <?php echo htmlspecialchars($row['booking_time']); ?></p>
    <p><strong>Seats:</strong> <?php echo htmlspecialchars($row['seats']); ?></p>
    <p><strong>Total Price:</strong> ₱<?php echo number_format($row['totalprice'], 2); ?></p>
  </div>
  <div class="print-btn">
    <button onclick="window.print()">Print Ticket</button>
  </div>
</div>

</body>
</html>
