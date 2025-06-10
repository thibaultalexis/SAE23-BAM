<?php
// logout.php - Session termination
session_start();
session_destroy(); // Destroy all session data
header('Location: ../index.html'); // Redirect to home page
?>
