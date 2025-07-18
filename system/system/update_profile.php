<?php
// IMPORTANT: These lines are for debugging ONLY. Remove them in a production environment.
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', "C:\xampp\apache\logs\error.log");

session_start(); // This MUST be the very first thing in the file.

// This path should be correct if db_connect.php is in the SAME directory (system/system/)
require_once(__DIR__ . '/db_connect.php'); 

// Set content type for JSON response
header('Content-Type: application/json');

// Function to generate a CSRF token
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to validate a CSRF token
function validateCsrfToken($token) {
    // --- DEBUGGING START ---
    error_log("CSRF Debug - Session ID: " . session_id()); // Log session ID
    error_log("CSRF Debug - Session Token: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));
    error_log("CSRF Debug - POST Token: " . ($token ?? 'NOT SENT'));
    error_log("CSRF Debug - Hash Equals Result: " . (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token) ? 'TRUE' : 'FALSE'));
    // --- DEBUGGING END ---

    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true; // Keep true for now, don't unset here for AJAX.
}

// Initialize response array
$response = ['success' => false, 'message' => 'An unknown error occurred.', 'new_csrf_token' => generateCsrfToken()];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $response['message'] = "Invalid request (CSRF token missing or invalid). Please refresh and try again.";
        echo json_encode($response);
        exit();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = "Unauthorized access. Please log in.";
        echo json_encode($response);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Collect and sanitize input
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $mobilenumber = htmlspecialchars(trim($_POST['mobilenumber'])); 

    // Server-side validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($mobilenumber)) {
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    // Check if the new email or mobile number already exists for *another* user
    // Email check
    $stmt_check_email = $conn->prepare("SELECT id FROM userregistration WHERE email = ? AND id != ?");
    if ($stmt_check_email === false) {
        error_log("Update Profile (Email Check) Prepare Failed: " . $conn->error);
        $response['message'] = "Database error during email check (prepare).";
        echo json_encode($response);
        exit();
    }
    $stmt_check_email->bind_param("si", $email, $user_id);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
    if ($stmt_check_email->num_rows > 0) {
        $response['message'] = "This email is already registered by another user.";
        echo json_encode($response);
        exit();
    }
    $stmt_check_email->close();

    // Mobile number check (assuming 'num' is the column name for mobile number in userregistration)
    $stmt_check_num = $conn->prepare("SELECT id FROM userregistration WHERE num = ? AND id != ?");
    if ($stmt_check_num === false) {
        error_log("Update Profile (Mobile Check) Prepare Failed: " . $conn->error);
        $response['message'] = "Database error during mobile number check (prepare).";
        echo json_encode($response);
        exit();
    }
    $stmt_check_num->bind_param("si", $mobilenumber, $user_id);
    $stmt_check_num->execute();
    $stmt_check_num->store_result();
    if ($stmt_check_num->num_rows > 0) {
        $response['message'] = "This mobile number is already registered by another user.";
        echo json_encode($response);
        exit();
    }
    $stmt_check_num->close();


    // Update user information
    $stmt_update = $conn->prepare("UPDATE userregistration SET firstname = ?, lastname = ?, email = ?, num = ? WHERE id = ?");
    if ($stmt_update === false) {
        error_log("Update Profile Prepare Failed: " . $conn->error);
        $response['message'] = "Error preparing profile update statement.";
        echo json_encode($response);
        exit();
    }

    $stmt_update->bind_param("ssssi", $firstname, $lastname, $email, $mobilenumber, $user_id);

    if ($stmt_update->execute()) {
        $response['success'] = true;
        $response['message'] = "Profile updated successfully!";
        // Update session variables immediately
        $_SESSION['user_firstname'] = $firstname;
        $_SESSION['user_lastname'] = $lastname;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_mobilenumber'] = $mobilenumber;
        $response['updated_data'] = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'mobilenumber' => $mobilenumber
        ];
    } else {
        error_log("Update Profile Execute Failed for User ID $user_id: " . $stmt_update->error);
        $response['message'] = "Error updating profile: " . htmlspecialchars($stmt_update->error);
    }
    $stmt_update->close();
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
$conn->close();
?>