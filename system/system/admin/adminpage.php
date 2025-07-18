<?php
session_start();
require_once "../db_connect.php"; // Use require_once

// Check if admin is logged in (basic check, could be more robust)
if (!isset($_SESSION['AdminLoginID'])) {
    header("location: admin.php");
    exit();
}

// Function to generate a CSRF token (if needed for other forms on this page)
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
    // DO NOT unset $_SESSION['csrf_token'] here, as multiple forms on the same page
    // cause a full page reload and would invalidate subsequent submissions.
    return true;
}

// Initialize session variables for notification
$_SESSION['notification_message'] = $_SESSION['notification_message'] ?? null;
$_SESSION['notification_type'] = $_SESSION['notification_type'] ?? null;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check CSRF token for all POST requests
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['notification_message'] = "Invalid request (CSRF token missing or invalid).";
        $_SESSION['notification_type'] = "danger"; // Set type for styling
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0; // Cast to int for safety

        if ($id > 0) { // Ensure ID is valid
            if (isset($_POST['status'])) {
                // Update booking status
                $new_status = $_POST['status'];

                // Use prepared statements for status update
                $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
                if ($stmt === false) {
                    error_log("Status Update Prepare Failed: " . $conn->error);
                    $_SESSION['notification_message'] = "Error preparing status update.";
                    $_SESSION['notification_type'] = "danger";
                } else {
                    $stmt->bind_param("si", $new_status, $id);
                    if ($stmt->execute()) {
                        $_SESSION['notification_message'] = "Status updated successfully.";
                        $_SESSION['notification_type'] = "success";
                    } else {
                        error_log("Status Update Execute Failed for ID $id: " . $stmt->error);
                        $_SESSION['notification_message'] = "Error updating status: " . htmlspecialchars($stmt->error);
                        $_SESSION['notification_type'] = "danger";
                    }
                    $stmt->close();
                }
            } elseif (isset($_POST['weight'])) {
                // Update weight
                $new_weight = (float)$_POST['weight']; // Cast to float

                // Use prepared statements for weight update
                $stmt = $conn->prepare("UPDATE booking SET weight = ? WHERE id = ?");
                if ($stmt === false) {
                    error_log("Weight Update Prepare Failed: " . $conn->error);
                    $_SESSION['notification_message'] = "Error preparing weight update.";
                    $_SESSION['notification_type'] = "danger";
                } else {
                    $stmt->bind_param("di", $new_weight, $id); // 'd' for double/float
                    if ($stmt->execute()) {
                        $_SESSION['notification_message'] = "Weight updated successfully.";
                        $_SESSION['notification_type'] = "success";
                    } else {
                        error_log("Weight Update Execute Failed for ID $id: " . $stmt->error);
                        $_SESSION['notification_message'] = "Error updating weight: " . htmlspecialchars($stmt->error);
                        $_SESSION['notification_type'] = "danger";
                    }
                    $stmt->close();
                }
            }
        } else {
            $_SESSION['notification_message'] = "Invalid request ID.";
            $_SESSION['notification_type'] = "danger";
        }
    }
    // Redirect to self to show notification message (via JavaScript) and refresh page
    header("Location: adminpage.php");
    exit();
}

// Generate new CSRF token for the page
$csrf_token = generateCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - Laundry Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- user-dashboards.css is no longer directly used for layout, as we're managing it directly -->
    <!-- <link rel="stylesheet" href="../../user-dashboards.css"> -->
    <style>
        /* Ensure html and body take full height and prevent global scroll */
        html, body {
            height: 100%;
            overflow: hidden;
            margin: 0; /* Ensure no default body margin */
        }
        body {
            background-color: #f1f1f1; /* Consistent background */
        }

        /* Full-width layout for admin panel without sidebar */
        .admin-layout-wrapper {
            position: relative; /* Changed from absolute */
            left: 0;
            width: 100vw; /* Takes full viewport width */
            height: 100vh; /* Takes full viewport height */
            background: #f1f1f1;
            display: flex; /* Use flexbox for header and content area */
            flex-direction: column; /* Stack header and content vertically */
        }

        /* Adjust the fixed header from header.php to fit the new layout */
        .navbar.fixed-top {
            position: sticky; /* Sticky or relative to stay in flow, no longer fixed top-left */
            top: 0;
            left: 0;
            width: 100%; /* Takes full width of its parent (.admin-layout-wrapper) */
            background-color: #7a8886 !important; /* Match dashboard header background */
            height: 10vh; /* Fixed height for header */
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-left: 20px;
            padding-right: 20px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            z-index: 1; /* Ensure it's above content */
        }

        .navbar.fixed-top h2 {
            margin-left: 0 !important;
            color: white !important;
            font-size: 24px;
        }

        /* The main scrollable content area below the header */
        .admin-content-area {
            flex-grow: 1; /* Allows it to take all remaining vertical space */
            overflow-y: auto; /* This is the scrollable area for the table if content overflows */
            padding: 20px 15px;
            box-sizing: border-box;
            background-color: #f1f1f1; /* Ensure background color consistency */
        }

        /* Table styles within the scrollable area */
        .admin-content-area table {
            width: 100% !important;
            margin: 0 auto !important;
            background-color: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-height: none !important; /* Ensure table itself doesn't scroll */
            overflow-y: visible !important; /* Ensure table itself doesn't scroll */
        }

        /* Specific styles for table cells/forms */
        .admin-content-area table form {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .admin-content-area table input[type="number"] {
            width: 60px;
        }
        .admin-content-area table .form-select {
            width: auto;
        }
        .admin-content-area table .btn {
            margin-top: 0;
        }
        .admin-content-area table td {
            vertical-align: middle;
        }
        .admin-content-area table td[style*="background: green"] { /* Target status cell */
            background: green;
            color:white;
            font-weight:600;
            padding:.15em .3em;
            font-size:75%;
            display:inline-block; /* Required for padding and border-radius to work on inline content */
            margin-top:10px;
            border-radius:7px;
            white-space: nowrap;
        }

        /* NOTIFICATION STYLES */
        .notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999; /* Ensure it's on top of everything */
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none; /* Allow clicks to pass through empty space */
        }

        .notification {
            background-color: #f8d7da; /* Default danger */
            color: #721c24; /* Default danger */
            border: 1px solid #f5c6cb; /* Default danger */
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 300px; /* Fixed width for notification */
            pointer-events: auto; /* Re-enable pointer events for the notification itself */
            animation: slideIn 0.5s forwards; /* Animation for appearance */
            opacity: 0; /* Start hidden for animation */
        }

        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .notification.danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .notification-close-btn {
            background: none;
            border: none;
            font-size: 1.2em;
            cursor: pointer;
            color: inherit; /* Inherit color from parent notification */
            margin-left: 10px;
            font-weight: bold;
            line-height: 1; /* Adjust vertical alignment of 'X' */
            padding: 0; /* Remove default button padding */
        }
        .notification-close-btn:hover {
            opacity: 0.7;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Media queries for responsiveness (simplified without sidebar) */
        @media screen and (max-width: 940px) {
            /* No sidebar to collapse, so these rules simplify */
            .admin-layout-wrapper {
                width: 100vw;
            }
            .navbar.fixed-top {
                width: 100vw;
            }
        }

        @media screen and (max-width: 536px) {
            .admin-layout-wrapper {
                position: relative; /* Changed from static to relative to maintain flow better with new structure */
                width: 100vw;
                height: auto; /* Allow height to adapt */
            }
            .navbar.fixed-top {
                position: relative; /* Changed from static to relative */
                width: 100vw;
                height: auto;
                flex-direction: column;
                align-items: flex-start;
                padding-left: 10px !important;
            }
            .admin-content-area {
                margin-top: 0 !important;
                height: auto !important;
                overflow-y: visible !important; /* Allow scroll naturally on very small screens */
                padding: 10px !important;
            }
            .admin-content-area table {
                font-size: 0.8em;
            }
            .admin-content-area table th,
            .admin-content-area table td {
                padding: 6px;
            }
            .admin-content-area table td[style*="background: green"] {
                margin-top: 5px;
                font-size: 65%;
            }
            .admin-content-area table form {
                flex-direction: column;
                align-items: flex-start;
            }
            .admin-content-area table form input[type="number"] {
                width: 80px;
            }
            .admin-content-area table select {
                width: 100% !important;
                margin-right: 0 !important;
                margin-bottom: 5px;
            }
            .admin-content-area table button {
                width: 100%;
            }
            .notification-container {
                bottom: 10px;
                right: 10px;
                width: calc(100% - 20px); /* Full width minus padding */
                align-items: flex-end; /* Align notifications to the right */
            }
            .notification {
                width: 100%; /* Take full width of container */
            }
        }
    </style>
</head>
<body>

<!-- The sidebar include is REMOVED from here -->
<!-- <?php include "../admin/sidebar.php"; ?> -->

<!-- Main container for admin panel (no longer offset by sidebar) -->
<div class="admin-layout-wrapper">
    <!-- Header content (using the refactored header.php which now only contains the <nav> tag) -->
    <?php include "../admin/header.php"; ?>

    <!-- Main content area where table and messages will reside -->
    <div class="admin-content-area">
        <!-- The message will now be displayed by JavaScript as a notification -->
        <table class="table table-bordered">
            <thead style="background-color: #666666; color: white;">
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Queue</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Garment Type</th>
                    <th scope="col">Package</th>
                    <th scope="col">Detergent Powder</th>
                    <th scope="col">Detergent Downy</th>
                    <th scope="col">Weight (kg)</th>
                    <th scope="col">Payment Method</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all bookings
                $sql = "SELECT * FROM booking ORDER BY id DESC";
                $result = $conn->query($sql);

                // Check if there are any results
                if ($result && $result->num_rows > 0) {
                    // Loop through and display each booking
                    while ($row = $result->fetch_assoc()) { // Use fetch_assoc() for object-oriented style
                        // Sanitize all output to prevent XSS
                        $id = htmlspecialchars($row['id']);
                        $firstname = htmlspecialchars($row['firstname']);
                        $lastname = htmlspecialchars($row['lastname']);
                        $date = htmlspecialchars($row['date']);
                        $garment_type = htmlspecialchars($row['garment_type']);
                        $package = htmlspecialchars($row['package']);
                        $detergent_powder = $row['detergent_powder'] ? 'Yes' : 'No'; // No need to escape 'Yes'/'No'
                        $detergent_downy = $row['detergent_downy'] ? 'Yes' : 'No';
                        $weight = htmlspecialchars(isset($row['weight']) ? $row['weight'] : 'N/A'); // Handle undefined array key
                        $payment_mode = htmlspecialchars($row['payment_mode']);
                        $status = htmlspecialchars($row['status']);

                        // Output each row of booking data
                        echo "<tr>
                                <th scope='row'>$date</th>
                                <td>$id</td>
                                <td>$firstname $lastname</td>
                                <td>$garment_type</td>
                                <td>$package</td>
                                <td>$detergent_powder</td>
                                <td>$detergent_downy</td>
                                <td>
                                    <form method='POST' action=''>
                                        <input type='hidden' name='id' value='$id'>
                                        <input type='hidden' name='csrf_token' value='" . htmlspecialchars($csrf_token) . "'>
                                        <input type='number' step='0.1' name='weight' value='$weight' required>
                                        <button type='submit' class='btn btn-primary btn-sm'>Update</button>
                                    </form>
                                </td>
                                <td>$payment_mode</td>
                                <td style='background: green; color:white; font-weight:600; padding:.15em .3em; font-size:75%; display:inline-block; margin-top:10px; border-radius:7px;'>$status</td>
                                <td>
                                    <form method='POST' action=''>
                                        <input type='hidden' name='id' value='$id'>
                                        <input type='hidden' name='csrf_token' value='" . htmlspecialchars($csrf_token) . "'>
                                        <select name='status' class='form-select'>
                                            <option value='Pending' " . ($status == 'Pending' ? 'selected' : '') . ">Pending</option>
                                            <option value='Processing' " . ($status == 'Processing' ? 'selected' : '') . ">Processing</option>
                                            <option value='Ready to Pickup' " . ($status == 'Ready to Pickup' ? 'selected' : '') . ">Ready to Pickup</option>
                                            <option value='Completed' " . ($status == 'Completed' ? 'selected' : '') . ">Service Completed</option>
                                        </select>
                                        <button type='submit' class='btn btn-warning'>Update</button>
                                    </form>
                                </td>
                            </tr>";
                    }
                } else {
                    // If no bookings found
                    echo "<tr><td colspan='11' style='text-align:center;'>No data found</td></tr>";
                }

                // Connection is closed implicitly at end of script or explicitly if needed
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Notification container HTML -->
<div id="notificationContainer" class="notification-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationContainer = document.getElementById('notificationContainer');

    // PHP variables to pass messages and type to JavaScript
    const notificationMessage = <?php echo json_encode($_SESSION['notification_message'] ?? null); ?>;
    const notificationType = <?php echo json_encode($_SESSION['notification_type'] ?? null); ?>;

    // Clear session messages immediately after retrieving them
    <?php
    unset($_SESSION['notification_message']);
    unset($_SESSION['notification_type']);
    ?>

    // Display notification if a message exists
    if (notificationMessage) {
        showNotification(notificationMessage, notificationType);
    }

    /**
     * Displays a dynamic notification.
     * @param {string} message The message to display.
     * @param {string} type The type of notification ('success', 'danger', 'info', etc.).
     * @param {number} duration The duration in milliseconds before auto-dismissal (default 5000ms = 5 seconds).
     */
    function showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`; // Add type class for styling
        notification.innerHTML = `
            <span>${message}</span>
            <button class="notification-close-btn">×</button>
        `;

        notificationContainer.appendChild(notification);

        // Trigger slide-in animation
        notification.style.opacity = 0; // Set initial opacity for animation
        requestAnimationFrame(() => { // Ensure styles are applied before triggering animation
            notification.style.animation = 'slideIn 0.5s forwards';
        });

        // Add event listener for the close button
        const closeBtn = notification.querySelector('.notification-close-btn');
        closeBtn.addEventListener('click', function() {
            dismissNotification(notification);
        });

        // Auto-dismiss after specified duration
        setTimeout(() => {
            dismissNotification(notification);
        }, duration);
    }

    /**
     * Dismisses a notification with a slide-out animation.
     * @param {HTMLElement} notificationElement The notification element to dismiss.
     */
    function dismissNotification(notificationElement) {
        if (notificationElement && notificationElement.parentNode) { // Check if it's still in the DOM
            notificationElement.style.animation = 'slideOut 0.5s forwards';
            // Remove the element from the DOM after the animation completes
            notificationElement.addEventListener('animationend', () => {
                notificationElement.remove();
            }, { once: true }); // Ensure this listener only runs once
        }
    }
});
</script>
</body>
</html>