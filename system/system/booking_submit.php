<?php
session_start();
require_once('db_connect.php'); // Ensure path is correct from this file's perspective (it's in the same directory)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Please log in to make a booking.";
    header("Location: home.php"); // Redirect to login page relative to this file
    exit();
}

// Function to validate a CSRF token
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    // Optionally regenerate or unset after successful validation
    unset($_SESSION['csrf_token']); // Consume token
    return true;
}

if (isset($_POST['save-booked'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = "Invalid request (CSRF token missing or invalid).";
        $_SESSION['message_type'] = 'danger'; // Add message type for styling
        header("Location: ../dashboard.php"); // Redirect back to dashboard if CSRF fails
        exit();
    }

    $user_id = $_SESSION['user_id']; // Get user ID from session
    // Get first and last names directly from session for security
    $firstname = isset($_SESSION['user_firstname']) ? $_SESSION['user_firstname'] : ''; // NEW: Get from session
    $lastname = isset($_SESSION['user_lastname']) ? $_SESSION['user_lastname'] : '';   // NEW: Get from session

    $address = htmlspecialchars(trim($_POST['address']));
    $date = htmlspecialchars(trim($_POST['date']));
    $garment_type = htmlspecialchars(trim($_POST['garment_type']));
    $package = htmlspecialchars(trim($_POST['package']));
    $detergent_powder = isset($_POST['detergent_powder']) ? 1 : 0;
    $detergent_downy = isset($_POST['detergent_downy']) ? 1 : 0;
    $payment_mode = "COD Only"; // Still hardcoded as per original design
    $status = "Pending"; // Initial status

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO booking (user_id, firstname, lastname, address, date, garment_type, package, detergent_powder, detergent_downy, payment_mode, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        error_log("Booking Submit Prepare Failed: " . $conn->error);
        $_SESSION['message'] = "An internal error occurred. Please try again.";
        $_SESSION['message_type'] = 'danger';
    } else {
        $stmt->bind_param("issssssiiss", $user_id, $firstname, $lastname, $address, $date, $garment_type, $package, $detergent_powder, $detergent_downy, $payment_mode, $status);

        if ($stmt->execute()) {
            // Set session variables for the modal notification
            $_SESSION['booking_modal_message'] = "Your booking has been successfully submitted!";
            $_SESSION['booking_modal_type'] = 'success';
        } else {
            error_log("Booking Submit Execute Failed: " . $stmt->error);
            $_SESSION['message'] = "Error: " . htmlspecialchars($stmt->error);
            $_SESSION['message_type'] = 'danger';
        }
        $stmt->close();
    }

    // Always redirect back to the dashboard after processing the booking
    header("Location: ../dashboard.php");
    exit();
} else {
    // If accessed directly without POST data, redirect to dashboard
    header("Location: ../dashboard.php");
    exit();
}
?>