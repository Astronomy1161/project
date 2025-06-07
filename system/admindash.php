<?php
$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // ADD MOVIE
  if (!isset($_POST['update_status']) && !isset($_POST['delete_cancelled']) && !isset($_POST['delete_movie'])) {
    $Title = $_POST['Title'] ?? '';
    $Genre = $_POST['Genre'] ?? '';
    $Overview = $_POST['Overview'] ?? '';

    if (isset($_FILES['movie_image']) && $_FILES['movie_image']['error'] === UPLOAD_ERR_OK) {
      $movie_image = $_FILES['movie_image']['name'];
      $target_dir = "uploads/";
      $target_file = $target_dir . basename($movie_image);

      if (move_uploaded_file($_FILES["movie_image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO movie (Movie, Title, Genre, Overview) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $movie_image, $Title, $Genre, $Overview);
        if ($stmt->execute()) {
          $message = "New movie added successfully!";
        } else {
          $message = "Error: " . $stmt->error;
        }
        $stmt->close();
      } else {
        $message = "Error uploading movie image.";
      }
    } else {
      $message = "Please upload a movie image.";
    }
  }

  // UPDATE MOVIE STATUS
  if (isset($_POST['update_status'])) {
    $movie_Id = $_POST['movie_Id'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    if ($movie_Id && $new_status) {
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
  }

  // DELETE CANCELLED ORDERS
  if (isset($_POST['delete_cancelled'])) {
    $sql = "DELETE FROM orders WHERE status = 'cancelled'";
    if ($conn->query($sql) === TRUE) {
      $message = "All cancelled orders have been deleted successfully!";
    } else {
      $message = "Error deleting cancelled orders: " . $conn->error;
    }
  }

  // DELETE MOVIE
  if (isset($_POST['delete_movie'])) {
    $movie_Id = intval($_POST['movie_Id'] ?? 0);

    if ($movie_Id > 0) {
      // Fetch image file to delete
      $movie_query = $conn->query("SELECT Movie FROM movie WHERE movie_Id = $movie_Id");
      if ($movie_query && $movie_row = $movie_query->fetch_assoc()) {
        $movie_image_path = "uploads/" . $movie_row['Movie'];
        if (file_exists($movie_image_path)) {
          unlink($movie_image_path);
        }
      }

      // Delete movie record
      $sql = "DELETE FROM movie WHERE movie_Id = $movie_Id";
      if ($conn->query($sql) === TRUE) {
        $message = "Movie deleted successfully!";
      } else {
        $message = "Error deleting movie: " . $conn->error;
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ANIMAPLEX Dashboard</title>
  <link rel="stylesheet" href="admindash.css" />
</head>
<body>

<div class="sidebar">
  <h2>ANIMAPLEX</h2>
  <nav>
    <a data-section="orders" class="active">Orders</a>
    <a data-section="messages">Messages</a>
    <a data-section="users">Users</a>
    <a data-section="movies">Movies</a>
    <a href="login.php" class="logout-button">◀️Logout</a>
  </nav>
</div>

<div class="main">

<div id="orders">
  <h1>Orders</h1>
  <input type="text" class="section-search" placeholder="Search orders...">
  <div id="delete">
  <form method="POST">
  <button type="submit" name="delete_cancelled">Delete Cancelled Orders</button>
</form>
</div>
  <table>
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Username</th>
        <th>Ticket Code</th>
        <th>Movie Title</th>
        <th>Booking Date</th>
        <th>Booking Time</th>
        <th>Seats</th>
        <th>Status</th>
        <th>Total Price</th>
        <th>Print</th>
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
          echo "<td>".htmlspecialchars($row['ticket_code'])."</td>";
          echo "<td>".htmlspecialchars($row['title'])."</td>";
          echo "<td>".htmlspecialchars($row['booking_date'])."</td>";
          echo "<td>".htmlspecialchars($row['booking_time'])."</td>";
          echo "<td>".htmlspecialchars($row['seats'])."</td>";
          echo "<td>".htmlspecialchars($row['status'])."</td>";
          echo "<td>₱".number_format($row['totalprice'], 2)."</td>";

          if ($row['status'] === 'reserved' || $row['status'] === 'paid') {
            echo "<td><a href='print_ticket.php?orderid=".urlencode($row['orderid'])."' target='_blank' style='color:orange; font-weight:bold;'>Print Ticket</a></td>";
          } else {
            echo "<td style='color:orange;'>N/A</td>";
          }

          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='11'>No orders found.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<div id="messages" class="hidden">
  <h1>Messages</h1>
  <input type="text" class="section-search" placeholder="Search messages...">
  <table>
    <thead>
      <tr>
        <th>Message ID</th>
        <th>Username</th>
        <th>Message</th>
        <th>Sent At</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = "SELECT * FROM messages ORDER BY created_at DESC";
      $result = $conn->query($sql);
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>#".htmlspecialchars($row['id'])."</td>";
          echo "<td>".htmlspecialchars($row['username'])."</td>";
          echo "<td>".nl2br(htmlspecialchars($row['message']))."</td>";
          echo "<td>".htmlspecialchars($row['created_at'])."</td>";
          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='4'>No messages found.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<div id="users" class="hidden">
  <h1>Users</h1>
  <input type="text" class="section-search" placeholder="Search users...">
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
  <?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <button id="toggleAddMovieBtn" style="margin-bottom: 15px; padding: 10px 15px; background:orange; color:black; border:none; border-radius:4px; cursor:pointer;">
    ➕ Add Movie
  </button>

  <div id="addMovieFormContainer" class="hidden">
    <form method="POST" enctype="multipart/form-data">
      <label>Title:</label><input type="text" name="Title" required>
      <label>Genre:</label><input type="text" name="Genre" required>
      <label>Overview:</label><textarea name="Overview" required></textarea>
      <label>Poster Image:</label><input type="file" name="movie_image" accept="image/*" required>
      <button type="submit">Add Movie</button>
    </form>
  </div>

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
                <form method='POST' style='margin-top:10px; display:flex; gap:10px; align-items:center;'>
                  <input type='hidden' name='movie_Id' value='".htmlspecialchars($row['movie_Id'])."'>
                  <select name='new_status'>
                    <option value='Coming Soon'".($row['status']=='Coming Soon'?' selected':'').">Coming Soon</option>
                    <option value='Now Showing'".($row['status']=='Now Showing'?' selected':'').">Now Showing</option>
                  </select>
                  <button type='submit' name='update_status'>Update</button>
                </form>
  <form method='POST' onsubmit=\"return confirm('Are you sure you want to delete this movie?');\" style='margin-top:5px;'>
    <input type='hidden' name='movie_Id' value='".htmlspecialchars($row['movie_Id'])."'>
    <button type='submit' name='delete_movie' style='padding:4px 8px; background:darkorange; color:white; border:none; border-radius:4px; cursor:pointer;'>Delete</button>
  </form>
              </div>";
      }
      echo "</div>";
    } else {
      echo "<p>No movies found.</p>";
    }
  ?>
</div>

</div>

<script>
document.querySelectorAll('.sidebar nav a').forEach(link => {
  link.addEventListener('click', () => {
    document.querySelectorAll('.sidebar nav a').forEach(a => a.classList.remove('active'));
    link.classList.add('active');
    document.querySelectorAll('.main > div').forEach(div => div.classList.add('hidden'));
    const section = link.getAttribute('data-section');
    if(section){
      document.getElementById(section).classList.remove('hidden');
    }
  });
});

document.getElementById('toggleAddMovieBtn').addEventListener('click', () => {
  const formContainer = document.getElementById('addMovieFormContainer');
  formContainer.classList.toggle('hidden');
});

// Search bar filtering functionality
document.querySelectorAll('.section-search').forEach(searchInput => {
  searchInput.addEventListener('input', () => {
    const section = searchInput.closest('div');
    const filter = searchInput.value.toLowerCase();
    const table = section.querySelector('table');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
      const rowText = row.textContent.toLowerCase();
      if (rowText.indexOf(filter) > -1) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
});
</script>

</body>
</html>
