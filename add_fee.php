<?php
require_once "auth.php"; // Add this line
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['student_id']) && !empty($_POST['total_amount'])) {
        
        $student_id = (int)$_POST['student_id'];
        $total_amount = (float)$_POST['total_amount'];
        $paid_amount = (float)$_POST['paid_amount'];
        
        $remaining = $total_amount - $paid_amount;
        if ($remaining <= 0) {
            $status = 'Paid';
        } elseif ($paid_amount > 0) {
            $status = 'Partial';
        } else {
            $status = 'Unpaid';
        }
        
        $sql = "INSERT INTO fees (student_id, total_amount, paid_amount, status) VALUES ($student_id, $total_amount, $paid_amount, '$status')";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: fees.php?success=1");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Please fill all required fields.";
    }
}
?>