<?php
require_once 'auth.php';
require 'db.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HMS - Students</title>
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
          <div class="topbar-title">Students</div>
          <div class="user-info">Admin User</div>
        </div>
        <div class="content">
          <div class="page-header">
            <h1>Manage Students</h1>
          </div>

          <!-- Add Student Form -->
          <div class="card" style="margin-bottom: 20px;">
            <h3>Add New Student</h3>
            <form action="add_student.php" method="POST" style="display: flex; gap: 10px; align-items: center; margin-top: 10px; flex-wrap: wrap;">
              <input type="text" name="name" placeholder="Full Name" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <input type="text" name="cnic" placeholder="CNIC Number" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <input type="email" name="email" placeholder="Email Address" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <input type="text" name="phone" placeholder="Phone Number" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <input type="number" step="0.01" name="monthly_fee" placeholder="Monthly Fee (RS)" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <select name="room_id" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">No Room (Unassigned)</option>
                <?php
                $room_query = "SELECT id, room_number, capacity FROM rooms";
                $rooms = $conn->query($room_query);
                if ($rooms && $rooms->num_rows > 0) {
                    while($r = $rooms->fetch_assoc()) {
                        echo "<option value='".$r['id']."'>Room ".$r['room_number']." (Cap: ".$r['capacity'].")</option>";
                    }
                }
                ?>
              </select>
              <button type="submit" class="btn-primary">Save Student</button>
            </form>
          </div>

          <?php if(isset($_GET['success'])): ?>
            <p style="color: green; margin-bottom: 10px;">Student added successfully!</p>
          <?php endif; ?>
          <?php if(isset($_GET['updated'])): ?>
            <p style="color: blue; margin-bottom: 10px;">Student updated successfully!</p>
          <?php endif; ?>
          <?php if(isset($_GET['error'])): ?>
            <p style="color: red; margin-bottom: 10px; font-weight: bold;"><?php echo htmlspecialchars($_GET['error']); ?></p>
          <?php endif; ?>

          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>CNIC</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Monthly Fee</th>
                <th>Room No</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT students.*, rooms.room_number FROM students LEFT JOIN rooms ON students.room_id = rooms.id ORDER BY students.id DESC";
              $result = $conn->query($sql);

              if ($result && $result->num_rows > 0) {
                  while($row = $result->fetch_assoc()) {
                      echo "<tr>";
                      echo "<td>" . $row["id"] . "</td>";
                      echo "<td>" . $row["name"] . "</td>";
                      echo "<td>" . $row["cnic"] . "</td>";
                      echo "<td>" . $row["email"] . "</td>";
                      echo "<td>" . $row["phone"] . "</td>";
                      echo "<td>RS " . number_format($row["monthly_fee"], 2) . "</td>";
                      echo "<td>" . (!empty($row["room_number"]) ? $row["room_number"] : "<span style='color:red; font-size:12px;'>Unassigned</span>") . "</td>";
                      echo "<td>";
                      echo "<a href='edit_student.php?id=" . $row["id"] . "' class='btn-sm'>Edit</a> ";
                      echo "<a href='delete_student.php?id=" . $row["id"] . "' class='btn-danger' onclick='return confirm(\"Are you sure you want to delete this student?\");'>Delete</a>";
                      echo "</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='8' style='text-align: center; color: #888;'>No students found in database. Add one above!</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>