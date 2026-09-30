<?php
require_once 'auth.php';
require 'db.php';
require_once 'auto_fee.php';

$total_students = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT SUM(capacity) AS count FROM rooms")->fetch_assoc()['count'];
$pending_fees = $conn->query("SELECT COUNT(*) AS count FROM fees WHERE status != 'Paid'")->fetch_assoc()['count'];

if (!$total_rooms) $total_rooms = 0;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HMS - Dashboard</title>
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
            <li><a href="fees.php">Fees</a></li>
            <li style="margin-top: 20px;"><a href="change_password.php">Change Password</a></li>
            <li><a href="logout.php" style="color: #ff6b6b;">Logout</a></li>
        </ul>
      </div>
      <div class="main-content">
        <div class="topbar">
          <div class="topbar-title">Dashboard</div>
          <div class="user-info"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
        </div>
        <div class="content">
          <h1>Welcome to Dashboard</h1>
          <p>This is the main overview of the Hostel Management System.</p>
          
          <div class="dashboard-cards">
            <div class="card">
              <h3>Total Students</h3>
              <p><?php echo $total_students; ?></p>
            </div>
            <div class="card">
              <h3>Bed Capacity</h3>
              <p><?php echo $total_rooms; ?></p>
            </div>
            <div class="card">
              <h3>Pending Fee Records</h3>
              <p><?php echo $pending_fees; ?></p>
            </div>
          </div>

          <!-- Quick Search Section -->
          <div class="card" style="margin-top: 30px;">
            <h3>Search Students</h3>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">Search by Student Name, CNIC, or Room Number</p>
            <form method="GET" action="index.php" style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Enter name, cnic or room no..." required 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 300px;">
                <button type="submit" class="btn-primary">Search</button>
                <?php if(isset($_GET['search'])): ?>
                    <a href="index.php" class="btn-sm" style="padding: 10px; text-decoration: none; background: #95a5a6;">Clear Search</a>
                <?php endif; ?>
            </form>

            <?php if(isset($_GET['search'])): ?>
                <div style="margin-top: 20px;">
                    <h4>Search Results for "<?php echo htmlspecialchars($_GET['search']); ?>"</h4>
                    <table class="data-table" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>CNIC</th>
                                <th>Phone</th>
                                <th>Room No</th>
                                <th>Total Unpaid (RS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $search = $conn->real_escape_string($_GET['search']);
                            
                            // Query to join students, rooms, and aggregate fees to get total unpaid
                            $sql = "SELECT s.id, s.name, s.phone, s.cnic, r.room_number,
                                    (SELECT SUM(total_amount - paid_amount) FROM fees WHERE student_id = s.id AND status != 'Paid') AS total_unpaid
                                    FROM students s 
                                    LEFT JOIN rooms r ON s.room_id = r.id 
                                    WHERE s.name LIKE '%$search%' OR r.room_number = '$search' OR s.cnic LIKE '%$search%'";
                                    
                            $result = $conn->query($sql);
                            if($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>".htmlspecialchars($row['name'])."</td>";
                                    echo "<td>".htmlspecialchars($row['cnic'])."</td>";
                                    echo "<td>".htmlspecialchars($row['phone'])."</td>";
                                    echo "<td>".(!empty($row["room_number"]) ? "Room ".htmlspecialchars($row["room_number"]) : "<span style='color:red'>Unassigned</span>")."</td>";
                                    
                                    $unpaid = $row['total_unpaid'] ? $row['total_unpaid'] : 0;
                                    if($unpaid > 0) {
                                        echo "<td style='color: red; font-weight: bold;'>RS " . number_format($unpaid, 2) . "</td>";
                                    } else {
                                        echo "<td style='color: green;'>RS 0.00 (All Cleared)</td>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No students found matching your search.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>