<?php
// logout.php - Session destruction script
session_start();
session_unset();
session_destroy();
header("Location: login.php?msg=" . urlencode("You have logged out successfully."));
exit;
?>
