<?php
session_start(); // Ensure session is started
require_once "../db_connect.php"; // Use require_once

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../home.php"); // Redirect to home/login if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Laundry Request Status</title>
</head>
<!-- Removed inline body styles, as dashboard.php's styles will handle the overall layout -->
<body>

<!-- ADDED id="request-status-main-content" and REMOVED width: 90%; text-align: center; from here.
     The width and alignment will be controlled by dashboard.php's CSS for #main-content-area #request-status-main-content -->
<div id="request-status-main-content" style="background-color: #ffffff; padding: 25px;">
    <!-- The button's style will be overridden by JS in dashboard.php -->
     
    <table class="table table-bordered" style="background-color: #ffffff; margin: 20px auto; box-shadow: 0 4px 8px rgba(40, 148, 236, 0.1); border-radius: 8px;">
      <thead style="background-color: gray;">
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
        </tr>
      </thead>
      <tbody>
        <?php
        // Fetch ALL booking data for the logged-in user (REMOVED LIMIT 1)
        $stmt = $conn->prepare("SELECT * FROM booking WHERE user_id = ? ORDER BY id DESC");
        if ($stmt === false) {
            error_log("Request Status Prepare Failed: " . $conn->error);
            echo "<tr><td colspan='10' style='text-align:center;'>Error retrieving data.</td></tr>";
        } else {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if there are any results
            if ($result && $result->num_rows > 0) {
                // Loop through and display ALL bookings
                while ($row = $result->fetch_assoc()) {
                    // Sanitize all output to prevent XSS
                    $id = htmlspecialchars($row['id']);
                    $firstname = htmlspecialchars($row['firstname']);
                    $lastname = htmlspecialchars($row['lastname']);
                    $date = htmlspecialchars($row['date']);
                    $garment_type = htmlspecialchars($row['garment_type']);
                    $package = htmlspecialchars($row['package']);
                    $detergent_powder = $row['detergent_powder'] ? 'Yes' : 'No';
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
                            <td>$weight</td> <!-- Display the weight -->
                            <td>$payment_mode</td>
                            <td style='background: green; color:white; font-weight:600; padding:.15em .3em; inline-height:1; font-size:75%; display:inline-block; margin-top:10px; border-radius:7px;'>$status</td>
                        </tr>";
                }
            } else {
                // Display a message if no bookings are found for the user
                echo "<tr><td colspan='10' style='text-align:center;'>No booking data found for your account.</td></tr>";
            }
            $stmt->close();
        }
        ?>
      </tbody>
    </table>
</div>

</body>
</html>