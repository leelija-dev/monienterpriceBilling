<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$customer_id = $_SESSION['customer_id'];

$sql = "SELECT * FROM admin WHERE cust_id = $customer_id";
$result = $conn->query($sql);
$customer = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $customer['cust_name']; ?></h2>
    <p>Email: <?php echo $customer['cust_email']; ?></p>
    <p>Phone: <?php echo $customer['cust_phone']; ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>
