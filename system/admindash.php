<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle form submission to add a new movie
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['movie_Id']) && !isset($_POST['update_status'])) {
  $movie_Id = $_POST['movie_Id'];
  $Title = $_POST['Title'];
  $Genre = $_POST['Genre'];
  $Overview = $_POST['Overview'];

  $movie_image = $_FILES['movie_image']['name'];
  $target_dir = "uploads/";
  $target_file = $target_dir . basename($movie_image);

  if (move_uploaded_file($_FILES["movie_image"]["tmp_name"], $target_file)) {
    $sql = "INSERT INTO movie (movie_Id, Movie, Title, Genre, Overview) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $movie_Id, $movie_image, $Title, $Genre, $Overview);
    if ($stmt->execute()) {
      $message = "New movie added successfully!";
    } else {
      $message = "Error: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $message = "Error uploading movie image.";
  }
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
  $movie_Id = $_POST['movie_Id'];
  $new_status = $_POST['new_status'];
  $sql = "UPDATE movie SET status = ? WHERE movie_Id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $new_status, $movie_Id);
  if ($stmt->execute()) {
    $message = "Status updated successfully!";
  } else {
    $message = "Error updating status: " . $stmt->error;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ANIMAPLEX Dashboard</title>
  <link rel="stylesheet" href="admindash.css">
</head>
<body>

<div class="sidebar">
  <h2>ANIMAPLEX</h2>
  <nav>
    <a data-section="orders" class="active">Orders</a>
    <a data-section="messages">Messages</a>
    <a data-section="users">Users</a>
    <a data-section="movies">Movies</a>
  </nav>
</div>

<div class="main">

<!-- Orders Section -->
<div id="orders">
  <h1>Orders</h1>
  <table>
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Username</th>
        <th>Ticket Code</th> <!-- Added header -->
        <th>Movie Title</th>
        <th>Booking Date</th>
        <th>Booking Time</th>
        <th>Seats</th>
        <th>Tickets</th>
        <th>Status</th>
        <th>Total Price</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = "SELECT * FROM orders";
      $result = $conn->query($sql);
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>#".$row['orderid']."</td>";
          echo "<td>".htmlspecialchars($row['username'])."</td>";
          echo "<td>".htmlspecialchars($row['ticket_code'])."</td>";  // Display ticket_code here
          echo "<td>".htmlspecialchars($row['title'])."</td>";
          echo "<td>".htmlspecialchars($row['booking_date'])."</td>";
          echo "<td>".htmlspecialchars($row['booking_time'])."</td>";
          echo "<td>".htmlspecialchars($row['seats'])."</td>";
          echo "<td>".htmlspecialchars($row['tickets'])."</td>";
          echo "<td>".htmlspecialchars($row['status'])."</td>";
          echo "<td>₱".number_format($row['totalprice'], 2)."</td>";
          echo "<td>
                  <div class='dropdown'>
                    <button>⋮</button>
                    <div class='dropdown-content'>
                      <a href='#'>Confirm</a>
                      <a href='#'>Cancel</a>
                      <a href='#'>Details</a>
                    </div>
                  </div>
                </td>";
          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='11'>No orders found.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<div id="messages" class="hidden"><h1>Messages</h1></div>

<div id="users" class="hidden">
  <h1>Users</h1>
  <table>
    <thead><tr><th>User ID</th><th>Username</th><th>Password</th></tr></thead>
    <tbody>
      <?php
        $sql = "SELECT id, username, password FROM users WHERE role = 'user'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>".htmlspecialchars($row['username'])."</td><td>".htmlspecialchars($row['password'])."</td></tr>";
          }
        } else { echo "<tr><td colspan='3'>No users found.</td></tr>"; }
      ?>
    </tbody>
  </table>
</div>

<div id="movies" class="hidden">
  <h1>Movies</h1>
  <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
  <form method="POST" enctype="multipart/form-data">
    <label>Movie ID:</label><input type="text" name="movie_Id" required>
    <label>Title:</label><input type="text" name="Title" required>
    <label>Genre:</label><input type="text" name="Genre" required>
    <label>Overview:</label><textarea name="Overview" required></textarea>
    <label>Poster Image:</label><input type="file" name="movie_image" accept="image/*" required>
    <button type="submit">Add Movie</button>
  </form>
  <?php
    $sql = "SELECT * FROM movie";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
      echo "<div class='gallery'>";
      while ($row = $result->fetch_assoc()) {
        echo "<div class='poster-card'>
              <img src='uploads/".htmlspecialchars($row['Movie'])."' alt='".htmlspecialchars($row['Title'])."'>
              <div class='poster-title'>".htmlspecialchars($row['Title'])."</div>
              <div class='poster-genre'>".htmlspecialchars($row['Genre'])."</div>
              
              <form method='POST' style='margin-top:10px;'>
                <input type='hidden' name='movie_Id' value='".htmlspecialchars($row['movie_Id'])."'>
                <select name='new_status'>
                  <option value='Coming Soon'".($row['status']=='Coming Soon'?' selected':'').">Coming Soon</option>
                  <option value='Now Showing'".($row['status']=='Now Showing'?' selected':'').">Now Showing</option>
                </select>
                <button type='submit' name='update_status'>Update</button>
              </form>
              </div>";
      }
      echo "</div>";
    } else { echo "<p>No movies found.</p>"; }
  ?>
</div>

</div>

<script>
const links = document.querySelectorAll('.sidebar nav a');
const sections = document.querySelectorAll('.main > div');
links.forEach(link => {
  link.addEventListener('click', () => {
    links.forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    sections.forEach(sec => sec.classList.add('hidden'));
    document.getElementById(link.getAttribute('data-section')).classList.remove('hidden');
  });
});
</script>

</body>
</html>
