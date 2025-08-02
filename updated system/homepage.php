<?php
session_start();

$conn = new mysqli("localhost", "root", "", "system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT movie_Id, Title, Movie, Overview FROM movie ORDER BY movie_Id DESC";
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

<nav>
  <ul>
    <li><a href="homepage.php">Home</a></li>
    <li><a href="about.php">About</a></li>
    <li><a href="services.php">Services</a></li>
    <?php if (isset($_SESSION["username"])): ?>
        <li><a href="dashboard.php">Movies</a></li>
        <li><a href="login.php">Logout</a></li>
    <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    <?php endif; ?>
  </ul>
</nav>

<header>
    <h1><img src="pngegg.png" alt="logo" height="50px" width="50px">ANIMAPLEX</h1>
</header>

<div class="hero">
    <h2>Book Your Favorite Movies Online!</h2>
    <p>Anime cinema is where imagination meets emotion every frame a vibrant story, every character a journey beyond reality.</p>
</div>

<div class="slider-container">
    <button class="arrow left-arrow">&#10094;</button>
    <div class="slider" id="slider">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="slide">
                    <div class="overview">
                        <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                        <p><?php echo htmlspecialchars($row['Overview']); ?></p>
                        <a href="details.php?id=<?php echo urlencode($row['movie_Id']); ?>"><button>View Details</button></a>
                    </div>
                    <div class="poster">
                        <img src="uploads/<?php echo htmlspecialchars($row['Movie']); ?>" alt="<?php echo htmlspecialchars($row['Title']); ?>" />
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="slide">
                <div class="overview">
                    <h3>No Movies Available</h3>
                    <p>Please check back later for new movies.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <button class="arrow right-arrow">&#10095;</button>
</div>

<footer>
    <p>&copy; 2025 ANIMAPLEX. All Rights Reserved</p>
    <div class="footer-links">
        <a href="homepage.php">Home</a>
        <a href="about.php">About</a>
        <a href="services.php">Services</a>
        <?php if (isset($_SESSION["username"])): ?>
            <a href="dashboard.php">Movies</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</footer>

<script>
// SINGLE CLEAN JAVASCRIPT IMPLEMENTATION
const slider = document.querySelector('.slider');
const slides = document.querySelectorAll('.slide');
const leftArrow = document.querySelector('.left-arrow');
const rightArrow = document.querySelector('.right-arrow');

let currentIndex = 0;
const slideCount = slides.length;

function updateSlider() {
    // Move slider by 100% for each slide
    slider.style.transform = `translateX(-${currentIndex * 100}%)`;
}

function showNext() {
    currentIndex = (currentIndex + 1) % slideCount;
    updateSlider();
}

function showPrev() {
    currentIndex = (currentIndex - 1 + slideCount) % slideCount;
    updateSlider();
}

// Initialize position
updateSlider();

// Attach event listeners
rightArrow.addEventListener('click', showNext);
leftArrow.addEventListener('click', showPrev);

// Optional: Keyboard support
document.addEventListener('keydown', (e) => {
    if(e.key === "ArrowRight") showNext();
    else if(e.key === "ArrowLeft") showPrev();
});

// Auto-slide every 5 seconds (optional)
setInterval(showNext, 5000);
</script>

</body>
</html>

<?php $conn->close(); ?>
