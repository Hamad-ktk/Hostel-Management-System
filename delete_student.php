<?php
require_once "auth.php"; // Add this line
require 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM students WHERE id = $id";
    $conn->query($sql);
}

header("Location: students.php");
exit();
?>