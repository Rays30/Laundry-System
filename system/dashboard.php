<?php
session_start();
require_once 'system/db_connect.php'; // Ensure path is correct

// Function to generate a CSRF token for this page's forms/AJAX
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Generate the token when the dashboard page loads
$csrf_token = generateCsrfToken();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: system/home.php"); // Redirect to login/home if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Initialize counts for dashboard overview
$new_request_count = 0;
$accepted_count = 0;
$inprocess_count = 0; // This will now represent 'Ready'
$finish_count = 0;

// Query for New Request (Pending)
$stmt_new = $conn->prepare("SELECT COUNT(*) AS count FROM booking WHERE user_id = ? AND status = 'Pending'");
if ($stmt_new) {
    $stmt_new->bind_param("i", $user_id);
    $stmt_new->execute();
    $result_new = $stmt_new->get_result();
    $new_request_count = $result_new->fetch_assoc()['count'];
    $stmt_new->close();
} else {
    error_log("Dashboard New Request Query Failed: " . $conn->error);
}

// Query for Accepted (Processing)
$stmt_accept = $conn->prepare("SELECT COUNT(*) AS count FROM booking WHERE user_id = ? AND status = 'Processing'");
if ($stmt_accept) {
    $stmt_accept->bind_param("i", $user_id);
    $stmt_accept->execute();
    $result_accept = $stmt_accept->get_result();
    $accepted_count = $result_accept->fetch_assoc()['count'];
    $stmt_accept->close();
} else {
    error_log("Dashboard Accepted Query Failed: " . $conn->error);
}

// Query for Ready (formerly Inprocess) - CHANGED STATUS FROM 'Inprocess' TO 'Ready to Pickup'
$stmt_inprocess = $conn->prepare("SELECT COUNT(*) AS count FROM booking WHERE user_id = ? AND status = 'Ready to Pickup'");
if ($stmt_inprocess) {
    $stmt_inprocess->bind_param("i", $user_id);
    $stmt_inprocess->execute();
    $result_inprocess = $stmt_inprocess->get_result();
    $inprocess_count = $result_inprocess->fetch_assoc()['count'];
    $stmt_inprocess->close();
} else {
    error_log("Dashboard Ready Query Failed: " . $conn->error); // Updated log message
}

// Query for Finish (Completed)
$stmt_finish = $conn->prepare("SELECT COUNT(*) AS count FROM booking WHERE user_id = ? AND status = 'Completed'");
if ($stmt_finish) {
    $stmt_finish->bind_param("i", $user_id);
    $stmt_finish->execute();
    $result_finish = $stmt_finish->get_result();
    $finish_count = $result_finish->fetch_assoc()['count'];
    $stmt_finish->close();
} else {
    error_log("Dashboard Finish Query Failed: " . $conn->error);
}

// Capture the default dashboard overview HTML using output buffering
// This allows us to inject it back with JavaScript without re-rendering via PHP
ob_start();
?>
<div class="cards" id="dashboard-overview-cards">
    <div class="card" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('system/img/towel.jpg');">
        <div class="box">
            <h1 style="color:white;"><?php echo htmlspecialchars($new_request_count); ?></h1>
            <h3 style="color:white;">New Request</h3>
        </div>
        <div class="icon-case">
            <img src="" alt=""> <!-- Placeholder image, consider specific icons -->
        </div>
    </div>
    <div class="card" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('system/img/towel.jpg');">
        <div class="box">
            <h1 style="color:white;"><?php echo htmlspecialchars($accepted_count); ?></h1>
            <h3 style="color:white;">Accepted</h3>
        </div>
        <div class="icon-case">
            <img src="" alt=""> <!-- Placeholder image -->
        </div>
    </div>
    <div class="card" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('system/img/towel.jpg');">
        <div class="box">
            <h1 style="color:white;"><?php echo htmlspecialchars($inprocess_count); ?></h1>
            <h3 style="color:white;">Ready</h3> <!-- CHANGED TEXT FROM 'Inprocess' TO 'Ready' -->
        </div>
        <div class="icon-case">
            <img src="" alt=""> <!-- Placeholder image -->
        </div>
    </div>
    <div class="card" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('system/img/towel.jpg');">
        <div class="box">
            <h1 style="color:white;"><?php echo htmlspecialchars($finish_count); ?></h1>
            <h3 style="color:white;">Finish</h3>
        </div>
        <div class="icon-case">
            <img src="" alt=""> <!-- Placeholder image -->
        </div>
    </div>
</div>
<?php
$dashboard_overview_html = ob_get_clean(); // Store the buffered content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="user-dashboards.css">
    <!-- Include Bootstrap CSS here, as dynamic pages use it and might not re-inject it -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>User Panel</title>
    <style>
        /* Style the dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            display: flex; /* Use flexbox for centering */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto; /* For browsers that don't support flex centering fully */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more responsive */
            max-width: 400px; /* Max width for larger screens */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
        }

        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px; /* Space between buttons */
            margin-top: 20px;
        }

        .modal-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .modal-buttons .btn-yes {
            background-color: #f05462;
            color: white;
        }

        .modal-buttons .btn-yes:hover {
            background-color: #d13d4b;
        }

        .modal-buttons .btn-cancel {
            background-color: #ccc;
            color: #333;
        }

        .modal-buttons .btn-cancel:hover {
            background-color: #bbb;
        }

        /* Styles for the dynamic content area */
        #main-content-area {
            padding: 20px 15px; /* Consistent padding with original .cards */
            /* Height will be handled by user-dashboards.css directly */
            overflow-y: auto; /* Allow content inside this area to scroll */
            overflow-x: hidden; /* ADDED: Prevent horizontal scrolling */
        }

        /* Adjustments for the fixed sidebar and main content area */
        /* These rules are now primarily in user-dashboards.css */
        .side-menu {
            position: fixed;
            width: 250px; /* Fixed pixel width for sidebar */
            background: #333333;
            z-index: 2; /* Ensure it's above other content */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .side-menu li {
            font-size: 20px; /* Slightly reduce font size to help fit */
            padding: 10px 20px; /* Reduced horizontal padding */
            color: gray;
            display: flex;
            white-space: nowrap; /* Prevent text from wrapping */
            align-items: center;
            justify-content: flex-start;
        }

        .side-menu li span:last-child {
            margin-left: auto; /* Push the arrow to the right side */
        }

        .container {
            position: absolute;
            left: 250px; /* Start content after fixed sidebar width */
            width: calc(100vw - 250px); /* Fill remaining width */
            height: 100vh;
            background: #f1f1f1;
        }
        .container .header {
            position: fixed;
            top: 0;
            left: 250px; /* Match container's left offset */
            width: calc(100vw - 250px); /* Match container's width */
            height: 10vh;
            background: #7a8886;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding-left: 20px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        /* Override specific styles from loaded pages to make them fit into the dashboard layout */
        /* Use IDs for robust selection */
        #main-content-area #booking-main-content,
        #main-content-area #request-status-main-content
        {
            background-color: transparent !important;
            box-shadow: none !important;
            width: 100% !important; /* Make them take full width of main-content-area */
            max-width: none !important; /* Remove max-width constraint */
            padding: 0 !important; /* Remove internal padding, adjust if needed for spacing */
            margin: 0 !important; /* Remove any external margins */
            height: auto !important; /* Adjust height dynamically */
            display: block !important;
            text-align: left !important; /* Override center alignment if any */
        }
        /* Further adjustments for tables within the loaded content */
        #main-content-area table {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: auto !important;
            border-radius: 0 !important; /* Remove rounded corners if not desired inside */
            box-shadow: none !important; /* Remove shadow if not desired inside */
        }
        /* Adjust alert message specific to booking.php if included */
        #main-content-area .alert {
            width: 100% !important;
            margin-left: 0 !important;
            text-align: left !important;
        }
        /* Style for active sidebar item */
        .side-menu li.active-sidebar-item {
            background: white; /* Active background */
        }
        .side-menu li.active-sidebar-item a span {
            color: #f05462; /* Active text color for spans */
        }

        /* NOTIFICATION STYLES (Copied from adminpage.php) */
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

        /* Media queries adjustments for responsiveness */
        @media screen and (max-width: 940px) {
            /* Adjust sidebar for smaller screens */
            .side-menu {
                width: 60px; /* Collapse sidebar to just icons */
                align-items: center;
            }
            .side-menu li {
                padding: 10px 0; /* Remove horizontal padding */
                justify-content: center;
            }
            .side-menu li span:not(:last-child) { /* Target text, not arrow */
                display: none; /* Hide text */
            }
            .side-menu li span:last-child {
                display: none; /* Hide arrows too */
            }
            .side-menu .brand-name h1 {
                display: none; /* Hide dashboard title */
            }
            .container, .container .header {
                left: 60px; /* Adjust main content to start after collapsed sidebar */
                width: calc(100vw - 60px);
            }
        }

        @media screen and (max-width: 536px) {
            /* On very small screens, make sidebar static and full width */
            .side-menu {
                position: static; /* No longer fixed */
                width: 100%; /* Full width */
                min-height: auto; /* Height adapts to content */
                flex-direction: row; /* Layout items horizontally */
                justify-content: space-around; /* Distribute items */
                padding: 10px 0;
            }
            .side-menu .brand-name {
                display: none; /* Hide brand name on very small screens */
            }
            .side-menu ul {
                display: flex;
                flex-wrap: wrap; /* Allow items to wrap */
                justify-content: center;
                width: 100%;
            }
            .side-menu li {
                font-size: 14px; /* Smaller font for mobile */
                padding: 5px 10px;
                white-space: normal; /* Allow text to wrap if necessary */
                flex-direction: column; /* Stack icon and text vertically */
            }
            .side-menu li span:not(:last-child) {
                display: block; /* Show text again */
                margin-left: 0;
                text-align: center;
            }
            .side-menu li span:last-child {
                display: none; /* Hide arrows still */
            }
            .side-menu ul li a img {
                width: 25px; /* Smaller icons */
                height: 25px;
                margin-right: 0;
                margin-bottom: 5px; /* Space between icon and text */
            }
            .container {
                position: static; /* No longer absolute */
                width: 100vw; /* Full width */
                min-height: auto;
                padding-top: 10px; /* Add some top padding as header is now above */
            }
            .container .header {
                position: static; /* No longer fixed */
                width: 100vw; /* Full width */
                padding-left: 10px;
                height: auto; /* Height adapts to content */
                flex-direction: column; /* Stack header items */
                align-items: flex-start;
            }
            .container .header h2 {
                font-size: 20px;
                margin-bottom: 10px;
            }
            .container .header .user-icons {
                width: 100%;
                justify-content: flex-end; /* Push user icon to right */
                margin-right: 10px;
            }
            .container .content .cards {
                flex-direction: column; /* Stack cards vertically */
            }
            .container .content .cards .card {
                width: 90%; /* Make cards wider on small screens */
                margin: 10px auto;
            }
            #main-content-area {
                padding: 10px; /* Adjust padding for loaded content */
                min-height: auto;
                /* For mobile, if the main-content-area is static, allow it to scroll naturally */
                overflow-y: visible; /* Or auto if you want internal scrolling */
            }
            /* Notification responsive adjustments */
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
<div class="side-menu">
    <div class="brand-name">
        <!-- Removed <h1>Dashboard</h1> -->
    </div>
    <ul>
        <li>
            <a href="javascript:void(0);" data-page="dashboard" class="sidebar-link">
                <img src="../system/system/img/icons/dashboard.png" alt=""> <span>Dashboard</span> <span>→</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0);" data-page="booking" class="sidebar-link">
                <img src="../system/system/img/icons/booking.png" alt=""><span>Laundry Request</span> <span>→</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0);" data-page="request_status" class="sidebar-link">
                <img src="../system/system/img/icons/pending.png" alt=""><span>Request Status</span> <span>→</span>
            </a>
        </li>
    </ul>
</div>

<div class="container">
    <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 id="page-title">Dashboard / <span>Overview</span></h2>
        <div class="user-icons" style="display: flex; gap: 10px;">
            <div class="dropdown">
                <img src="./system/img/icons/user.png" alt="User 1" class="user-icon" style="width: 40px; height: 30px; border-radius: 50%; margin-right:40px;">
                <div class="dropdown-content">
                    <a href="javascript:void(0);" id="profileTrigger">Profile</a>
                    <!-- Changed href to call a JavaScript function for logout confirmation -->
                    <a href="#" id="logoutTrigger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <!-- This is the area where dynamic content will be loaded -->
        <div id="main-content-area">
            <?php echo $dashboard_overview_html; // Display initial dashboard content ?>
        </div>
    </div>

    <!-- The Logout Confirmation Modal -->
    <div id="logoutModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Are you sure you want to log out?</h3>
            <div class="modal-buttons">
                <button class="btn-yes" id="confirmLogoutBtn">Yes</button>
                <button class="btn-cancel" id="cancelLogoutBtn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- REMOVED: Booking Confirmation Modal (replaced by new notification system) -->
    <!-- <div id="bookingConfirmationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3 id="bookingModalMessage"></h3>
            <div class="modal-buttons">
                <button class="btn-yes" id="okBookingModalBtn">OK</button>
            </div>
        </div>
    </div> -->

    <!-- Profile Edit Modal -->
    <div id="profileModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Edit Profile Information</h3>
            <div id="profile-message" style="margin-bottom: 15px; text-align: center;"></div>
            <form id="profileEditForm">
                <!-- Ensure this value is populated correctly from PHP's session variable on page load -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="profile_firstname">First Name</label>
                    <input type="text" class="form-control" id="profile_firstname" name="firstname" readonly required>
                </div>
                <div class="form-group">
                    <label for="profile_lastname">Last Name</label>
                    <input type="text" class="form-control" id="profile_lastname" name="lastname" readonly required>
                </div>
                <div class="form-group">
                    <label for="profile_email">Email</label>
                    <input type="email" class="form-control" id="profile_email" name="email" readonly required>
                </div>
                <div class="form-group">
                    <label for="profile_mobilenumber">Contact Number</label>
                    <input type="text" class="form-control" id="profile_mobilenumber" name="mobilenumber" readonly required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-primary" id="editProfileBtn">Edit Profile</button>
                    <button type="submit" class="btn-yes" id="saveProfileBtn" style="display: none;">Save Changes</button>
                    <button type="button" class="btn-cancel" id="cancelProfileEditBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification container HTML (NEW) -->
    <div id="notificationContainer" class="notification-container"></div>

    <script>
        // Get the modal elements (from previous task)
        var logoutModal = document.getElementById("logoutModal");
        var logoutTrigger = document.getElementById("logoutTrigger");
        var confirmLogoutBtn = document.getElementById("confirmLogoutBtn");
        var cancelLogoutBtn = document.getElementById("cancelLogoutBtn");

        // REMOVED: Booking modal elements
        // var bookingConfirmationModal = document.getElementById("bookingConfirmationModal");
        // var bookingModalMessage = document.getElementById("bookingModalMessage");
        // var okBookingModalBtn = document.getElementById("okBookingModalBtn");

        // NEW: Get profile modal elements
        var profileModal = document.getElementById("profileModal");
        var profileTrigger = document.getElementById("profileTrigger"); // Use the ID added above
        var editProfileBtn = document.getElementById("editProfileBtn");
        var saveProfileBtn = document.getElementById("saveProfileBtn");
        var cancelProfileEditBtn = document.getElementById("cancelProfileEditBtn");
        var profileMessage = document.getElementById("profile-message");

        // Profile form fields
        var profileFirstname = document.getElementById("profile_firstname");
        var profileLastname = document.getElementById("profile_lastname");
        var profileEmail = document.getElementById("profile_email");
        var profileMobileNumber = document.getElementById("profile_mobilenumber");
        var profileEditForm = document.getElementById("profileEditForm");

        // Notification container element (NEW)
        const notificationContainer = document.getElementById('notificationContainer');

        // Logout Modal functionality
        logoutTrigger.onclick = function(event) {
            event.preventDefault(); // Prevent default link behavior
            logoutModal.style.display = "flex"; // Use flex to center the content
        }
        confirmLogoutBtn.onclick = function() {
            // Path relative to the dashboard.php file
            window.location.href = "system/logout.php";
        }
        cancelLogoutBtn.onclick = function() {
            logoutModal.style.display = "none";
        }

        // REMOVED: Booking Confirmation Modal functionality
        // okBookingModalBtn.onclick = function() {
        //     bookingConfirmationModal.style.display = "none";
        // }

        // Function to populate profile modal and set initial state or update with new data
        // It now accepts an optional 'data' object.
        function populateProfileModal(data = null) {
            if (data) {
                // If data is provided (e.g., from AJAX response), use it
                profileFirstname.value = data.firstname;
                profileLastname.value = data.lastname;
                profileEmail.value = data.email;
                profileMobileNumber.value = data.mobilenumber;
            } else {
                // Otherwise, use the initial values rendered from PHP session (on page load)
                profileFirstname.value = "<?php echo htmlspecialchars($_SESSION['user_firstname'] ?? ''); ?>";
                profileLastname.value = "<?php echo htmlspecialchars($_SESSION['user_lastname'] ?? ''); ?>";
                profileEmail.value = "<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>";
                profileMobileNumber.value = "<?php echo htmlspecialchars($_SESSION['user_mobilenumber'] ?? ''); ?>";
            }

            // Set all fields to readonly initially
            profileFirstname.readOnly = true;
            profileLastname.readOnly = true;
            profileEmail.readOnly = true;
            profileMobileNumber.readOnly = true;

            // Show Edit button, hide Save button
            editProfileBtn.style.display = "inline-block";
            saveProfileBtn.style.display = "none";
            profileMessage.innerHTML = ""; // Clear previous messages
        }

        // Profile Modal functionality
        if (profileTrigger) {
            profileTrigger.onclick = function(event) {
                event.preventDefault();
                populateProfileModal(); // Populate and reset fields before showing (no data arg, so uses PHP session)
                profileModal.style.display = "flex";
            };
        }

        editProfileBtn.onclick = function() {
            profileFirstname.readOnly = false;
            profileLastname.readOnly = false;
            profileEmail.readOnly = false;
            profileMobileNumber.readOnly = false;

            editProfileBtn.style.display = "none";
            saveProfileBtn.style.display = "inline-block";
            profileMessage.innerHTML = ""; // Clear message when entering edit mode
        };

        cancelProfileEditBtn.onclick = function() {
            profileModal.style.display = "none";
            populateProfileModal(); // Reset fields to original state (no data arg)
        };

        profileEditForm.onsubmit = async function(event) {
            event.preventDefault(); // Prevent default form submission

            profileMessage.innerHTML = ""; // Clear previous messages

            // Basic client-side validation
            if (!profileFirstname.value || !profileLastname.value || !profileEmail.value || !profileMobileNumber.value) {
                profileMessage.innerHTML = "<div style='color: red;'>All fields are required.</div>";
                return;
            }
            if (!/\S+@\S+\.\S+/.test(profileEmail.value)) {
                profileMessage.innerHTML = "<div style='color: red;'>Invalid email format.</div>";
                return;
            }

            const formData = new FormData(profileEditForm);

            try {
                // Corrected path for fetch request
                const response = await fetch('system/update_profile.php', { // Path relative to dashboard.php
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    profileMessage.innerHTML = `<div style='color: green;'>${result.message}</div>`;
                    // Update input values directly from returned data for instant feedback
                    if (result.updated_data) {
                        // THIS IS THE KEY CHANGE: Pass the updated_data to populateProfileModal
                        // This ensures the fields are updated from the fresh AJAX response, not stale PHP session values.
                        populateProfileModal(result.updated_data);

                        // Update the hidden CSRF token for future requests, if it's regenerated
                        const newCsrfTokenInput = profileEditForm.querySelector('input[name="csrf_token"]');
                        if (newCsrfTokenInput && result.new_csrf_token) {
                            newCsrfTokenInput.value = result.new_csrf_token;
                        }
                    }
                } else {
                    profileMessage.innerHTML = `<div style='color: red;'>${result.message}</div>`;
                     // If update failed, regenerate CSRF token immediately to allow a retry
                     const newCsrfTokenInput = profileEditForm.querySelector('input[name="csrf_token"]');
                        if (newCsrfTokenInput && result.new_csrf_token) {
                            newCsrfTokenInput.value = result.new_csrf_token;
                        }
                }
            } catch (error) {
                console.error('Error saving profile:', error);
                profileMessage.innerHTML = `<div style='color: red;'>An error occurred: ${error.message}</div>`;
            }
        };

        window.onclick = function(event) {
            if (event.target == logoutModal) { // Only close logout modal if clicking outside it
                logoutModal.style.display = "none";
            }
            // REMOVED: Close booking modal if clicking outside it
            // if (event.target == bookingConfirmationModal) {
            //     bookingConfirmationModal.style.display = "none";
            // }
            // NEW: Close profile modal if clicking outside it
            if (event.target == profileModal) {
                profileModal.style.display = "none";
                populateProfileModal(); // Reset fields to original state on close (no data arg)
            }
        }

        // --- New JavaScript for Dynamic Content Loading ---

        const mainContentArea = document.getElementById('main-content-area');
        const pageTitleElement = document.getElementById('page-title');
        const sidebarLinks = document.querySelectorAll('.side-menu ul li a.sidebar-link'); // Added class selector

        // Store the original dashboard overview HTML in a JS variable for quick loading
        // PHP's addslashes is crucial here for correct JS string escaping
        const initialDashboardHtml = `<?php echo addslashes($dashboard_overview_html); ?>`;

        // Function to update the header title
        function updatePageTitle(title) {
            pageTitleElement.innerHTML = `Dashboard / <span>${title}</span>`;
        }

        // Function to load content dynamically
        async function loadPage(pageName) {
            let url = '';
            let title = '';

            // Determine the URL and title based on the pageName
            switch (pageName) {
                case 'dashboard':
                    url = ''; // Special case: load local content
                    title = 'Overview';
                    break;
                case 'booking':
                    // Path: dashboard.php is in 'system/', booking.php is in 'system/system/'.
                    // So, from dashboard.php, you go into 'system' subfolder to find booking.php
                    url = 'system/booking.php';
                    title = 'Laundry Request';
                    break;
                case 'request_status':
                    // Path: dashboard.php is in 'system/', request_status/index.php is in 'system/system/request_status/'.
                    // So, from dashboard.php, you go into 'system' subfolder, then 'request_status' subfolder.
                    url = 'system/request_status/index.php';
                    title = 'Request Status';
                    break;
                default:
                    console.error("Unknown page:", pageName);
                    mainContentArea.innerHTML = '<p style="color: red;">Error: Page not found.</p>';
                    return;
            }

            updatePageTitle(title); // Update the header title immediately

            // Reset active class on all sidebar links
            sidebarLinks.forEach(link => {
                link.closest('li').classList.remove('active-sidebar-item');
            });

            // Set active class on the current clicked link's parent <li>
            const activeLink = document.querySelector(`.side-menu ul li a[data-page="${pageName}"]`);
            if (activeLink) {
                activeLink.closest('li').classList.add('active-sidebar-item');
            }


            if (pageName === 'dashboard') {
                // For 'dashboard', load the pre-rendered HTML content
                mainContentArea.innerHTML = initialDashboardHtml;
            } else {
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        // Log the error response text to help debug
                        const errorText = await response.text();
                        console.error(`HTTP error! status: ${response.status} for URL: ${url}`, errorText);
                        throw new Error(`Failed to load content for ${pageName}. Status: ${response.status}`);
                    }
                    const html = await response.text();

                    // Create a temporary div to parse the fetched HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;

                    // Extract the relevant content using the new IDs
                    let contentToInject = '';
                    if (pageName === 'booking') {
                        // Look for the div with id="booking-main-content"
                        const bookingContentDiv = tempDiv.querySelector('#booking-main-content');
                        if (bookingContentDiv) {
                            contentToInject = bookingContentDiv.outerHTML;
                        } else {
                            console.warn(`Could not find specific content div with ID 'booking-main-content' for ${pageName}. Injecting full body content.`);
                            contentToInject = tempDiv.innerHTML; // Fallback
                        }
                    } else if (pageName === 'request_status') {
                        // Look for the div with id="request-status-main-content"
                        const statusContentDiv = tempDiv.querySelector('#request-status-main-content');
                        if (statusContentDiv) {
                            // Find and modify the 'Back to Home' button within the fetched content
                            const backButton = statusContentDiv.querySelector('a[href*="/LaundryManagement%20System/system/dashboard.php"]');
                            if (backButton) {
                                backButton.setAttribute('href', 'javascript:void(0);');
                                backButton.onclick = () => loadPage('dashboard');
                                backButton.style.float = 'none';
                                backButton.style.display = 'block';
                                backButton.style.margin = '10px auto 25px auto';
                            }
                            contentToInject = statusContentDiv.outerHTML;
                        } else {
                            console.warn(`Could not find specific content div with ID 'request-status-main-content' for ${pageName}. Injecting full body content.`);
                            contentToInject = tempDiv.innerHTML; // Fallback
                        }
                    } else {
                        // Generic fallback for other pages: inject the full parsed HTML
                        contentToInject = tempDiv.innerHTML;
                    }

                    mainContentArea.innerHTML = contentToInject;

                    // Re-execute scripts within the newly loaded content
                    mainContentArea.querySelectorAll('script').forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        document.body.appendChild(newScript);
                        oldScript.remove();
                    });

                } catch (error) {
                    console.error('Error loading page:', error);
                    mainContentArea.innerHTML = `<p style="color: red;">Error loading content: ${error.message}. Please try again.</p>`;
                }
            }
        }

        // Add event listeners to sidebar links
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent actual navigation
                const pageName = this.dataset.page;
                loadPage(pageName);
            });
        });

        // NEW: Notification functions (Copied from adminpage.php)
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


        // Initial load: ensure dashboard overview is displayed and active link is set
        document.addEventListener('DOMContentLoaded', function() {
            // Set 'dashboard' as active link on initial load
            const dashboardLink = document.querySelector('.side-menu ul li a[data-page="dashboard"]');
            if (dashboardLink) {
                dashboardLink.closest('li').classList.add('active-sidebar-item');
            }
            // The dashboard overview HTML is already in place due to PHP echo.

            // NEW: Check for booking success message on page load and display notification
            const bookingMessage = <?php echo json_encode($_SESSION['booking_modal_message'] ?? null); ?>;
            const bookingType = <?php echo json_encode($_SESSION['booking_modal_type'] ?? null); ?>;

            // Clear session messages immediately after retrieving them
            <?php
            unset($_SESSION['booking_modal_message']); // Clear message after displaying
            unset($_SESSION['booking_modal_type']); // Clear type
            ?>

            if (bookingMessage) {
                showNotification(bookingMessage, bookingType);
            }
        });
    </script>
    <!-- Include Bootstrap JS here if it's needed by the dynamically loaded content -->
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>