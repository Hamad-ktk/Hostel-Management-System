<?php
require_once 'auth.php';
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: fees.php");
    exit();
}

$id = (int)$_GET['id'];

// Fetch the fee record with student name
$sql = "SELECT fees.*, students.name AS student_name 
        FROM fees 
        JOIN students ON fees.student_id = students.id 
        WHERE fees.id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Fee record not found.");
}
$fee = $result->fetch_assoc();
$remaining = $fee['total_amount'] - $fee['paid_amount'];

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $add_payment = (float)$_POST['add_payment'];
    
    if ($add_payment > 0) {
        $new_paid_amount = $fee['paid_amount'] + $add_payment;
        // Don't allow overpaying
        if ($new_paid_amount > $fee['total_amount']) {
            $new_paid_amount = $fee['total_amount'];
        }
        
        $new_remaining = $fee['total_amount'] - $new_paid_amount;
        
        if ($new_remaining <= 0) {
            $status = 'Paid';
        } else {
            $status = 'Partial';
        }
        
        $update_sql = "UPDATE fees SET paid_amount = $new_paid_amount, status = '$status' WHERE id = $id";
        if ($conn->query($update_sql) === TRUE) {
            header("Location: fees.php?updated=1");
            exit();
        } else {
            $error = "Error updating fee: " . $conn->error;
        }
    } else {
        $error = "Please enter a valid amount.";
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Edit Fee - HMS</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="app-container">
      <div class="sidebar">
        <div class="logo">HMS Admin</div>
        <ul class="nav-links">
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="students.php">Students</a></li>
          <li><a href="rooms.php">Rooms</a></li>
          <li><a href="fees.php" class="active">Fees</a></li>
        </ul>
      </div>
      <div class="main-content">
        <div class="topbar">
          <div class="topbar-title">Add Fee Payment</div>
        </div>
        <div class="content">
          <div class="card" style="max-width: 500px;">
            <h3>Update Fee for <?php echo htmlspecialchars($fee['student_name']); ?></h3>
            <div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <p><strong>Total Rent/Fee:</strong> $<?php echo number_format($fee['total_amount'], 2); ?></p>
                <p><strong>Already Paid:</strong> $<?php echo number_format($fee['paid_amount'], 2); ?></p>
                <p><strong style="color: red;">Remaining Balance: $<?php echo number_format($remaining, 2); ?></strong></p>
            </div>

            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            
            <?php if ($remaining > 0): ?>
            <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
              <label>
                Enter New Payment Amount ($):<br>
                <input type="number" name="add_payment" max="<?php echo $remaining; ?>" step="0.01" required style="padding: 8px; width: 100%; box-sizing: border-box; margin-top: 5px;">
              </label>
              <button type="submit" class="btn-primary">Submit Payment</button>
            </form>
            <?php else: ?>
                <p style="color: green; font-weight: bold;">This fee is fully paid!</p>
                <a href="fees.php" class="btn-sm" style="text-decoration:none; display:inline-block; margin-top:10px;">Go Back</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>