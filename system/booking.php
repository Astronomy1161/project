<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No movie selected.";
    exit();
}

$movieId = intval($_GET['id']);

// DB connection
$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate 11-digit ticket ID
function generateTicketID($length = 11) {
    $digits = '0123456789';
    $ticketcode = '';
    for ($i = 0; $i < $length; $i++) {
        $ticketID .= $digits[rand(0, strlen($digits) - 1)];
    }
    return $ticketID;
}

// Fetch movie title
$sql = "SELECT Title FROM movie WHERE movie_Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movieId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Movie not found.";
    exit();
}
$movie = $result->fetch_assoc();

// Fetch user age
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT age FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

$userAge = $user['age'];
$isSenior = ($userAge >= 60);
$ticket_price = 300;
$discountRate = $isSenior ? 0.2 : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $tickets = intval($_POST['tickets']);
    $selectedSeat = $_POST['selected_seat'];

    $totalprice = $ticket_price * $tickets;
    $discount = $totalprice * $discountRate;
    $finalPrice = $totalprice - $discount;

    // Generate unique ticket ID and ensure it doesn't exist yet
    do {
        $ticketcode = generateTicketID();
        $check = $conn->prepare("SELECT ticket_code FROM orders WHERE ticket_code = ?");
        $check->bind_param("s", $ticketcode);
        $check->execute();
        $checkResult = $check->get_result();
    } while ($checkResult->num_rows > 0);

    $insert = $conn->prepare("INSERT INTO orders (ticket_code, title, username, booking_date, booking_time, seats, tickets, status, totalprice) VALUES (?, ?, ?, ?, ?, ?, ?, 'reserved', ?)");
    $insert->bind_param("ssssssid", $ticketcode, $movie['Title'], $username, $booking_date, $booking_time, $selectedSeat, $tickets, $finalPrice);

    if ($insert->execute()) {
        echo "<script>alert('Booking confirmed! Your Ticket ID: $ticketID'); window.location='dashboard.php';</script>";
        exit();
    } else {
        echo "Booking failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Book: <?php echo htmlspecialchars($movie['Title']); ?></title>
<link rel="stylesheet" href="booking.css" />
<meta name="ticket-price" content="<?php echo $ticket_price; ?>" />
<meta name="discount-rate" content="<?php echo $discountRate; ?>" />
</head>
<body>

<nav>
    <a href="dashboard.php"><button type="button">← Back to Dashboard</button></a>
</nav>

<div class="details-container">
    <div class="info">
        <h1>Book: <?php echo htmlspecialchars($movie['Title']); ?></h1>

        <form method="POST" class="booking-form">
            <label>Date:</label>
            <input type="date" name="booking_date" required />

            <label>Time:</label>
            <select name="booking_time" required>
                <option value="06:00">6:00 AM</option>
                <option value="09:00">9:00 AM</option>
                <option value="12:00">12:00 PM</option>
                <option value="15:00">3:00 PM</option>
                <option value="18:00">6:00 PM</option>
                <option value="21:00">9:00 PM</option>
            </select>

            <label>Tickets:</label>
            <input type="number" id="tickets" name="tickets" min="1" max="10" value="1" required />

            <label>Selected Seat:</label>
            <input type="text" id="selectedSeat" name="selected_seat" readonly required />

            <p><strong>Price per Ticket:</strong> ₱<?php echo $ticket_price; ?></p>
            <?php if ($isSenior): ?>
                <p style="color: green;"><strong>Senior Discount: 20% off</strong></p>
            <?php endif; ?>
            <p><strong>Total Price:</strong> ₱<span id="totalPrice"><?php echo $ticket_price; ?></span></p>

            <button type="button" onclick="openSeatMap()" class="seat-btn">Select Seat</button>
            <button type="submit" class="book-btn">Confirm Booking</button>
        </form>
    </div>
</div>

<!-- Seat Map Modal -->
<div id="seatMapModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeSeatMap()">&times;</span>
        <h2>SCREEN</h2>
        <div class="seat-map">
            <?php
            $rows = range('A', 'J');
            $cols = range(1, 10);
            foreach ($rows as $row) {
                foreach ($cols as $col) {
                    $seatNumber = $row . $col;
                    echo "<button type='button' class='seat' onclick='selectSeat(\"$seatNumber\")'>$seatNumber</button>";
                }
            }
            ?>
        </div>
    </div>
</div>

<script src="booking.js"></script>
</body>
</html>
