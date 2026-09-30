<?php
require_once "auth.php"; // Add this line
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['name']) && !empty($_POST['cnic']) && !empty($_POST['email']) && !empty($_POST['phone'])) {
        
        $name = $conn->real_escape_string($_POST['name']);
        $cnic = $conn->real_escape_string($_POST['cnic']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $monthly_fee = !empty($_POST['monthly_fee']) ? (float)$_POST['monthly_fee'] : 0.00;
        $room_id = !empty($_POST['room_id']) ? (int)$_POST['room_id'] : 'NULL';
        $default_pwd = password_hash('123456', PASSWORD_DEFAULT);
        
        if ($room_id !== 'NULL') {
            $cap_check = $conn->query("SELECT capacity, (SELECT COUNT(*) FROM students WHERE room_id = $room_id) as current_occupants FROM rooms WHERE id = $room_id");
            if ($cap_check && $cap_check->num_rows > 0) {
                $cap_data = $cap_check->fetch_assoc();
                if ($cap_data['current_occupants'] >= $cap_data['capacity']) {
                    $err = urlencode("Error: Selected room is full (Capacity: ".$cap_data['capacity']."). Please choose another room.");
                    header("Location: students.php?error=$err");
                    exit();
                }
            }
        }
        
        $sql = "INSERT INTO students (name, cnic, email, phone, monthly_fee, room_id, password) VALUES ('$name', '$cnic', '$email', '$phone', $monthly_fee, $room_id, '$default_pwd')";
        
        try {
            $conn->query($sql);
            header("Location: students.php?success=1");
            exit();
        } catch (\Exception $e) {
            if ($e->getCode() == 1062 || $conn->errno == 1062) {
                $err = urlencode("The email address '$email' is already registered to another student. Please use a different email.");
            } else {
                $err = urlencode("Error: " . $e->getMessage());
            }
            header("Location: students.php?error=$err");
            exit();
        }
    } else {
        echo "Please fill all required fields.";
    }
} else {
    echo "Invalid request method.";
}
?>