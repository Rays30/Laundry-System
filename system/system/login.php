<?php
session_start(); // Ensure session is started at the very beginning
require_once "db_connect.php"; // Use require_once

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
    unset($_SESSION['csrf_token']); // Consume token after validation
    return true;
}

if (isset($_POST["login"])) {
    // Validate CSRF token first
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['login_message'] = "Invalid request (CSRF token missing or invalid).";
        header("Location: login.php");
        exit();
    }

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Use prepared statements to prevent SQL injection
    // Fetch firstname, lastname, and password
    $stmt = $conn->prepare("SELECT `id`, `firstname`, `lastname`, `password`, `num` FROM userregistration WHERE email = ?");
    if ($stmt === false) {
        error_log("Login Prepare Failed: " . $conn->error);
        $_SESSION['login_message'] = "An internal error occurred. Please try again.";
        header("Location: login.php");
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user_data = $result->fetch_assoc(); // Renamed to user_data for clarity
        $hashed_password_from_db = $user_data["password"];

        // Verify password using password_verify
        if (password_verify($password, $hashed_password_from_db)) {
            // Login successful
            session_regenerate_id(true); // Prevent session fixation
            
            // Store user ID, email, firstname, and lastname in session
            $_SESSION['user_id'] = $user_data['id']; // Store user ID
            $_SESSION['user_email'] = $email;
            $_SESSION['user_firstname'] = $user_data['firstname']; // NEW
            $_SESSION['user_lastname'] = $user_data['lastname'];   // NEW
            $_SESSION['user_mobilenumber'] = $user_data['num']; // Store mobile number

            header("Location: /LaundryManagement%20System/system/dashboard.php");
            exit(); // Stop further script execution after redirect
        } else {
            // Password doesn't match the stored hash
            $_SESSION['login_message'] = "Incorrect Email or Password.";
        }
    } else {
        // If no user found with the entered email (treat as incorrect password for security)
        $_SESSION['login_message'] = "Incorrect Email or Password.";
    }
    $stmt->close();
    header("Location: login.php"); // Redirect to self to display message
    exit();
}

// Generate CSRF token for the form
$csrf_token = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Login & Registration Form</title>
  <!---Custom CSS File--->
  <link rel="stylesheet" href="../system/css/login.css">
  <style>
    .alert-danger-custom {
      padding: 15px;
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      border-radius: 5px;
      font-family: Arial, sans-serif;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <input type="checkbox" id="check">
    <div class="login form">
      <header>Laundry System | User Login</header>
      <?php if (isset($_SESSION['login_message'])): ?>
          <div class="alert-danger-custom">
              <?php echo htmlspecialchars($_SESSION['login_message']); ?>
          </div>
          <?php unset($_SESSION['login_message']); ?>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="email" id="email" name="email" placeholder="Jhon@gmail.com" required>
        <input type="password" name="password" placeholder="Enter your password" required>
        <a href="#">Forgot password?</a>
        <input type="submit" class="button" name="login" value="Login">
      </form>

      <div class="signup">
        <span class="signup">Don't have an account?
         <label>
          <a href="register.php">Sign up</a>
         </label>
        </span>
      </div>
      <hr style="margin-top:20px;">
      <a href="home.php" style="display: block; text-align: center; margin-top: 20px;">Back to Home</a>
    </div>
  </div>
</body>
</html>