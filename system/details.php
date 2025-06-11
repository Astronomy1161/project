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
            color: white;
        }

        nav button {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: greenyellow;
            color: black;
            font-size: 16px;
            border: none;
            padding: 12px 24px;
            border-radius: 20px;
            cursor: pointer;
            z-index: 100;
        }

        .details-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 60px;
            padding: 80px 20px;
            flex-wrap: wrap;
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
            min-height: 100vh;
        }

        .info {
            max-width: 400px;
            background: rgba(0, 0, 0, 0.35);
            padding: 30px;
            border-radius: 16px;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.6);
        }

        .info h1 {
            margin-top: 0;
            font-size: 36px;
        }

        .info p {
            font-size: 18px;
            line-height: 1.5;
        }

        .info h3 {
            margin-top: 20px;
            font-size: 24px;
        }

        .book-btn {
            margin-top: 25px;
            padding: 14px 35px;
            font-size: 18px;
            border: none;
            border-radius: 25px;
            background-color: #ffa500;
            color: black;
            cursor: pointer;
            transition: background 0.3s ease;
            display: inline-block;
        }

        .book-btn:hover {
            background-color: #ff7c00;
        }

        .poster {
            text-align: center;
        }

        .poster img {
            width: 400px;
            height: 600px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php"><button type="button">Back</button></a>
</nav>

<div class="details-container">
    <div class="poster">
        <img src="uploads/<?php echo htmlspecialchars($movie['Movie']); ?>" alt="<?php echo htmlspecialchars($movie['Title']); ?>">
    </div>

    <div class="info">
        <h1><?php echo htmlspecialchars($movie['Title']); ?></h1>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['Genre']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($movie['status']); ?></p>
        <h3>Overview</h3>
        <p><?php echo nl2br(htmlspecialchars($movie['Overview'])); ?></p>

        <?php if ($movie['status'] === 'Now Showing'): ?>
            <a href="booking.php?id=<?php echo urlencode($movie['movie_Id']); ?>">
                <button class="book-btn">Book Now</button>
            </a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
