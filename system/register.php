<?php
session_start();

$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "system";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName, 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];  // Plain password, no hashing
    $confirm_password = $_POST['confirm_password'];
    $age = isset($_POST['age']) ? intval($_POST['age']) : 0;
    $birthday = isset($_POST['birthday']) ? $_POST['birthday'] : '';

    if (empty($username) || empty($password) || empty($confirm_password) || empty($birthday)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $stmt->close();

            // Insert without hashing password
            $insert = $conn->prepare("INSERT INTO users (username, password, age, birthday) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssis", $username, $password, $age, $birthday);

            if ($insert->execute()) {
                $success = "Registration successful!";
            } else {
                $error = "Error during registration: " . $conn->error;
            }

            $insert->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>SIGN-UP</title>
    <link rel="stylesheet" href="register.css" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h1>SIGN-UP</h1>

            <?php if ($error): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required />
                <i class="bx bxs-user"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required />
                <i class="bx bxs-lock-alt"></i>
            </div>

            <div class="input-box">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required />
                <i class="bx bxs-lock-alt"></i>
            </div>

            <div class="input-box">
                <input type="number" name="age" placeholder="Age" min="1" required />
                <i class="bx bxs-user-detail"></i>
            </div>

            <div class="input-box">
                <input type="date" name="birthday" placeholder="Birthday" required />
                <i class="bx bxs-calendar"></i>
            </div>

            <button type="submit" class="btn">Register</button>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </form>
    </div>
</body>
</html>
