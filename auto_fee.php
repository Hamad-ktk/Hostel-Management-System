<?php
require_once "auth.php"; // Add this line
require_once 'db.php';

$fee_period = date('F Y'); // e.g., May 2026

// Get all students who have a monthly fee assigned > 0
$students_sql = "SELECT id, monthly_fee FROM students WHERE monthly_fee > 0";
$students_res = $conn->query($students_sql);

if ($students_res && $students_res->num_rows > 0) {
    while ($std = $students_res->fetch_assoc()) {
        $sid = $std['id'];
        $amount = $std['monthly_fee'];
        
        // Check if this student ALREADY has a fee generated for the current month
        $check_fee_sql = "SELECT id FROM fees WHERE student_id = $sid AND fee_period = 'Auto-generated for $fee_period'";
        $check_res = $conn->query($check_fee_sql);
        
        // If no fee generated for this month, create one
        if ($check_res && $check_res->num_rows == 0) {
            $insert_fee = "INSERT INTO fees (student_id, fee_period, total_amount, paid_amount, status) VALUES ($sid, 'Auto-generated for $fee_period', $amount, 0, 'Unpaid')";
            $conn->query($insert_fee);
        }
    }
}
?>