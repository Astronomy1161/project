<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("User not specified.");
}
$username = $_GET['username'];

if ($_SESSION["username"] !== $username) {
    die("Unauthorized access.");
}

$conn = new mysqli("localhost", "root", "", "system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderid'])) {
    $orderid = (int)$_POST['orderid'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE orderid = ? AND username = ? AND status != 'cancelled'");
    $stmt->bind_param("is", $orderid, $username);
    $stmt->execute();
    $stmt->close();
}

$sql = "SELECT * FROM orders WHERE username = ? ORDER BY booking_date DESC, booking_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Your Orders <?php echo htmlspecialchars($username); ?></title>
<link rel="stylesheet" href="usero.css">
</head>
<body>

<h1><?php echo htmlspecialchars($username);?>'s Booking List</h1>
<p><a href="dashboard.php">Back</a></p>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>Ticket Code</th>
            <th>Movie Title</th>
            <th>Booking Date</th>
            <th>Booking Time</th>
            <th>Seats</th>
            <th>Status</th>
            <th>Total Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['ticket_code']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
            <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
            <td><?php echo htmlspecialchars($row['seats']); ?></td>
            <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
            <td>₱<?php echo number_format($row['totalprice'], 2); ?></td>
            <td>
                <?php if ($row['status'] !== 'cancelled'): ?>
                    <form method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                        <input type="hidden" name="orderid" value="<?php echo (int)$row['orderid']; ?>">
                        <button type="submit">Cancel</button>
                    </form>
                <?php else: ?>
                    Cancelled
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p>No orders found for this user.</p>
<?php endif; ?>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
