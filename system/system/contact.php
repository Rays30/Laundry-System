<?php
session_start();
// No DB connection needed for this simple example, but could be if storing messages
// require_once('db_connect.php');

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

$message_status = ''; // To show success/error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $message_status = '<div style="color: red; margin-bottom: 15px;">Invalid request (CSRF token missing or invalid).</div>';
    } else {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $message = htmlspecialchars(trim($_POST['message']));

        // Basic server-side validation
        if (empty($name) || empty($email) || empty($message)) {
            $message_status = '<div style="color: red; margin-bottom: 15px;">Please fill in all fields.</div>';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message_status = '<div style="color: red; margin-bottom: 15px;">Invalid email format.</div>';
        } else {
            // Here you would typically send an email or save to database
            // Example: mail('admin@example.com', 'Contact Form Submission', "From: $name ($email)\n\n$message");
            // For now, just a success message:
            $message_status = '<div style="color: green; margin-bottom: 15px;">Thank you for your message! We will get back to you shortly.</div>';
        }
    }
}

$csrf_token = generateCsrfToken(); // Generate CSRF token for the form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="contact.css">
    <title>Contact Laundrylux</title>
    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f4f4f4;
        }

        /* Container styling */
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3498db;
        }

        p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .contact-info {
            margin-top: 20px;
            font-weight: bold;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group textarea {
            resize: vertical;
            height: 80px;
        }

        .form-group input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
        }

        .form-group input[type="submit"]:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Contact Laundry System</h2>
        <p>At Laundry Systems, we are dedicated to providing you with exceptional service and support. Whether you have questions about our offerings, need assistance with your laundry needs, or want to schedule a consultation, our team of experts is here to help! Your satisfaction is our top priority, and we look forward to assisting you.
        Reach out to us today, and let’s make laundry easy together!</p>
        <p>Fill out the form to tell us about your business needs and we’ll reach out to discuss how our industry-leading equipment and services can help you generate more profit.</p>
        <p class="contact-info">If you need immediate assistance, call us at 1-800-645-2204.</p>

        <?php echo $message_status; // Display form submission status ?>

        <!-- Contact Form -->
        <form action="#" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit">
            </div>
        </form>
    </div>

</body>
</html>