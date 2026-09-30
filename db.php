<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hms_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-update database for new features
$checkRoomId = $conn->query("SHOW COLUMNS FROM students LIKE 'room_id'");
if($checkRoomId && $checkRoomId->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN room_id INT DEFAULT NULL");
}

$checkPaidAmount = $conn->query("SHOW COLUMNS FROM fees LIKE 'paid_amount'");
if($checkPaidAmount && $checkPaidAmount->num_rows == 0) {
    $conn->query("ALTER TABLE fees CHANGE amount total_amount DECIMAL(10,2) NOT NULL");
    $conn->query("ALTER TABLE fees ADD COLUMN paid_amount DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount");
    $conn->query("ALTER TABLE fees MODIFY COLUMN status ENUM('Paid', 'Unpaid', 'Partial') DEFAULT 'Unpaid'");
}

$checkCnic = $conn->query("SHOW COLUMNS FROM students LIKE 'cnic'");
if($checkCnic && $checkCnic->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN cnic VARCHAR(20) DEFAULT '' AFTER name");
}

$checkFeePeriod = $conn->query("SHOW COLUMNS FROM fees LIKE 'fee_period'");
if($checkFeePeriod && $checkFeePeriod->num_rows == 0) {
    $conn->query("ALTER TABLE fees ADD COLUMN fee_period VARCHAR(100) DEFAULT 'Monthly' AFTER student_id");
}

$checkMonthlyFee = $conn->query("SHOW COLUMNS FROM students LIKE 'monthly_fee'");
if($checkMonthlyFee && $checkMonthlyFee->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN monthly_fee DECIMAL(10,2) DEFAULT 0.00 AFTER phone");
}

$checkStudentPassword = $conn->query("SHOW COLUMNS FROM students LIKE 'password'");
if($checkStudentPassword && $checkStudentPassword->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN password VARCHAR(255) NULL");
    
    // Set default password to '123456' for existing students
    $default_student_pwd = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("UPDATE students SET password = '$default_student_pwd' WHERE password IS NULL");
}

$checkFeeCreatedAt = $conn->query("SHOW COLUMNS FROM fees LIKE 'created_at'");
if($checkFeeCreatedAt && $checkFeeCreatedAt->num_rows == 0) {
    $conn->query("ALTER TABLE fees ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$conn->query("CREATE TABLE IF NOT EXISTS fee_generations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month_year VARCHAR(10) NOT NULL UNIQUE,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'admin'");
if ($checkAdmin && $checkAdmin->num_rows == 0) {
    $hashed_password = password_hash('password', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password) VALUES ('admin', '$hashed_password')");
}
?>