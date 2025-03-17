<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
$admin_id = $_SESSION['admin_id'];

include 'db.php';

// Fetch billing records with associated customer details
$sql = "SELECT b.billing_id, b.total_quentity, b.billing_amount, b.billing_date, c.cust_name, c.cust_phone 
        FROM billing b 
        LEFT JOIN customers c ON b.cust_id = c.cust_id WHERE b.admin_id = $admin_id
        ORDER BY b.billing_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billing Records</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      display: flex;
      margin: 0;
      height: 100vh;
    }
    .sidebar {
      width: 200px;
      background-color: #f8f9fa;
      padding: 15px;
      border-right: 1px solid #ddd;
    }
    .content {
      flex-grow: 1;
      padding: 20px;
    }
  </style>
</head>
<body>
<div class="d-flex w-100">
  <!-- Include the sidebar (assumed to be at sidebar/sidebar.php) -->
  <?php include 'sidebar/sidebar.php'; ?>

  <!-- Main Content Area -->
  <div class="w-100">
    <!-- Top Navbar -->
    <nav class="navbar navbar-light bg-light w-100">
      <div class="container-fluid">
        <span class="navbar-text fw-bold">
          Welcome, <?php echo $_SESSION['admin_name']; ?>
        </span>
        <a class="btn btn-outline-danger" href="logout.php">Logout</a>
      </div>
    </nav>
    <div class="container mt-4 px-5">
      <div class="d-flex justify-content-between">
        <div><h2>Billing Records</h2></div>
        <div><button class="btn btn-success" onclick="window.location.href='add_billing.php'"><i class="fas fa-plus me-2"></i> Bill Generate</button></div>
      </div>
      <hr>
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>Billing ID</th>
            <th>Customer Name</th>
            <th>Customer Phone</th>
            <th>Total Quentity</th>
            <th>Billing Amount</th>
            <th>Billing Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($result && $result->num_rows > 0) {
              while($row = $result->fetch_assoc()){
                  echo "<tr>";
                  echo "<td>" . $row['billing_id'] . "</td>";
                  echo "<td>" . $row['cust_name'] . "</td>";
                  echo "<td>" . $row['cust_phone'] . "</td>";
                  echo "<td>" . $row['total_quentity'] . "</td>";
                  echo "<td>" . number_format($row['billing_amount'], 2) . "</td>";
                  echo "<td>" . $row['billing_date'] . "</td>";
                  // Action: Print Invoice button opens print_invoice.php in a new tab/window
                  echo '<td><a href="print_invoice.php?billing_id=' . $row['billing_id'] . '" class="btn btn-sm btn-primary" target="_blank">Print Invoice</a></td>';
                  echo "</tr>";
              }
          } else {
              echo '<tr><td class="text-center text-danger fw-bold" colspan="7">No billing records found.</td></tr>';
          }
          $conn->close();
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
