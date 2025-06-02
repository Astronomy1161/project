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

// Fetch movie details by id
$sql = "SELECT * FROM movie WHERE movie_Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movieId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Movie not found.";
    exit();
}

$movie = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($movie['Title']); ?> - Details</title>
    <link rel="stylesheet" href="details.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('uploads/<?php echo htmlspecialchars($movie["Movie"]); ?>') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php"><button type="button"></button></a>
</nav>

<div class="details-container">
    <div class="poster">
        <img src="uploads/<?php echo htmlspecialchars($movie['Movie']); ?>" alt="<?php echo htmlspecialchars($movie['Title']); ?>">
        <?php if ($movie['status'] === 'Now Showing'): ?>
            <a href="booking.php?id=<?php echo urlencode($movie['movie_Id']); ?>">
                <button class="book-btn">Book Now</button>
            </a>
        <?php endif; ?>
    </div>

    <div class="info">
        <h1><?php echo htmlspecialchars($movie['Title']); ?></h1>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['Genre']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($movie['status']); ?></p>
        <h3>Overview</h3>
        <p><?php echo nl2br(htmlspecialchars($movie['Overview'])); ?></p>
    </div>
</div>

</body>
</html>
