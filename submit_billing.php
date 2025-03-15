<!-- < ?php
include 'db.php';

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
    // Insert new customer if not exist
    $stmt->close();
    $sql = "INSERT INTO customers (cust_name, cust_phone) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $customer_name, $customer_phone);
    $stmt->execute();
    $cust_id = $stmt->insert_id;
}
$stmt->close();

// Insert billing record (initial billing amount set as 0; update later if needed)
$billing_date = date("Y-m-d H:i:s");
$sql = "INSERT INTO billing (cust_id, billing_amount, billing_date) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$dummy_amount = 0;
$stmt->bind_param("ids", $cust_id, $dummy_amount, $billing_date);
$stmt->execute();
$billing_id = $stmt->insert_id;
$stmt->close();

// Insert each billing detail (item)
if(isset($_POST['item_name'])){
    $item_names = $_POST['item_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $gst_array = $_POST['gst'];
    for($i = 0; $i < count($item_names); $i++){
        $sql = "INSERT INTO billing_details (billing_id, item_description, quantity, unit_price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isid", $billing_id, $item_names[$i], $quantities[$i], $prices[$i]);
        $stmt->execute();
        $stmt->close();
        // Optionally, calculate GST and update the billing total
    }
}

$conn->close();

// Redirect back to the billing page (or to a success page)
header("Location: billing.php?success=1");
exit;
?> -->


<?php
include 'db.php';

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
    // Insert new customer if not exist
    $stmt->close();
    $sql = "INSERT INTO customers (cust_name, cust_phone) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $customer_name, $customer_phone);
    $stmt->execute();
    $cust_id = $stmt->insert_id;
}
$stmt->close();

// Insert billing record (initial billing amount set as 0; update later if needed)
$billing_date = date("Y-m-d H:i:s");
$sql = "INSERT INTO billing (cust_id, billing_amount, billing_date) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$dummy_amount = 0;
$stmt->bind_param("ids", $cust_id, $dummy_amount, $billing_date);
$stmt->execute();
$billing_id = $stmt->insert_id;
$stmt->close();

// Insert each billing detail (item)
// Note: We're storing the GST value along with each item.
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

$conn->close();

// Redirect back to the billing page with the new billing ID to display the invoice modal.
header("Location: billing.php?success=1&billing_id=".$billing_id);
exit;
?>

