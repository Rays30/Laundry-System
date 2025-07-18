--- START OF FILE LaundryManagement System/system/system/logout.php ---
<?php
session_start(); // Start session at the very beginning

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the home page after successful logout
header("Location: home.php");
exit(); // Always call exit() after a header redirect to prevent further script execution
?>
--- END OF FILE LaundryManagement System/system/system/logout.php ---