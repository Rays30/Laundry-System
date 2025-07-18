<?php
session_start();
require_once('db_connect.php'); // Use require_once for critical files

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Optionally redirect to login or show an error
    $_SESSION['message'] = "Please log in to make a booking.";
    header("Location: home.php"); // Redirect to login page
    exit();
}

// Get user's first and last name from session for pre-filling
$user_firstname = isset($_SESSION['user_firstname']) ? htmlspecialchars($_SESSION['user_firstname']) : '';
$user_lastname = isset($_SESSION['user_lastname']) ? htmlspecialchars($_SESSION['user_lastname']) : '';

// Function to generate a CSRF token (still needed for the form to submit to booking_submit.php)
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Removed the 'if (isset($_POST['save-booked']))' block as submission is handled by booking_submit.php

$csrf_token = generateCsrfToken(); // Generate CSRF token for the form
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Book Now!</title>
  </head>
  <!-- Removed inline body styles, as dashboard.php's styles will handle the overall layout -->
  <body>

    <!-- ADDED id="booking-main-content" and REMOVED width:1000px from here.
         The width will be controlled by dashboard.php's CSS for #main-content-area #booking-main-content -->
    <div id="booking-main-content" style="background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
     <?php include('../system/controller/message.php');?>
    <h3>Book Now:</h3>
      <!-- Updated form action to point to the new submit handler -->
      <form action="system/booking_submit.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="firstname">First Name</label>
            <!-- Auto-filled and Readonly -->
            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First Name" value="<?php echo $user_firstname; ?>" readonly required>
          </div>
          <div class="form-group col-md-6">
            <label for="lastname">Last Name</label>
            <!-- Auto-filled and Readonly -->
            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last Name" value="<?php echo $user_lastname; ?>" readonly required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="inputAddress">Address</label>
            <input type="text" class="form-control" id="inputAddress"  name="address" placeholder="1234 Main St" required>
          </div>
          <div class="form-group col-md-6">
            <label for="date-scheduled">Date Scheduled:</label>
            <input type="date" class="form-control" id="date-scheduled" name="date" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="pick-delivery">Service Type:</label>
            <input type="text" class="form-control" id="pick-delivery" value="Pick Up and Delivery:" disabled>
          </div>
          <div class="form-group col-md-6">
            <label for="weight">Weight (kg):</label>
            <input type="text" class="form-control" id="weight" value="00" disabled>
          </div>
        </div>
        <div class="form-row">
        <div class="form-group col-md-6">
            <label for="garment-type">Type of Garments:</label>
            <select id="garment-type" name="garment_type" class="form-control" required>
            <option value="tshirt">Casual Wear</option>
            <option value="linen">Linen Items</option>
            <option value="bedding">Bedding</option>
            <option value="large_loads">Large Loads</option>
            <option value="mixed">Mixed</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="package">Package:</label>
            <select id="package" name="package" class="form-control" required>
            <option value="wash-only">Wash Only</option>
            <option value="wash-dryer">Wash & Dryer</option>
            <option value="wash-dry-fold">Wash, Dry, Fold</option>
            </select>
        </div>
        </div>

        <div class="form-row">
        <div class="form-group col-md-6">
            <label>Detergent:</label><br>
            <input type="checkbox" id="fabcon" name="detergent_powder">
            <label for="fabcon">Powder</label><br>
            <input type="checkbox" id="detergent" name="detergent_downy">
            <label for="detergent">Downy</label>
        </div>
        <div class="form-group col-md-6">
            <label for="payment-mode">Mode of Payment:</label>
            <input type="text" class="form-control" id="payment-mode" name="payment_mode" value="COD Only" disabled>
        </div>
        </div>
            <button type="submit" class="btn btn-primary" name="save-booked">Submit</button>
            <!-- Buttons removed as per request -->
    </form>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>