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

$movieId = $_GET['id']; // Keep as string since movie_Id might be string

$conn = new mysqli("localhost", "root", "", "system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM movie WHERE movie_Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $movieId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Movie not found.";
    exit();
}

$movie = $result->fetch_assoc();
$stmt->close();
$conn->close();
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

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #ff6f00;
            color: white;
            font-size: 18px;
            font-weight: 600;
            border: none;
            padding: 14px 35px;
            border-radius: 25px;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 111, 0, 0.3);
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }

        .back-btn:hover {
            background: #ff8f00;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 111, 0, 0.4);
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
            text-decoration: none;
            text-align: center;
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
    <a href="dashboard.php" class="back-btn">Back</a>
</nav>

<div class="details-container">
    <div class="poster">
        <img src="uploads/<?php echo htmlspecialchars($movie['Movie']); ?>" alt="<?php echo htmlspecialchars($movie['Title']); ?>">
    </div>

    <div class="info">
        <h1><?php echo htmlspecialchars($movie['Title']); ?></h1>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['Genre']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($movie['status'] ?? 'Coming Soon'); ?></p>
        <h3>Overview</h3>
        <p><?php echo nl2br(htmlspecialchars($movie['Overview'])); ?></p>

        <?php if (($movie['status'] ?? '') === 'Now Showing'): ?>
            <a href="booking.php?id=<?php echo urlencode($movie['movie_Id']); ?>" class="book-btn">Book Now</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
