<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php'; 

// Query to retrieve billing details along with customer information
$query = "
  SELECT 
    b.billing_id,
    b.billing_date,
    c.cust_name,
    c.cust_phone,
    bd.item_description,
    bd.quantity,
    bd.unit_price
  FROM billing b
  JOIN customers c ON b.cust_id = c.cust_id
  JOIN billing_details bd ON b.billing_id = bd.billing_id
  ORDER BY b.billing_date DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Billing Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">  
  <!-- <link rel="stylesheet" href="sidebar/sidebar.css"> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    /* Your sidebar and content styles here */
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
<?php include 'sidebar/sidebar.php'; ?>
<div class=" w-100">
<nav class="navbar navbar-light bg-light w-100">
<div class="container-fluid">
          <span class="navbar-text fw-bold">
            Welcome, <?php echo $_SESSION['customer_name']; ?>
          </span>
          <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        </div>
</nav>
<div class="content mt-4">
  <h2>Billing Details</h2>
  <hr>
  <?php if($result && $result->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Billing ID</th>
          <th>Date</th>
          <th>Customer Name</th>
          <th>Customer Phone</th>
          <th>Item Description</th>
          <th>Quantity</th>
          <th>Unit Price</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo($row['billing_id']) ?></td>
          <td><?php echo($row['billing_date']) ?></td>
          <td><?php echo($row['cust_name']) ?></td>
          <td><?php echo($row['cust_phone']) ?></td>
          <td><?php echo($row['item_description']) ?></td>
          <td><?php echo($row['quantity']) ?></td>
          <td><?php echo($row['unit_price']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="d-flex justify-content-center mt-5"><p class="m-0 h4 text-danger">No billing details found.</p></div>
  <?php endif; ?>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
