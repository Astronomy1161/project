<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: login.php");
  exit();
}

$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql_now_showing = "SELECT * FROM movie WHERE status = 'Now Showing' ORDER BY movie_Id DESC LIMIT 10";
$result_now_showing = $conn->query($sql_now_showing);

$sql_coming_soon = "SELECT * FROM movie WHERE status = 'Coming Soon' ORDER BY movie_Id DESC LIMIT 10";
$result_coming_soon = $conn->query($sql_coming_soon);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Movie Gallery</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<nav>
  <div class="dropdown">
    <button class="dropbtn">&#9776;</button> <!-- Hamburger icon -->
    <div class="dropdown-content">
      <a href="usero.php?username=<?php echo urlencode($_SESSION["username"]); ?>">My Orders</a>
      <a href="login.php">Log Out</a>
      <a href="message.php">Contact Us</a>
    </div>
  </div>
</nav>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>

<h2>Now Showing</h2>
<div class="gallery">
  <?php if ($result_now_showing && $result_now_showing->num_rows > 0): ?>
    <?php while($row = $result_now_showing->fetch_assoc()): ?>
      <div class="movie">
        <a href="details.php?id=<?php echo urlencode($row['movie_Id']); ?>">
          <img src="uploads/<?php echo htmlspecialchars($row['Movie']); ?>" alt="<?php echo htmlspecialchars($row['Title']); ?>">
          <div class="overlay">
            <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
            <p>Genre: <?php echo htmlspecialchars($row['Genre']); ?></p>
          </div>
        </a>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No movies currently showing.</p>
  <?php endif; ?>
</div>

<h2>Coming Soon</h2>
<div class="gallery">
  <?php if ($result_coming_soon && $result_coming_soon->num_rows > 0): ?>
    <?php while($row = $result_coming_soon->fetch_assoc()): ?>
      <div class="movie">
        <a href="details.php?id=<?php echo urlencode($row['movie_Id']); ?>">
          <img src="uploads/<?php echo htmlspecialchars($row['Movie']); ?>" alt="<?php echo htmlspecialchars($row['Title']); ?>">
          <div class="overlay">
            <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
            <p>Genre: <?php echo htmlspecialchars($row['Genre']); ?></p>
          </div>
        </a>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No upcoming movies.</p>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; 2025 ANIMAPLEX. All Rights Reserved</p>
</footer>
</body>
</html>
