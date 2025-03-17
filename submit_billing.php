<!-- < ?php
include 'db.php';

// Get customer details from form
$customer_name = $_POST['customer_name'];
$customer_phone = $_POST['customer_phone'];

// Check if customer exists by name
$sql = "SELECT cust_id FROM customers WHERE cust_name = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $customer_name);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    $stmt->bind_result($cust_id);
    $stmt->fetch();
} else {
    // Insert new customer if not exists
    $stmt->close();
    $sql = "INSERT INTO customers (cust_name, cust_phone) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $customer_name, $customer_phone);
    $stmt->execute();
    $cust_id = $stmt->insert_id;
}
$stmt->close();

// Insert billing record (initial billing_amount and total_quentity set as 0; update later)
$billing_date = date("Y-m-d H:i:s");
$sql = "INSERT INTO billing (cust_id, billing_amount, total_quentity, billing_date) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$dummy_amount = 0;
$dummy_quantity = 0;
$stmt->bind_param("idis", $cust_id, $dummy_amount, $dummy_quantity, $billing_date);
$stmt->execute();
$billing_id = $stmt->insert_id;
$stmt->close();

// Insert each billing detail (item) along with its GST value
if(isset($_POST['item_name'])){
    $item_names = $_POST['item_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $gst_array = $_POST['gst'];
    for($i = 0; $i < count($item_names); $i++){
        $sql = "INSERT INTO billing_details (billing_id, item_description, quantity, unit_price, gst) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isidd", $billing_id, $item_names[$i], $quantities[$i], $prices[$i], $gst_array[$i]);
        $stmt->execute();
        $stmt->close();
    }
}

// Calculate the total billing amount and total quantity from the inserted items
$totalAmount = 0;
$totalQuantity = 0;
if(isset($_POST['item_name'])){
    for($i = 0; $i < count($_POST['item_name']); $i++){
        $quantity = $_POST['quantity'][$i];
        $price = $_POST['price'][$i];
        $gst = $_POST['gst'][$i];
        $baseAmount = $quantity * $price;
        $gstAmount = $baseAmount * $gst / 100;
        $totalAmount += ($baseAmount + $gstAmount);
        $totalQuantity += $quantity;
    }
}

// Update the billing record with the calculated total amount and total quantity
$sql = "UPDATE billing SET billing_amount = ?, total_quentity = ? WHERE billing_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dii", $totalAmount, $totalQuantity, $billing_id);
$stmt->execute();
$stmt->close();

$conn->close();

// Redirect back to the billing page with the new billing ID to display the invoice modal.
header("Location: add_billing.php?success=1&billing_id=" . $billing_id);
exit;
?> -->



<?php
session_start(); // Added to get the admin id from session

include 'db.php';

// Retrieve the admin id from session
if (!isset($_SESSION['admin_id'])) {
    die("Admin not logged in.");
}
$admin_id = $_SESSION['admin_id'];

// Get customer details from form
$customer_name = $_POST['customer_name'];
$customer_phone = $_POST['customer_phone'];

// Check if customer exists by name
$sql = "SELECT cust_id FROM customers WHERE cust_name = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $customer_name);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    $stmt->bind_result($cust_id);
    $stmt->fetch();
    $stmt->close();
    
    // Optionally update the existing customer record to set the admin_id (if desired)
    $sql = "UPDATE customers SET admin_id = ? WHERE cust_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $admin_id, $cust_id);
    $stmt->execute();
    $stmt->close();
} else {
    // Insert new customer if not exists, including admin_id
    $stmt->close();
    $sql = "INSERT INTO customers (cust_name, cust_phone, admin_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $customer_name, $customer_phone, $admin_id);
    $stmt->execute();
    $cust_id = $stmt->insert_id;
    $stmt->close();
}

// Insert billing record (initial billing_amount and total_quentity set as 0; update later)
$billing_date = date("Y-m-d H:i:s");
$sql = "INSERT INTO billing (cust_id, billing_amount, total_quentity, billing_date, admin_id) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$dummy_amount = 0;
$dummy_quantity = 0;
$stmt->bind_param("idisi", $cust_id, $dummy_amount, $dummy_quantity, $billing_date, $admin_id);
$stmt->execute();
$billing_id = $stmt->insert_id;
$stmt->close();

// Insert each billing detail (item) along with its GST value and admin_id
if(isset($_POST['item_name'])){
    $item_names = $_POST['item_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $gst_array = $_POST['gst'];
    for($i = 0; $i < count($item_names); $i++){
        $sql = "INSERT INTO billing_details (billing_id, item_description, quantity, unit_price, gst, admin_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiddi", $billing_id, $item_names[$i], $quantities[$i], $prices[$i], $gst_array[$i], $admin_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Calculate the total billing amount and total quantity from the inserted items
$totalAmount = 0;
$totalQuantity = 0;
if(isset($_POST['item_name'])){
    for($i = 0; $i < count($_POST['item_name']); $i++){
        $quantity = $_POST['quantity'][$i];
        $price = $_POST['price'][$i];
        $gst = $_POST['gst'][$i];
        $baseAmount = $quantity * $price;
        $gstAmount = $baseAmount * $gst / 100;
        $totalAmount += ($baseAmount + $gstAmount);
        $totalQuantity += $quantity;
    }
}

// Update the billing record with the calculated total amount and total quantity
$sql = "UPDATE billing SET billing_amount = ?, total_quentity = ? WHERE billing_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dii", $totalAmount, $totalQuantity, $billing_id);
$stmt->execute();
$stmt->close();

$conn->close();

// Redirect back to the billing page with the new billing ID to display the invoice modal.
header("Location: add_billing.php?success=1&billing_id=" . $billing_id);
exit;
?>

