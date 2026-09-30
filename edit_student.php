<?php
require_once 'auth.php';
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$id = (int)$_GET['id'];

$result = $conn->query("SELECT * FROM students WHERE id = $id");
if ($result->num_rows == 0) {
    die("Student not found.");
}
$student = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $cnic = $conn->real_escape_string($_POST['cnic']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $monthly_fee = !empty($_POST['monthly_fee']) ? (float)$_POST['monthly_fee'] : 0.00;
    $room_id = !empty($_POST['room_id']) ? (int)$_POST['room_id'] : 'NULL';
    
    $proceed = true;
    if ($room_id !== 'NULL') {
        $cap_check = $conn->query("SELECT capacity, (SELECT COUNT(*) FROM students WHERE room_id = $room_id AND id != $id) as current_occupants FROM rooms WHERE id = $room_id");
        if ($cap_check && $cap_check->num_rows > 0) {
            $cap_data = $cap_check->fetch_assoc();
            if ($cap_data['current_occupants'] >= $cap_data['capacity']) {
                $error = "Error: Selected room is full (Capacity: ".$cap_data['capacity']."). Please choose another room.";
                $proceed = false;
            }
        }
    }
    
    if ($proceed) {
        $sql = "UPDATE students SET name='$name', cnic='$cnic', email='$email', phone='$phone', monthly_fee=$monthly_fee, room_id=$room_id WHERE id=$id";
        try {
            $conn->query($sql);
            header("Location: students.php?updated=1");
            exit();
        } catch (\Exception $e) {
            if ($e->getCode() == 1062 || $conn->errno == 1062) {
                $error = "The email address '$email' is already registered to another student. Please use a different email.";
            } else {
                $error = "Error updating record: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Edit Student - HMS</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="app-container">
      <div class="sidebar">
        <div class="logo">HMS Admin</div>
        <ul class="nav-links">
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="students.php" class="active">Students</a></li>
          <li><a href="rooms.php">Rooms</a></li>
          <li><a href="fees.php">Fees</a></li>
        </ul>
      </div>
      <div class="main-content">
        <div class="topbar">
          <div class="topbar-title">Edit Student</div>
        </div>
        <div class="content">
          <div class="card" style="max-width: 500px;">
            <h3>Update Student Details</h3>
            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            <form method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
              <label>
                Full Name:<br>
                <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required style="padding: 8px; width: 100%; box-sizing: border-box;">
              </label>
              <label>
                CNIC Number:<br>
                <input type="text" name="cnic" value="<?php echo htmlspecialchars($student['cnic']); ?>" required style="padding: 8px; width: 100%; box-sizing: border-box;">
              </label>
              <label>
                Email Address:<br>
                <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required style="padding: 8px; width: 100%; box-sizing: border-box;">
              </label>
              <label>
                Phone Number:<br>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required style="padding: 8px; width: 100%; box-sizing: border-box;">
              </label>
              <label>
                Monthly Fee (RS):<br>
                <input type="number" step="0.01" name="monthly_fee" value="<?php echo htmlspecialchars($student['monthly_fee']); ?>" required style="padding: 8px; width: 100%; box-sizing: border-box;">
              </label>
              <label>
                Assigned Room:<br>
                <select name="room_id" style="padding: 8px; width: 100%; box-sizing: border-box;">
                  <option value="">No Room (Unassigned)</option>
                  <?php
                  $rooms = $conn->query("SELECT id, room_number FROM rooms");
                  while($r = $rooms->fetch_assoc()) {
                      $selected = ($r['id'] == $student['room_id']) ? "selected" : "";
                      echo "<option value='".$r['id']."' $selected>Room ".$r['room_number']."</option>";
                  }
                  ?>
                </select>
              </label>
              <button type="submit" class="btn-primary">Update Student</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>