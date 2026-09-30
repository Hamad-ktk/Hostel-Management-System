<?php
require_once "auth.php"; // Add this line
require 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM fees WHERE id = $id";
    $conn->query($sql);
}

header("Location: fees.php");
exit();
?>