<?php
require_once "auth.php"; // Add this line
require 'db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$id = (int)$_GET['id'];

$sql = "SELECT fees.*, students.name AS student_name, students.cnic, students.phone, rooms.room_number 
        FROM fees 
        JOIN students ON fees.student_id = students.id 
        LEFT JOIN rooms ON students.room_id = rooms.id
        WHERE fees.id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Fee record not found.");
}
$fee = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Receipt - <?php echo htmlspecialchars($fee['student_name']); ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f0f2f5; padding: 20px; }
        .receipt-container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #7f8c8d; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        .details-table th { width: 35%; color: #34495e; }
        .total-section { border-top: 2px solid #333; padding-top: 15px; text-align: right;font-size: 18px; }
        .total-section strong { color: #2c3e50; }
        .print-btn { display: block; width: 100%; padding: 12px; background: #3498db; color: #fff; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; border: none; cursor: pointer; }
        @media print {
            .print-btn { display: none; }
            body { background: #fff; padding: 0; }
            .receipt-container { box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Hostel Management System</h1>
            <p>Fee Payment Receipt</p>
            <p><small>Date: <?php echo date('d M Y, h:i A'); ?></small></p>
        </div>
        
        <table class="details-table">
            <tr>
                <th>Receipt No:</th>
                <td>#<?php echo str_pad($fee['id'], 5, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <th>Student Name:</th>
                <td><?php echo htmlspecialchars($fee['student_name']); ?></td>
            </tr>
            <tr>
                <th>CNIC:</th>
                <td><?php echo htmlspecialchars($fee['cnic']); ?></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td><?php echo htmlspecialchars($fee['phone']); ?></td>
            </tr>
            <tr>
                <th>Room No:</th>
                <td><?php echo $fee['room_number'] ? htmlspecialchars($fee['room_number']) : 'Unassigned'; ?></td>
            </tr>
            <tr>
                <th>Duration / Period:</th>
                <td><strong><?php echo htmlspecialchars($fee['fee_period']); ?></strong></td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <?php 
                        if($fee['status'] == 'Paid') echo '<span style="color:green;font-weight:bold;">Fully Paid</span>';
                        else if($fee['status'] == 'Partial') echo '<span style="color:orange;font-weight:bold;">Partially Paid</span>';
                        else echo '<span style="color:red;font-weight:bold;">Unpaid</span>';
                    ?>
                </td>
            </tr>
        </table>

        <div class="total-section">
            <p>Total Fee: RS <?php echo number_format($fee['total_amount'], 2); ?></p>
            <p>Paid Amount: RS <?php echo number_format($fee['paid_amount'], 2); ?></p>
            <h3 style="<?php echo ($fee['total_amount'] - $fee['paid_amount'] > 0) ? 'color: red;' : 'color: green;'; ?>">
                Remaining Balance: RS <?php echo number_format($fee['total_amount'] - $fee['paid_amount'], 2); ?>
            </h3>
        </div>
        
        <button class="print-btn" onclick="window.print()">Print Receipt</button>
    </div>
</body>
</html>