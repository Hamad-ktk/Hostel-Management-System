<?php
require_once 'auth.php';
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: rooms.php");
    exit();
}

$id = (int)$_GET['id'];

// Fetch the room
$result = $conn->query("SELECT * FROM rooms WHERE id = $id");
if ($result->num_rows == 0) {
    die("Room not found.");
}
$room = $result->fetch_assoc();

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_number = $conn->real_escape_string($_POST['room_number']);
    $capacity = (int)$_POST['capacity'];
    
    $sql = "UPDATE rooms SET room_number='$room_number', capacity=$capacity WHERE id=$id";
    
    try {
        $conn->query($sql);
        header("Location: rooms.php?updated=1");
        exit();
    } catch (\Exception $e) {
        if ($e->getCode() == 1062 || $conn->errno == 1062) {
            $error = "Room No '$room_number' already exists. Please try a different Room No.";
        } else {
            $error = "Error updating record: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Edit Room - HMS</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="app-container">
      <div class="sidebar">
        <div class="logo">HMS Admin</div>
        <ul class="nav-links">
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="students.php">Students</a></li>
          <li><a href="rooms.php" class="active">Rooms</a></li>
          <li><a href="fees.php">Fees</a></li>
        </ul>
      </div>
      <div class="main-content">
        <div class="topbar">
          <div class="topbar-title">Edit Room</div>
        </div>
        <div class="content">
          <div class="card" style="max-width: 500px;">
            <h3>Update Room Details</h3>
            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            <form method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
              <label>
                Room Number:<br>
                <input type="text" name="room_number" value="<?php echo htmlspecialchars($room['room_number']); ?>" required style="padding: 8px; width: 100%; box-sizing: border-box;">
              </label>
              <label>
                Capacity:<br>
                <input type="number" name="capacity" value="<?php echo htmlspecialchars($room['capacity']); ?>" required style="padding: 8px; width: 100%; box-sizing: border-box;">
              </label>
              <button type="submit" class="btn-primary">Update Room</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>