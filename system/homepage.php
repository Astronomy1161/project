<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch movies from DB
$sql = "SELECT movie_Id, Title, movie, Overview FROM movie ORDER BY movie_Id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Welcome to ANIMAPLEX</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="homepage.css" />
</head>
<body>
<video autoplay muted loop id="bg-video">
  <source src="bg.mp4" type="video/mp4" />
  Your browser does not support the video tag.
</video>

<header>
    <h1><img src="logo.png" alt="logo" height="50px" width="50px">ANIMAPLEX</h1>
</header>

<nav>
    <a href="dashboard.php"><button>Movies</button></a>
    <?php if (isset($_SESSION["username"])): ?>
        <a href="login.php"><button>Logout</button></a>
    <?php else: ?>
        <a href="login.php"><button>Login</button></a>
        <a href="register.php"><button>Register</button></a>
    <?php endif; ?>
</nav>

<div class="hero">
    <h2>Book Your Favorite Movies Online!</h2>
    <p>Anime cinema is where imagination meets emotion every frame a vibrant story, every character a journey beyond reality.</p>
</div>

<div class="slider-container">
    <button class="arrow left-arrow">&#10094;</button>
    <div class="slider">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="slide">
                    <div class="overview">
                        <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                        <p><?php echo htmlspecialchars($row['Overview']); ?></p>
                        <a href="details.php?id=<?php echo $row['movie_Id']; ?>"><button>View Details</button></a>
                    </div>
                    <div class="poster">
                        <img src="uploads/<?php echo htmlspecialchars($row['movie']); ?>" alt="<?php echo htmlspecialchars($row['Title']); ?>" />
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No movies found.</p>
        <?php endif; ?>
    </div>
    <button class="arrow right-arrow">&#10095;</button>
</div>

<footer>
    &copy; ©2025 ANIMAPLEX. All rights reserved.
</footer>

<script src="slider.js"></script>
</body>
</html>
