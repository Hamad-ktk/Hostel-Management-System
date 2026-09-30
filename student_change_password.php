<?php
require_once 'student_auth.php';
require 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $student_id = $_SESSION['student_id'];
        $stmt = $conn->prepare("SELECT password FROM students WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            
            if ($stmt->fetch()) {
                if (password_verify($current_password, $hashed_password)) {
                    $stmt->close();
                    
                    $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("si", $new_hashed, $student_id);
                        if ($update_stmt->execute()) {
                            $success = "Password successfully changed.";
                        } else {
                            $error = "Failed to update password.";
                        }
                        $update_stmt->close();
                    } else {
                        $error = "Database error during update.";
                    }
                } else {
                    $error = "Incorrect current password.";
                }
            } else {
                $error = "Student not found.";
            }
        } else {
            $error = "Database error.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Portal - Change Password</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="app-container">
      <div class="sidebar" style="background-color: #2c3e50;">
        <div class="logo">Student Portal</div>
        <ul class="nav-links">
          <li><a href="student_dashboard.php">My Dashboard</a></li>
          <li><a href="student_change_password.php" class="active">Change Password</a></li>
          <li style="margin-top: 20px;"><a href="student_logout.php" style="color: #ff6b6b;">Logout</a></li>
        </ul>
      </div>
      <div class="main-content">
        <div class="topbar">
          <div class="topbar-title">Change Password</div>
          <div class="user-info"><?php echo htmlspecialchars($_SESSION['student_name']); ?></div>
        </div>
        <div class="content">
          <div class="card" style="max-width: 500px; margin: 0 auto; margin-top: 50px;">
            <h3 style="margin-bottom: 20px;">Change Password</h3>
            <?php if (!empty($error)): ?>
                <div style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div style="color: green; margin-bottom: 15px;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="student_change_password.php" style="display: flex; flex-direction: column; gap: 15px;">
              <div>
                <label style="display: block; margin-bottom: 5px;">Current Password</label>
                <input type="password" name="current_password" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              </div>
              <div>
                <label style="display: block; margin-bottom: 5px;">New Password</label>
                <input type="password" name="new_password" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              </div>
              <div>
                <label style="display: block; margin-bottom: 5px;">Confirm New Password</label>
                <input type="password" name="confirm_password" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              </div>
              <button type="submit" style="padding: 10px; font-size: 16px; margin-top: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Update Password</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>