<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "system";
$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName, 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION["username"];
    $message = trim($_POST['message']);

    if (empty($message)) {
        $error = "Please enter your message.";
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $message);

        if ($stmt->execute()) {
            $success = "Your message has been sent. Thank you!";
        } else {
            $error = "Error sending message: " . $conn->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact Us - Feedback</title>
    <link rel="stylesheet" href="message.css" />
</head>
<body>
<nav>
    <button onclick="window.location.href='dashboard.php'" class="back-btn">← Back to Dashboard</button>
</nav>

<div class="container">
    <h1>Send Us Your Feedback</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <textarea name="message" placeholder="Write your message here..." required></textarea>
        <button type="submit" class="send-btn">Send Message</button>
    </form>
</div>
</body>
</html>
