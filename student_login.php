<?php
session_start();
if (isset($_SESSION['student_id'])) {
    header("Location: student_dashboard.php");
    exit();
}

require_once 'db.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cnic = trim($_POST['cnic']);
    $password = $_POST['password'];

    if (empty($cnic) || empty($password)) {
        $error = "Please enter CNIC and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE cnic = ?");
        $stmt->bind_param("s", $cnic);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['student_id'] = $id;
                $_SESSION['student_name'] = $name;
                header("Location: student_dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Student not found with this CNIC.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HMS - Student Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 10px; box-sizing: border-box; }
        .login-btn { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body style="background-color: #f4f7f6;">
    <div class="login-container">
        <h2 style="text-align: center;">Student Login</h2>
        <?php if (!empty($error)) : ?>
            <div style="color: red; text-align: center; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>CNIC</label>
                <input type="text" name="cnic" placeholder="e.g. 12345-6789012-3" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
            <div style="text-align: center; margin-top: 15px; font-size: 0.9em; color: #777;">Default Password: 123456</div>
            <div style="text-align: center; margin-top: 15px;">
                <a href="login.php" style="color: #007bff; text-decoration: none;">Admin Login</a>
            </div>
        </form>
    </div>
</body>
</html>