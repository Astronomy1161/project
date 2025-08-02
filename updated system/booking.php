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

$movieId = $_GET['id'];
$conn = new mysqli("localhost", "root", "", "system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateTicketID($length = 11) {
    $digits = '0123456789';
    $ticketID = '';
    for ($i = 0; $i < $length; $i++) {
        $ticketID .= $digits[rand(0, strlen($digits) - 1)];
    }
    return $ticketID;
}

$sql = "SELECT Title FROM movie WHERE movie_Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $movieId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Movie not found.";
    exit();
}
$movie = $result->fetch_assoc();

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

$bookedSeats = [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $booking_date = $_POST['booking_date'] ?? '';
    $booking_time = $_POST['booking_time'] ?? '';
    $selectedSeats = $_POST['selected_seats'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'reserve';

    if (empty($selectedSeats)) {
        $error = "Please select at least one seat.";
    } else {
        $seatsArray = explode(",", $selectedSeats);
        $bookedAlready = [];

        foreach ($seatsArray as $seat) {
            $checkSeatStmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE title = ? AND booking_date = ? AND booking_time = ? AND FIND_IN_SET(?, seats) > 0");
            $checkSeatStmt->bind_param("ssss", $movie['Title'], $booking_date, $booking_time, $seat);
            $checkSeatStmt->execute();
            $seatResult = $checkSeatStmt->get_result()->fetch_assoc();
            if ($seatResult['count'] > 0) {
                $bookedAlready[] = $seat;
            }
        }

        if (count($bookedAlready) > 0) {
            $error = "The following seats are already booked: " . implode(", ", $bookedAlready);
        } else {
            $finalPricePerSeat = $ticket_price - ($ticket_price * $discountRate);
            $status = ($payment_method === 'online') ? 'paid' : 'reserved';

            foreach ($seatsArray as $seat) {
                do {
                    $ticketcode = generateTicketID();
                    $check = $conn->prepare("SELECT ticket_code FROM orders WHERE ticket_code = ?");
                    $check->bind_param("s", $ticketcode);
                    $check->execute();
                    $checkResult = $check->get_result();
                } while ($checkResult->num_rows > 0);

                $insert = $conn->prepare("INSERT INTO orders (ticket_code, title, username, booking_date, booking_time, seats, tickets, status, totalprice) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)");
                $insert->bind_param("sssssssd", $ticketcode, $movie['Title'], $username, $booking_date, $booking_time, $seat, $status, $finalPricePerSeat);
                $insert->execute();
            }

            $paymentMsg = $payment_method === 'online' ? "Payment successful. " : "Seats reserved. Please pay at the counter.";

            echo "<script>alert('Booking confirmed for seats: $selectedSeats. $paymentMsg'); window.location='dashboard.php';</script>";
            exit();
        }
    }
}

$selected_date = $_POST['booking_date'] ?? $_GET['date'] ?? '';
$selected_time = $_POST['booking_time'] ?? $_GET['time'] ?? '';

if ($selected_date && $selected_time) {
    $seatQuery = $conn->prepare("SELECT seats FROM orders WHERE title = ? AND booking_date = ? AND booking_time = ?");
    $seatQuery->bind_param("sss", $movie['Title'], $selected_date, $selected_time);
    $seatQuery->execute();
    $seatRes = $seatQuery->get_result();
    while ($row = $seatRes->fetch_assoc()) {
        $seatsArr = explode(",", $row['seats']);
        $bookedSeats = array_merge($bookedSeats, $seatsArr);
    }
    $bookedSeats = array_unique($bookedSeats);
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

        <?php if (!empty($error)) {
            echo "<p style='color:red;'>$error</p>";
        } ?>

        <form method="POST" class="booking-form" id="bookingForm">
            <label>Date:</label>
            <input type="date" name="booking_date" id="booking_date" value="<?php echo htmlspecialchars($selected_date ?? ''); ?>" required />

            <label>Time:</label>
            <select name="booking_time" id="booking_time" required>
                <option value="">Select time</option>
                <?php
                $times = ["06:00","09:00","12:00","15:00","18:00","21:00"];
                foreach ($times as $time) {
                    $selected = (($selected_time ?? '') === $time) ? "selected" : "";
                    echo "<option value=\"$time\" $selected>$time</option>";
                }
                ?>
            </select>

            <label>Selected Seats:</label>
            <input type="text" id="selectedSeats" name="selected_seats" readonly required />

            <p><strong>Price per Ticket:</strong> ₱<?php echo $ticket_price; ?></p>
            <?php if ($isSenior): ?>
                <p style="color: green;"><strong>Senior Discount: 20% off</strong></p>
            <?php endif; ?>
            <p><strong>Total Price:</strong> ₱<span id="totalPrice"><?php echo $ticket_price; ?></span></p>

            <label>Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="">Select payment method</option>
                <option value="online">Online Payment</option>
                <option value="reserve">Reserve (Pay Later)</option>
            </select>

            <button type="button" onclick="openSeatMap()" class="seat-btn">Select Seats</button>
            <button type="submit" class="book-btn">Confirm Booking</button>
        </form>
    </div>
</div>

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
                    echo "<button type='button' class='seat' data-seat='$seatNumber' onclick='toggleSeat(\"$seatNumber\")'>$seatNumber</button>";
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
const ticketPrice = parseFloat(document.querySelector('meta[name="ticket-price"]').getAttribute("content"));
const discountRate = parseFloat(document.querySelector('meta[name="discount-rate"]').getAttribute("content"));
const totalPriceElem = document.getElementById("totalPrice");
const bookedSeats = <?php echo json_encode($bookedSeats); ?>;
let selectedSeats = [];

function updateTotalPrice() {
    const tickets = selectedSeats.length;
    const total = ticketPrice * tickets;
    const discount = total * discountRate;
    const finalPrice = total - discount;
    totalPriceElem.textContent = finalPrice.toFixed(2);
}

function openSeatMap() {
    const date = document.getElementById('booking_date').value;
    const time = document.getElementById('booking_time').value;
    if (!date || !time) {
        alert("Please select a date and time first.");
        return;
    }
    document.getElementById("seatMapModal").style.display = "block";
    disableBookedSeats();
}

function closeSeatMap() {
    document.getElementById("seatMapModal").style.display = "none";
}

function disableBookedSeats() {
    const seats = document.querySelectorAll(".seat");
    seats.forEach(seat => {
        const seatNum = seat.getAttribute("data-seat");
        if (bookedSeats.includes(seatNum)) {
            seat.disabled = true;
            seat.style.backgroundColor = "#ccc";
            seat.title = "Seat already booked";
        } else {
            seat.disabled = false;
            seat.style.backgroundColor = selectedSeats.includes(seatNum) ? "#4CAF50" : "";
            seat.title = "";
        }
    });
}

function toggleSeat(seatNumber) {
    if (bookedSeats.includes(seatNumber)) {
        alert("This seat is already booked. Please choose another.");
        return;
    }
    
    if (selectedSeats.includes(seatNumber)) {
        selectedSeats = selectedSeats.filter(s => s !== seatNumber);
    } else {
        selectedSeats.push(seatNumber);
    }
    document.getElementById("selectedSeats").value = selectedSeats.join(",");
    updateTotalPrice();
    disableBookedSeats();
}

// Update booked seats when date/time changes
document.getElementById('booking_date').addEventListener('change', updateBookedSeats);
document.getElementById('booking_time').addEventListener('change', updateBookedSeats);

function updateBookedSeats() {
    const date = document.getElementById('booking_date').value;
    const time = document.getElementById('booking_time').value;
    
    if (date && time) {
        // Clear current selections
        selectedSeats = [];
        document.getElementById("selectedSeats").value = "";
        updateTotalPrice();
        
        // Fetch booked seats for this date/time via AJAX would be ideal,
        // but for now we'll require a page refresh to get updated data
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const dateInput = document.createElement('input');
        dateInput.name = 'booking_date';
        dateInput.value = date;
        
        const timeInput = document.createElement('input');
        timeInput.name = 'booking_time';
        timeInput.value = time;
        
        form.appendChild(dateInput);
        form.appendChild(timeInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>
