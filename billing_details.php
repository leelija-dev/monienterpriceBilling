<?php
// billing_details.php

// Database credentials - adjust these to your settings
$host = "localhost";
$dbname = "trueacc_billing";
$user = "root";
$pass = "";

// Create connection using mysqli
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2>Billing Details</h2>
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
    <p>No billing details found.</p>
  <?php endif; ?>
</div>
</body>
</html>

<?php
$conn->close();
?>
