<?php
session_start(); // Start the session

$conn = new mysqli("localhost", "root", "", "system", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Query the database for the user
        $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Set session variables
            $_SESSION["username"] = $row['username'];
            $_SESSION["role"] = $row['role'];

            // Redirect based on role
            if ($row['role'] == 'admin') {
                header("Location: admindash.php");
                exit();
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Form</title>
  <link rel="stylesheet" href="login.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<nav>
  <ul class="links">
    <b style="color: white">Home</b>
    <b style="color: white">About Us</b>
    <b style="color: white">Services</b>
  </ul>
</nav>

<div class="wrapper">
  <form action="login.php" method="POST">
    <h1>SIGN-IN</h1>

    <?php if (!empty($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

    <div class="input-box">
      <input type="text" name="username" placeholder="Username" required>
      <i class='bx bxs-user'></i>
    </div>

    <div class="input-box">
      <input type="password" name="password" placeholder="Password" required>
      <i class='bx bxs-lock-alt'></i>
    </div>

    <div class="remember-forgot">
      <label><input type="checkbox">Remember Me</label>
      <a href="#">Forgot Password</a>
    </div>

    <button type="submit" class="btn">Login</button>

    <div class="register-link">
      <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
  </form>
</div>

<div class="footer">
  <p>&copy; 2025 ANIMAPLEX. All Rights Reserve</p>
  </div>
</div>

</body>
</html>
