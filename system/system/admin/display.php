<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <title>Laundry Request</title>
  </head>
  <body style="background-color: #e0e0e0;">

  <?php
  session_start();
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
      unset($_SESSION['csrf_token']); // Consume token
      return true;
  }

  $update_message = ''; // Variable to hold success/error messages
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status']) && isset($_POST['id'])) {
      // Validate CSRF token
      if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
          $update_message = "<div class='alert alert-danger'>Invalid request (CSRF token missing or invalid).</div>";
      } else {
          $new_status = $_POST['status'];
          $id = (int)$_POST['id']; // Cast to int for safety

          // Update the status in the database using prepared statements
          $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
          if ($stmt === false) {
              error_log("Display Status Update Prepare Failed: " . $conn->error);
              $update_message = "<div class='alert alert-danger'>Error preparing status update.</div>";
          } else {
              $stmt->bind_param("si", $new_status, $id);
              if ($stmt->execute()) {
                  $update_message = "<div class='alert alert-success' style='width: 50%; margin-left:62.5%; text-align: center; background-color: #d4edda;'>Booking status updated successfully.</div>";
              } else {
                  error_log("Display Status Update Execute Failed for ID $id: " . $stmt->error);
                  $update_message = "<div class='alert alert-danger' style='margin-top: 20px;'>Error updating status: " . htmlspecialchars($stmt->error) . "</div>";
              }
              $stmt->close();
          }
      }
  }

  // Generate new CSRF token for the page
  $csrf_token = generateCsrfToken();
  ?>

  <div class="container mt-5" style="text-align: center; width: 80%;">
    <?php
      // Display success or error message if any
      if ($update_message) {
          echo $update_message;
      }
    ?>

    <table class="table table-bordered" style="background-color: #f9f9f9; margin-left: 13%; margin-right: 13%; margin-bottom: 70px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border-radius: 8px; width: 100%; max-height: 500px; overflow-y: auto;">
      <thead style="background-color: #666666; color: white;">
        <tr>
          <th scope="col">Date</th>
          <th scope="col">Queue</th>
          <th scope="col">Customer Name</th>
          <th scope="col">Garment Type</th>
          <th scope="col">Package</th>
          <th scope="col">Detergent Powder</th>
          <th scope="col">Detergent Downy</th>
          <th scope="col">Weight</th>
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
                        <td>$weight kg</td> <!-- Display the weight value -->
                        <td>$payment_mode</td>
                        <td style='background: green; color:white; font-weight:600; padding:.15em .3em; font-size:75%; display:inline-block; margin-top:10px; border-radius:7px;'>$status</td>
                        <td>
                            <form method='POST' action=''>
                                <input type='hidden' name='id' value='$id'>
                                <input type='hidden' name='csrf_token' value='" . htmlspecialchars($csrf_token) . "'>
                                <select name='status' class='form-select' style='width: auto; display: inline-block; margin-right:20px;'>
                                    <option value='Pending' " . ($status == 'Pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='Processing' " . ($status == 'Processing' ? 'selected' : '') . ">Processing</option>
                                    <option value='Ready to Pickup' " . ($status == 'Ready to Pickup' ? 'selected' : '') . ">Ready to Pickup</option>
                                    <option value='Completed' " . ($status == 'Completed' ? 'selected' : '') . ">Service Completed</option>
                                </select>
                                <button type='submit' class='btn btn-warning' style='margin-top: 5px;'>Update</button>
                            </form>
                        </td>
                    </tr>";
            }
        } else {
            // If no bookings found
            echo "<tr><td colspan='11' style='text-align:center;'>No data found</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  </body>
</html>