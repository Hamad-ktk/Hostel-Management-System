<?php
require_once "auth.php"; // Add this line
require 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM rooms WHERE id = $id";
    $conn->query($sql);
}

header("Location: rooms.php");
exit();
?>