<?php
require_once 'auth.php';
require 'db.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HMS - Fees</title>
    <link rel="stylesheet" href="style.css">
    <style>
      .student-option:hover {
          background-color: #f1f1f1;
      }
    </style>
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
          <div class="topbar-title">Fees</div>
          <div class="user-info">Admin User</div>
        </div>
        <div class="content">
          <div class="page-header">
            <h1>Fee Records</h1>
          </div>

          <!-- Add Fee Form -->
          <div class="card" style="margin-bottom: 20px;">
            <h3>Add Fee Record</h3>
            <form action="add_fee.php" method="POST" id="addFeeForm" style="display: flex; gap: 10px; align-items: center; margin-top: 10px; flex-wrap: wrap;">
              <div style="position: relative; flex-grow: 1; max-width: 300px;">
                  <input type="text" id="studentSearch" placeholder="Search by Name or CNIC..." style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%; box-sizing: border-box;" autocomplete="off" required>
                  <input type="hidden" name="student_id" id="studentIdFallback">
                  <ul id="studentDropdown" style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #ccc; border-radius: 4px; width: 100%; max-height: 200px; overflow-y: auto; list-style: none; padding: 0; margin: 0; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                      <?php
                      $student_query = "SELECT id, name, cnic FROM students";
                      $students = $conn->query($student_query);
                      if ($students && $students->num_rows > 0) {
                          while($s = $students->fetch_assoc()) {
                              $searchText = strtolower(htmlspecialchars($s['name'], ENT_QUOTES) . " " . htmlspecialchars($s['cnic'], ENT_QUOTES));
                              echo "<li class='student-option' data-id='".$s['id']."' data-search='".$searchText."' style='padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;'>".htmlspecialchars($s['name'])." (".htmlspecialchars($s['cnic']).")</li>";
                          }
                      }
                      ?>
                  </ul>
              </div>
              <input type="text" name="fee_period" placeholder="Duration (e.g. 15 Days, 2 Months)" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <input type="number" name="total_amount" placeholder="Total Rent (RS)" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <input type="number" name="paid_amount" placeholder="Amount Paid Now (RS)" value="0" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
              <button type="submit" class="btn-primary">Save Fee</button>
            </form>
          </div>

          <?php if(isset($_GET['success'])): ?>
            <p style="color: green; margin-bottom: 10px;">Fee record added successfully!</p>
          <?php endif; ?>
          <?php if(isset($_GET['updated'])): ?>
            <p style="color: blue; margin-bottom: 10px;">Fee payment updated successfully!</p>
          <?php endif; ?>

          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Duration</th>
                <th>Total Fee (RS)</th>
                <th>Paid (RS)</th>
                <th>Remaining (RS)</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT fees.id, fees.fee_period, fees.total_amount, fees.paid_amount, fees.status, students.name AS student_name 
                      FROM fees 
                      JOIN students ON fees.student_id = students.id 
                      ORDER BY fees.status DESC, fees.id DESC";
              $result = $conn->query($sql);

              if ($result && $result->num_rows > 0) {
                  while($row = $result->fetch_assoc()) {
                      $remaining = $row["total_amount"] - $row["paid_amount"];
                      
                      echo "<tr>";
                      echo "<td>" . $row["id"] . "</td>";
                      echo "<td>" . $row["student_name"] . "</td>";
                      echo "<td>" . htmlspecialchars($row["fee_period"]) . "</td>";
                      echo "<td>RS " . number_format($row["total_amount"], 2) . "</td>";
                      echo "<td>RS " . number_format($row["paid_amount"], 2) . "</td>";
                      echo "<td style='color: " . ($remaining > 0 ? "red" : "green") . "; font-weight: bold;'>RS " . number_format($remaining, 2) . "</td>";
                      
                      $statusClass = strtolower($row["status"]);
                      echo "<td><span class='status $statusClass'>" . $row["status"] . "</span></td>";
                      
                      echo "<td>";
                      if ($remaining > 0) {
                          echo "<a href='edit_fee.php?id=" . $row["id"] . "' class='btn-sm'>Pay</a> ";
                      } else {
                          echo "<span style='color: #888; font-size: 12px; display:inline-block; margin-right:5px;'>Cleared</span> ";
                      }
                      echo "<a href='receipt.php?id=" . $row["id"] . "' class='btn-sm' style='background-color:#28a745; margin-right:5px;' target='_blank'>Receipt</a> ";
                      echo "<a href='delete_fee.php?id=" . $row["id"] . "' class='btn-danger' onclick='return confirm(\"Are you sure you want to delete this record?\");'>Delete</a>";
                      echo "</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='8' style='text-align: center; color: #888;'>No fee records found. Add one above!</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
          const searchInput = document.getElementById('studentSearch');
          const hiddenId = document.getElementById('studentIdFallback');
          const dropdown = document.getElementById('studentDropdown');
          const options = document.querySelectorAll('.student-option');
          const form = document.getElementById('addFeeForm');

          searchInput.addEventListener('input', function() {
              const filter = searchInput.value.toLowerCase();
              let hasVisible = false;
              // Clear selection if they start typing
              hiddenId.value = ''; 

              options.forEach(opt => {
                  if(opt.getAttribute('data-search').indexOf(filter) > -1 || filter === '') {
                      opt.style.display = 'block';
                      hasVisible = true;
                  } else {
                      opt.style.display = 'none';
                  }
              });
              
              if(hasVisible && filter.length > 0) {
                  dropdown.style.display = 'block';
              } else {
                  dropdown.style.display = filter === '' ? 'block' : 'none';
              }
          });

          searchInput.addEventListener('focus', function() {
              dropdown.style.display = 'block';
          });

          document.addEventListener('click', function(e) {
              if(!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                  dropdown.style.display = 'none';
              }
          });

          options.forEach(opt => {
              opt.addEventListener('click', function() {
                  searchInput.value = opt.innerText;
                  hiddenId.value = opt.getAttribute('data-id');
                  dropdown.style.display = 'none';
              });
          });

          form.addEventListener('submit', function(e) {
              if(!hiddenId.value) {
                  e.preventDefault();
                  alert('Please search and select a valid student from the dropdown list.');
                  searchInput.focus();
              }
          });
      });
    </script>
  </body>
</html>