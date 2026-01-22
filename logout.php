<?php
session_start();
session_destroy(); // This clears the user data
header("Location: login.php"); // Send them to the start
exit();
?>