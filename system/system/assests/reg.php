<?php
session_start(); // Start session for CSRF token and messages
require_once "../db_connect.php"; // Adjust path if necessary

// Function to generate a CSRF token
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to validate a CSRF token
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    unset($_SESSION['csrf_token']); // Consume token
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid request (CSRF token missing or invalid).";
        header("Location: reg.php");
        exit();
    }

    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $number = htmlspecialchars(trim($_POST['num']));
    $password = $_POST['password'];
    $confirmpass = $_POST['conpass'];
    $gender = isset($_POST['gender']) ? htmlspecialchars($_POST['gender']) : null; // Handle gender

    $errors = [];

    // Basic server-side validation
    if (empty($fullname) || empty($username) || empty($email) || empty($number) || empty($password) || empty($confirmpass)) {
        $errors[] = "All fields are required.";
    }
    if ($password !== $confirmpass) {
        $errors[] = "Passwords do not match!";
    }
    if (strlen($password) < 8) { // Minimum password length
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check for existing username or email using prepared statements
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
        if ($stmt_check === false) {
            error_log("Registration Check Prepare Failed: " . $conn->error);
            $errors[] = "An internal error occurred. Please try again.";
        } else {
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Username or Email is already registered!";
            }
            $stmt_check->close();
        }
    }

    if (count($errors) > 0) {
        $_SESSION['message'] = implode("<br>", $errors); // Join errors for display
        header("Location: reg.php");
        exit();
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user using prepared statements
        $stmt_insert = $conn->prepare("INSERT INTO users (fullname, username, email, num, password, gender) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt_insert === false) {
            error_log("Registration Insert Prepare Failed: " . $conn->error);
            $_SESSION['message'] = "An internal error occurred. Please try again.";
        } else {
            $stmt_insert->bind_param("ssssss", $fullname, $username, $email, $number, $hashedPassword, $gender);

            if ($stmt_insert->execute()) {
                $_SESSION['message'] = "Registration successful! You can now log in.";
                header("Location: home.php"); // Redirect to login or home
                exit();
            } else {
                error_log("Registration Insert Execute Failed: " . $stmt_insert->error);
                $_SESSION['message'] = "Error during registration: " . htmlspecialchars($stmt_insert->error);
            }
            $stmt_insert->close();
        }
        header("Location: reg.php"); // Redirect on error
        exit();
    }
}

$csrf_token = generateCsrfToken(); // Generate CSRF token for the form
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsive Registration Form</title>
  <link rel="stylesheet" href="reg.css">
</head>
<body>
  <div class="container">
    <div class="title">Registration</div>
    <div class="content">
      <?php if (isset($_SESSION['message'])): ?>
          <div class="alert alert-info">
              <?php echo htmlspecialchars($_SESSION['message']); ?>
          </div>
          <?php unset($_SESSION['message']); ?>
      <?php endif; ?>
      <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Full Name</span>
            <input type="text" placeholder="Enter your name" name="fullname" required>
          </div>
          <div class="input-box">
            <span class="details">Username</span>
            <input type="text" placeholder="Enter your username" name="username" required>
          </div>
          <div class="input-box">
            <span class="details">Email</span>
            <input type="email" placeholder="Enter your email" name="email" required>
          </div>
          <div class="input-box">
            <span class="details">Phone Number</span>
            <input type="text" placeholder="Enter your number" name="num" required>
          </div>
          <div class="input-box">
            <span class="details">Password</span>
            <input type="password" placeholder="Enter your password" name="password" required>
          </div>
          <div class="input-box">
            <span class="details">Confirm Password</span>
            <input type="password" placeholder="Confirm your password" name="conpass" required>
          </div>
        </div>
        <div class="gender-details">
          <input type="radio" name="gender" id="dot-1" value="Male" required>
          <input type="radio" name="gender" id="dot-2" value="Female">
          <input type="radio" name="gender" id="dot-3" value="Prefer not to say">
          <span class="gender-title">Gender</span>
          <div class="category">
            <label for="dot-1">
              <span class="dot one"></span>
              <span class="gender">Male</span>
            </label>
            <label for="dot-2">
              <span class="dot two"></span>
              <span class="gender">Female</span>
            </label>
            <label for="dot-3">
              <span class="dot three"></span>
              <span class="gender">Prefer not to say</span>
            </label>
          </div>
        </div>
        <div class="button">
          <input type="submit" value="Register">
          <p style="text-align: center;">Already have an account? <a href="../home.php" style="color: #3498db;">Login Here</a></p>
        </div>
      </form>
    </div>
  </div>
</body>
</html>