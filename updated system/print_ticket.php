<?php
$conn = new mysqli("localhost", "root", "", "system");
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
  $stmt->close();
} else {
  echo "No ticket ID provided.";
  exit();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ticket #<?php echo htmlspecialchars($row['ticket_code']); ?></title>
  <link rel="stylesheet" href="ticket.css">
</head>
<body>

<div class="ticket">
  <h2>ANIMAPLEX</h2>
  <div class="details">
    <p><strong>Ticket Code:</strong> <?php echo htmlspecialchars($row['ticket_code']); ?></p>
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['username']); ?></p>
    <p><strong>Movie Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
    <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($row['booking_date']); ?></p>
    <p><strong>Time:</strong> <?php echo htmlspecialchars($row['booking_time']); ?></p>
    <p><strong>Seats:</strong> <?php echo htmlspecialchars($row['seats']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($row['status'])); ?></p>
    <p><strong>Total Price:</strong> ₱<?php echo number_format($row['totalprice'], 2); ?></p>
  </div>
  <div class="print-btn">
    <button onclick="window.print()">Print Ticket</button>
  </div>
</div>

</body>
</html>
