<?php
$conn = new mysqli("localhost", "root", "", "system");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // UPDATE ORDER STATUS
  if (isset($_POST['update_order_btn'])) {
    $orderid = intval($_POST['orderid']);
    $new_order_status = $_POST['update_order_status'] ?? '';

    if ($orderid > 0 && $new_order_status) {
      $sql = "UPDATE orders SET status = ? WHERE orderid = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("si", $new_order_status, $orderid);
      if ($stmt->execute()) {
        $message = "Order status updated successfully!";
      } else {
        $message = "Error updating order status: " . $stmt->error;
      }
      $stmt->close();
    }
  }
  
  // ADD NEW MOVIE
  if (!isset($_POST['update_status']) && !isset($_POST['delete_cancelled']) && !isset($_POST['delete_movie']) && !isset($_POST['update_order_btn'])) {
    $Title = $_POST['Title'] ?? '';
    $Genre = $_POST['Genre'] ?? '';
    $Overview = $_POST['Overview'] ?? '';

    if (isset($_FILES['movie_image']) && $_FILES['movie_image']['error'] === UPLOAD_ERR_OK) {
      $movie_image = $_FILES['movie_image']['name'];
      $target_dir = "uploads/";
      
      // Create uploads directory if it doesn't exist
      if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
      }
      
      $target_file = $target_dir . basename($movie_image);

      // Basic file validation
      $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
      $allowed_types = array("jpg", "jpeg", "png", "gif");
      
      if (in_array($imageFileType, $allowed_types)) {
        if (move_uploaded_file($_FILES['movie_image']['tmp_name'], $target_file)) {
          $movie_Id = uniqid('movie_');
          $sql = "INSERT INTO movie (movie_Id, Movie, Title, Genre, Overview, status) VALUES (?, ?, ?, ?, ?, 'Coming Soon')";
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
      } else {
        $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
      }
    } else {
      $message = "Please select a movie image.";
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
      $message = "Cancelled orders deleted successfully!";
    } else {
      $message = "Error deleting cancelled orders: " . $conn->error;
    }
  }

  // DELETE MOVIE
  if (isset($_POST['delete_movie'])) {
    $movie_Id = $_POST['movie_Id'] ?? '';

    if ($movie_Id) {
      // Fetch image file to delete
      $movie_query = $conn->prepare("SELECT Movie FROM movie WHERE movie_Id = ?");
      $movie_query->bind_param("s", $movie_Id);
      $movie_query->execute();
      $movie_result = $movie_query->get_result();
      
      if ($movie_result && $movie_row = $movie_result->fetch_assoc()) {
        $movie_image_path = "uploads/" . $movie_row['Movie'];
        if (file_exists($movie_image_path)) {
          unlink($movie_image_path);
        }
      }

      // Delete movie record
      $sql = "DELETE FROM movie WHERE movie_Id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $movie_Id);
      if ($stmt->execute()) {
        $message = "Movie deleted successfully!";
      } else {
        $message = "Error deleting movie: " . $stmt->error;
      }
      $stmt->close();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>🍿ANIMAPLEX</title>
  <link rel="stylesheet" href="admindash.css" />
</head>
<body>

<div class="sidebar">
  <h2>🍿ANIMAPLEX</h2>
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
      $sql = "SELECT * FROM orders ORDER BY booking_date DESC, booking_time DESC";
      $result = $conn->query($sql);
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>#".htmlspecialchars($row['orderid'])."</td>";
          echo "<td>".htmlspecialchars($row['username'])."</td>";
          echo "<td>".htmlspecialchars($row['ticket_code'])."</td>";
          echo "<td>".htmlspecialchars($row['title'])."</td>";
          echo "<td>".htmlspecialchars($row['booking_date'])."</td>";
          echo "<td>".htmlspecialchars($row['booking_time'])."</td>";
          echo "<td>".htmlspecialchars($row['seats'])."</td>";

          // Status with update dropdown
          echo "<td>
                  <form method='POST' style='display:flex; gap:5px; align-items:center;'>
                    <input type='hidden' name='orderid' value='".htmlspecialchars($row['orderid'])."'>
                    <select name='update_order_status'>
                      <option value='reserved'".($row['status']=='reserved'?' selected':'').">Reserved</option>
                      <option value='paid'".($row['status']=='paid'?' selected':'').">Paid</option>
                      <option value='cancelled'".($row['status']=='cancelled'?' selected':'').">Cancelled</option>
                    </select>
                    <button type='submit' name='update_order_btn' style='padding:3px 6px; background:orange; border:none; border-radius:4px; cursor:pointer;'>Update</button>
                  </form>
                </td>";

          echo "<td>₱".number_format($row['totalprice'], 2)."</td>";

          if ($row['status'] === 'reserved' || $row['status'] === 'paid') {
            echo "<td><a href='print_ticket.php?orderid=".urlencode($row['orderid'])."' target='_blank' style='color:orange; font-weight:bold;'>Print Ticket</a></td>";
          } else {
            echo "<td style='color:orange;'>N/A</td>";
          }

          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='10'>No orders found.</td></tr>";
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
    <thead><tr><th>User ID</th><th>Username</th><th>Age</th><th>Birthday</th><th>Role</th></tr></thead>
    <tbody>
      <?php
        $sql = "SELECT id, username, age, birthday, role FROM users WHERE role = 'user'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>".htmlspecialchars($row['username'])."</td><td>".htmlspecialchars($row['age'])."</td><td>".htmlspecialchars($row['birthday'])."</td><td>".htmlspecialchars($row['role'])."</td></tr>";
          }
        } else { echo "<tr><td colspan='5'>No users found.</td></tr>"; }
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
    $sql = "SELECT * FROM movie ORDER BY movie_Id DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
      echo "<div class='gallery'>";
      while ($row = $result->fetch_assoc()) {
        echo "<div class='poster-card'>
                <img src='uploads/".htmlspecialchars($row['Movie'])."' alt='".htmlspecialchars($row['Title'])."'>
                <div class='poster-title'>".htmlspecialchars($row['Title'])."</div>
                <div class='poster-genre'>".htmlspecialchars($row['Genre'])."</div>
                <div class='poster-status'>Status: ".htmlspecialchars($row['status'] ?? 'Coming Soon')."</div>
                <form method='POST' style='margin-top:10px; display:flex; gap:10px; align-items:center;'>
                  <input type='hidden' name='movie_Id' value='".htmlspecialchars($row['movie_Id'])."'>
                  <select name='new_status'>
                    <option value='Coming Soon'".(($row['status'] ?? 'Coming Soon')=='Coming Soon'?' selected':'').">Coming Soon</option>
                    <option value='Now Showing'".(($row['status'] ?? '')=='Now Showing'?' selected':'').">Now Showing</option>
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
    
    if (section.id === 'movies') {
      const posters = section.querySelectorAll('.poster-card');
      posters.forEach(card => {
        const title = card.querySelector('.poster-title').textContent.toLowerCase();
        const genre = card.querySelector('.poster-genre').textContent.toLowerCase();
        card.style.display = (title.includes(filter) || genre.includes(filter)) ? '' : 'none';
      });
    } else {
      const table = section.querySelector('table');
      if (table) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
          const rowText = row.textContent.toLowerCase();
          if (rowText.indexOf(filter) > -1) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      }
    }
  });
});
</script>

</body>
</html>
