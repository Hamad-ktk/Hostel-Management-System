<?php
require_once 'auth.php';
// Include the database connection at the top of the file
require 'db.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HMS - Rooms</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="app-container">
      <!-- Sidebar -->
      <div class="sidebar">
        <div class="logo">HMS Admin</div>
        <ul class="nav-links">
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="students.php">Students</a></li>
          <li><a href="rooms.php" class="active">Rooms</a></li>
          <li><a href="fees.php">Fees</a></li>
        </ul>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <div class="topbar">
          <div class="topbar-title">Rooms</div>
          <div class="user-info">Admin User</div>
        </div>

        <div class="content">
          <div class="page-header">
            <h1>Manage Rooms</h1>
          </div>

          <!-- Add Room Form -->
          <div class="card" style="margin-bottom: 20px;">
            <h3>Add New Room</h3>
            <form action="add_room.php" method="POST" style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
              <input type="text" name="room_number" placeholder="Room Number (e.g. 101)" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <input type="number" name="capacity" placeholder="Capacity (e.g. 3)" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <button type="submit" class="btn-primary">Save Room</button>
            </form>
          </div>

          <?php if(isset($_GET['success'])): ?>
            <p style="color: green; margin-bottom: 10px;">Room added successfully!</p>
          <?php endif; ?>
          <?php if(isset($_GET['updated'])): ?>
            <p style="color: blue; margin-bottom: 10px;">Room updated successfully!</p>
          <?php endif; ?>
          <?php if(isset($_GET['error'])): ?>
            <p style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($_GET['error']); ?></p>
          <?php endif; ?>

          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Room Number</th>
                <th>Capacity</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT * FROM rooms ORDER BY id DESC";
              $result = $conn->query($sql);

              if ($result && $result->num_rows > 0) {
                  while($row = $result->fetch_assoc()) {
                      echo "<tr>";
                      echo "<td>" . $row["id"] . "</td>";
                      echo "<td>" . $row["room_number"] . "</td>";
                      echo "<td>" . $row["capacity"] . " Persons</td>";
                      echo "<td>";
                      echo "<a href='edit_room.php?id=" . $row["id"] . "' class='btn-sm'>Edit</a> ";
                      echo "<a href='delete_room.php?id=" . $row["id"] . "' class='btn-danger' onclick='return confirm(\"Are you sure you want to delete this room?\");'>Delete</a>";
                      echo "</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='4' style='text-align: center; color: #888;'>No rooms found. Add one above!</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>