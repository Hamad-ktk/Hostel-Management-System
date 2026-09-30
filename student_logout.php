<?php
session_start();
// Unset all of the session variables for student
unset($_SESSION['student_id']);
unset($_SESSION['student_name']);
header("Location: student_login.php");
exit();
?>