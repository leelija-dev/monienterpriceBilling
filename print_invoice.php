<?php
include 'db.php';

if(!isset($_GET['billing_id'])){
    die("Billing ID not provided.");
}

$billing_id = $_GET['billing_id'];

// Fetch billing info (including customer details)
$sql = "SELECT b.billing_date, b.billing_amount, c.cust_id, c.cust_name, c.cust_phone 
        FROM billing b 
        JOIN customers c ON b.cust_id = c.cust_id 
        WHERE b.billing_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $billing_id);
$stmt->execute();
$result = $stmt->get_result();
$billing_info = $result->fetch_assoc();
$stmt->close();

// Fetch billing items
$sql = "SELECT * FROM billing_details WHERE billing_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $billing_id);
$stmt->execute();
$itemsResult = $stmt->get_result();
$billing_items = array();
while($row = $itemsResult->fetch_assoc()){
    $billing_items[] = $row;
}
$stmt->close();

// Calculate totals
$totalBase = 0;
$totalGST = 0;
foreach($billing_items as $item) {
    $itemBase = $item['quantity'] * $item['unit_price'];
    $itemGST = $itemBase * $item['gst'] / 100;
    $totalBase += $itemBase;
    $totalGST += $itemGST;
}
$totalAmount = $totalBase + $totalGST;
$cgst = $totalGST / 2;
$sgst = $totalGST / 2;
?>
<!DOCTYPE html>
<html>
<head>
  <title>Invoice #<?php echo $billing_id; ?></title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print {
      body * {
         visibility: hidden;
      }
      #printableArea, #printableArea * {
         visibility: visible;
      }
      #printableArea {
         position: absolute;
         left: 0;
         right: 0;
         top: 0;
         box-shadow: none;
         /* margin: 0; */
         width: auto;
         page: 'A4';
      }
    }
  </style>
</head>
<body>
<div class="container mt-5 p-4 shadow" id="printableArea">
   <div class="row mb-3">
      <div class="col-md-6">
         <h4>Company Details</h4>
         <p>
            <strong>My Company</strong><br>
            123 Business Rd,<br>
            City, Country<br>
            Phone: 123-456-7890
         </p>
      </div>
      <div class="col-md-6 text-end">
         <h4>Customer Details</h4>
         <p>
            <strong><?php echo $billing_info['cust_name']; ?></strong><br>
            Customer ID: <?php echo $billing_info['cust_id']; ?><br>
            Phone: <?php echo $billing_info['cust_phone']; ?><br>
            Date: <?php echo $billing_info['billing_date']; ?>
         </p>
      </div>
   </div>
   <table class="table table-bordered">
      <thead>
         <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>GST (%)</th>
            <th>GST Amount</th>
            <th>Total</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach($billing_items as $item): 
             $base = $item['quantity'] * $item['unit_price'];
             $gstAmount = $base * $item['gst'] / 100;
             $itemTotal = $base + $gstAmount;
         ?>
         <tr>
            <td><?php echo $item['item_description']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td><?php echo number_format($item['unit_price'], 2); ?></td>
            <td><?php echo $item['gst']; ?></td>
            <td><?php echo number_format($gstAmount, 2); ?></td>
            <td><?php echo number_format($itemTotal, 2); ?></td>
         </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
   <div class="row">
      <div class="col-md-6"></div>
      <div class="col-md-6 text-end">
         <p><strong>Sub Total:</strong> <?php echo number_format($totalBase,2); ?></p>
         <p><strong>Total GST:</strong> <?php echo number_format($totalGST,2); ?></p>
         <p><strong>CGST:</strong> <?php echo number_format($cgst,2); ?></p>
         <p><strong>SGST:</strong> <?php echo number_format($sgst,2); ?></p>
         <p><strong>Total Amount:</strong> <?php echo number_format($totalAmount,2); ?></p>
      </div>
   </div>
</div>
<div class="container text-center mt-3">
   <button class="btn btn-primary" onclick="window.print()">Print Invoice</button>
   <a href="billing.php" class="btn btn-secondary">Back to Billing Records</a>
</div>
</body>
</html>
