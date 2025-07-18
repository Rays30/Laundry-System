<?php
session_start(); // Start session for messages and CSRF
require_once "db_connect.php"; // CORRECT PATH: db_connect.php is in the same folder (system/system/)

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

if (isset($_POST["submit"])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid request (CSRF token missing or invalid).";
        $_SESSION['message_type'] = 'danger'; // Set message type for styling
        header("Location: register.php");
        exit();
    }

    // Changed to capture first and last names
    $firstname = htmlspecialchars(trim($_POST["firstname"])); // NEW
    $lastname = htmlspecialchars(trim($_POST["lastname"]));   // NEW
    $email = htmlspecialchars(trim($_POST["email"]));
    $mobilenumber = htmlspecialchars(trim($_POST["mobilenumber"]));
    $password = $_POST["password"];
    $errors = array();

    // Check if any field is empty
    if (empty($firstname) || empty($lastname) || empty($email) || empty($mobilenumber) || empty($password)) { // Updated check
      array_push($errors, "All fields are required to fill out");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      array_push($errors, "Email is not valid");
    }

    // Check password length
    if (strlen($password) < 8 || strlen($password) > 15) {
      array_push($errors, "Password must be between 8 and 15 characters long");
    }

    // Check if email already exists in the database
    $sql = "SELECT email FROM userregistration WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            array_push($errors, "Email already exists!");
        }
        $stmt->close();
    } else {
        error_log("Register Check Prepare Failed: " . $conn->error);
        array_push($errors, "An internal error occurred.");
    }

    // Display errors if any or proceed with registration
    if (count($errors) > 0) {
      $_SESSION['message_type'] = 'danger';
      $_SESSION['message'] = implode("<br>", $errors);
    } else {
      // Hash the password for security
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      // --- CRITICAL FIX HERE: Changed 'mobilenumber' to 'num' in the SQL query ---
      // The variable $mobilenumber will correctly be inserted into the 'num' column
      // Updated SQL to include firstname and lastname, removed fullname
      $sql = "INSERT INTO userregistration (firstname, lastname, email, num, password) VALUES (?, ?, ?, ?, ?)"; // UPDATED SQL
      $stmt = $conn->prepare($sql);

      if ($stmt) {
        // Bind parameters to the SQL query
        // '$mobilenumber' variable is bound to the 'num' column
        $stmt->bind_param("sssss", $firstname, $lastname, $email, $mobilenumber, $hashedPassword); // UPDATED bind_param

        if ($stmt->execute()) {
          $_SESSION['message_type'] = 'success';
          $_SESSION['message'] = "You are Registered! You can now log in.";
          header("Location: login.php"); // Path relative to this file
          exit();
        } else {
          error_log("Register Insert Execute Failed: " . $stmt->error);
          $_SESSION['message_type'] = 'danger';
          $_SESSION['message'] = "Something went wrong during registration: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
      } else {
        error_log("Register Insert Prepare Failed: " . $conn->error);
        $_SESSION['message_type'] = 'danger';
        $_SESSION['message'] = "An internal error occurred. Please try again.";
      }
    }
    header("Location: register.php"); // Redirect to self to show messages
    exit();
}

$csrf_token = generateCsrfToken(); // Generate CSRF token for the form
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration or Sign Up form</title>
    <link rel="stylesheet" href="css/registerr.css"> <!-- Path relative to this file -->
    <style>
        .alert {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }
        .alert.danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
    </style>
  </head>
<body>
  <div class="wrapper">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert <?php echo htmlspecialchars($_SESSION['message_type']); ?>">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <header style="font-size: 1.2rem; font-weight: 500; margin-bottom: 1rem; text-align:center; border: solid black 1px;">
      Laundry System | User Registration
    </header>
    <form action="" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

      <div class="input-box">
        <input type="text" placeholder="Enter your First Name" name="firstname" required> <!-- CHANGED -->
      </div>
      <div class="input-box">
        <input type="text" placeholder="Enter your Last Name" name="lastname" required> <!-- NEW FIELD -->
      </div>
      <div class="input-box">
        <input type="email" id="email" placeholder="Jhon@gmail.com" name="email" required>
      </div>
      <div class="input-box">
        <!-- Input field name is 'mobilenumber', which is processed by PHP -->
        <input type="text" id="phone" placeholder="(63-9693397812)" name="mobilenumber" required>
      </div>
      <div class="input-box">
        <input type="password" placeholder="Password" name="password" required>
      </div>
      <div class="policy">
        <input type="checkbox" required> <!-- Make policy checkbox required -->
        <h3>I accept all terms & conditions</h3>
      </div>
      <div class="input-box button">
        <input type="Submit" name="submit" value="Register Now">
      </div>
      <div class="text">
        <hr style="margin-top:15px;">
        <h3>Already have an account? <a href="login.php">Login now</a></h3> <!-- Path relative to this file -->
      </div>
    </form>
  </div>
</body>
</html>