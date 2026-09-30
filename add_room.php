<?php
require_once "auth.php"; // Add this line
// ... existing file code
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['room_number']) && !empty($_POST['capacity'])) {
        
        $room_number = $conn->real_escape_string($_POST['room_number']);
        $capacity = (int)$_POST['capacity'];
        
        $sql = "INSERT INTO rooms (room_number, capacity) VALUES ('$room_number', $capacity)";
        
        try {
            $conn->query($sql);
            header("Location: rooms.php?success=1");
            exit();
        } catch (\Exception $e) {
            if ($e->getCode() == 1062 || $conn->errno == 1062) {
                $err = urlencode("Room No '$room_number' already exists. Please try a different Room No.");
            } else {
                $err = urlencode("Error: " . $e->getMessage());
            }
            header("Location: rooms.php?error=$err");
            exit();
        }
    } else {
        echo "Please fill all required fields.";
    }
} else {
    echo "Invalid request.";
}
?>