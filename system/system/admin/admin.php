<?php
session_start();
require_once("../db_connect.php"); // Use require_once for critical files

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
    // Optionally regenerate or unset after successful validation to make it single-use
    unset($_SESSION['csrf_token']);
    return true;
}

// Handle login attempt
if (isset($_POST['Signin'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid request (CSRF token missing or invalid).";
        header("Location: admin.php");
        exit();
    }

    $username = $_POST['Username']; // No need for mysqli_real_escape_string here if using prepared statements
    $password = $_POST['Password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT `Password` FROM `admin_login` WHERE `Username` = ?");
    if ($stmt === false) {
        error_log("Admin Login Prepare Failed: " . $conn->error);
        $_SESSION['message'] = "An internal error occurred. Please try again.";
        header("Location: admin.php");
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password_from_db = $row['Password'];

        // Verify password using password_verify
        if (password_verify($password, $hashed_password_from_db)) {
            // Login successful
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['AdminLoginID'] = $username;
            header("location: adminpage.php"); // Redirect to admin page
            exit();
        } else {
            // Password does not match
            $_SESSION['message'] = "Incorrect Username or Password.";
        }
    } else {
        // Username not found
        $_SESSION['message'] = "Incorrect Username or Password.";
    }
    $stmt->close();
    header("Location: admin.php"); // Redirect to self to display message
    exit();
}

// Generate CSRF token for the form
$csrf_token = generateCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- For icons -->
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .login-panel {
            width: 410px;
            box-shadow: 0px 20px 50px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            overflow: hidden;
        }

        .login-header {
            background-color: #065465;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 1.2em;
        }

        .login-body {
            padding: 20px;
            background-color: #fff;
        }

        .input-group {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 3px;
            background-color: #f7f7f7;
        }

        .input-group i {
            margin-right: 10px;
            color: #888;
        }

        .input-group input {
            border: none;
            outline: none;
            width: 100%;
            background: none;
            font-size: 1em;
            color: #333;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            background-color: #4ca3fc;
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 1em;
            cursor: pointer;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #3eb3ab;
        }

        .forgot-password {
            margin-top: 10px;
            font-size: 0.9em;
            color: #555;
        }

        .forgot-password a {
            color: #4ecdc4;
            text-decoration: none;
        }

        .forgot-password a:hover {
                text-decoration: underline;
        }
    </style>
</head>
<body>
    <form method="POST">
        <div class="login-panel">
            <div class="login-header">
                ADMIN LOGIN PANEL
            </div>
            <div class="login-body">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-danger" role="alert" style="padding: 10px; ">
                        <?php echo htmlspecialchars($_SESSION['message']); ?>
                    </div>
                    <?php unset($_SESSION['message']); // Clear message after displaying ?>
                <?php endif; ?>

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Username" name="Username" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" name="Password" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button class="login-btn" name="Signin">Sign In</button>
                <div class="forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>