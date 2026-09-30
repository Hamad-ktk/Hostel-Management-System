<?php
require_once 'student_auth.php';
require 'db.php';

$student_id = $_SESSION['student_id'];

// Get student details
$stmt = $conn->prepare("
    SELECT s.*, r.room_number 
    FROM students s 
    LEFT JOIN rooms r ON s.room_id = r.id 
    WHERE s.id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();
$stmt->close();

// Get fee details
$time_filter = isset($_GET['months']) ? $_GET['months'] : 'all';
$query = "
    SELECT fee_period, total_amount, paid_amount, status, created_at 
    FROM fees 
    WHERE student_id = ? 
";
if ($time_filter !== 'all') {
    $months = (int)$time_filter;
    $query .= " AND created_at >= DATE_SUB(NOW(), INTERVAL $months MONTH) ";
}
$query .= " ORDER BY id DESC";

$fee_stmt = $conn->prepare($query);
$fee_stmt->bind_param("i", $student_id);
$fee_stmt->execute();
$fees_result = $fee_stmt->get_result();
$fee_stmt->close();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Portal - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
      .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
      }
      .info-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border-left: 4px solid #007bff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      }
      .info-label {
        font-size: 0.85em;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 5px;
        letter-spacing: 0.5px;
      }
      .info-value {
        font-size: 1.1em;
        font-weight: 600;
        color: #343a40;
      }
      .history-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 15px;
      }
      .history-table th, .history-table td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
      }
      .history-table th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      .history-table th:first-child {
        border-top-left-radius: 6px;
      }
      .history-table th:last-child {
        border-top-right-radius: 6px;
      }
      .history-table tbody tr {
        transition: background-color 0.2s ease;
      }
      .history-table tbody tr:hover {
        background-color: #f1f3f5;
      }
      .history-table tbody tr:last-child td {
        border-bottom: none;
      }
      .amount-cell {
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.05em;
        font-weight: 600;
      }
      .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
        text-align: center;
      }
      .badge-paid { background-color: #e3fbec; color: #198754; }
      .badge-unpaid { background-color: #fceceb; color: #dc3545; }
      .badge-partial { background-color: #fff3cd; color: #ffc107; }
      @media print {
        .sidebar, .topbar, form, button.print-btn {
          display: none !important;
        }
        .main-content {
          margin-left: 0 !important;
          padding: 0 !important;
        }
        .card {
          box-shadow: none !important;
          border: 1px solid #ccc;
          margin-bottom: 20px;
        }
        body {
          background-color: #fff;
        }
      }
    </style>
  </head>
  <body>
    <div class="app-container">
      <div class="sidebar" style="background-color: #2c3e50;">
        <div class="logo">Student Portal</div>
        <ul class="nav-links">
          <li><a href="student_dashboard.php" class="active">My Dashboard</a></li>
          <li><a href="student_change_password.php">Change Password</a></li>
          <li style="margin-top: 20px;"><a href="student_logout.php" style="color: #ff6b6b;">Logout</a></li>
        </ul>
      </div>
      
      <div class="main-content">
        <div class="topbar">
          <div class="topbar-title">Welcome, <?php echo htmlspecialchars($student['name']); ?></div>
          <div class="user-info"><?php echo htmlspecialchars($_SESSION['student_name']); ?></div>
        </div>

        <div class="content">
          <div class="card" style="margin-bottom: 20px;">
            <h3>My Profile</h3>
            <div class="info-grid" style="margin-top: 15px;">
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">CNIC</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['cnic']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Room Number</div>
                    <div class="info-value"><?php echo $student['room_number'] ? htmlspecialchars($student['room_number']) : 'Not Assigned'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Monthly Rent</div>
                    <div class="info-value">RS <?php echo number_format($student['monthly_fee'], 2); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['phone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
                </div>
            </div>
          </div>

          <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;">My Fee History</h3>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <form method="GET" action="student_dashboard.php" style="display: flex; gap: 10px; align-items: center; margin: 0;">
                        <label for="months" style="font-size: 0.9em; color: #6c757d;">Filter:</label>
                        <select name="months" id="months" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 4px; border: 1px solid #ced4da; font-size: 0.9em;">
                            <option value="all" <?php if($time_filter == 'all') echo 'selected'; ?>>All Time</option>
                            <option value="1" <?php if($time_filter == '1') echo 'selected'; ?>>Last 1 Month</option>
                            <option value="2" <?php if($time_filter == '2') echo 'selected'; ?>>Last 2 Months</option>
                            <option value="3" <?php if($time_filter == '3') echo 'selected'; ?>>Last 3 Months</option>
                            <option value="6" <?php if($time_filter == '6') echo 'selected'; ?>>Last 6 Months</option>
                        </select>
                    </form>
                    <button onclick="window.print()" class="print-btn" style="padding: 6px 12px; background-color: #198754; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em; font-weight: 600;">
                        Print Record
                    </button>
                </div>
            </div>
            <div style="margin-top: 15px; overflow-x: auto; background-color: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fee Period</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Balance</th>
                    <th style="text-align: center;">Status</th>
                    <th>Date Added</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($fees_result && $fees_result->num_rows > 0): ?>
                      <?php while($f = $fees_result->fetch_assoc()): ?>
                          <?php $balance = $f['total_amount'] - $f['paid_amount']; ?>
                          <tr>
                            <td><?php echo htmlspecialchars($f['fee_period']); ?></td>
                            <td class="amount-cell text-muted">RS <?php echo number_format($f['total_amount'], 2); ?></td>
                            <td class="amount-cell text-muted">RS <?php echo number_format($f['paid_amount'], 2); ?></td>
                            <td class="amount-cell" style="color: <?php echo $balance > 0 ? '#dc3545' : '#198754'; ?>;">
                                RS <?php echo number_format($balance, 2); ?>
                            </td>
                            <td style="text-align: center;">
                                <?php
                                if ($f['status'] == 'Paid') echo '<span class="badge badge-paid">Paid</span>';
                                elseif ($f['status'] == 'Unpaid') echo '<span class="badge badge-unpaid">Unpaid</span>';
                                else echo '<span class="badge badge-partial">Partial</span>';
                                ?>
                            </td>
                            <td style="color: #6c757d; font-size: 0.9em;"><?php echo date('d M Y, h:i A', strtotime($f['created_at'])); ?></td>
                          </tr>
                      <?php endwhile; ?>
                  <?php else: ?>
                      <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #6c757d;">No fee records found.</td>
                      </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>