<?php
// fetch_customer.php - Returns matching customer details in table form.
$host = "localhost";
$dbname = "trueacc_billing";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['query'])){
    $query = $conn->real_escape_string($_POST['query']);
    $sql = "SELECT cust_id, cust_name, cust_phone FROM customers WHERE cust_name LIKE '%$query%' LIMIT 5";
    $result = $conn->query($sql);
    if($result->num_rows > 0){
        echo "<table class='table table-striped table-bordered'>";
        echo "<thead><tr><th>ID</th><th>Name</th><th>Phone</th></tr></thead><tbody>";
        while($row = $result->fetch_assoc()){
            echo "<tr class='customer-item' style='cursor:pointer;' data-name='".htmlspecialchars($row['cust_name'])."' data-phone='".htmlspecialchars($row['cust_phone'])."'>";
            echo "<td>" . htmlspecialchars($row['cust_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cust_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cust_phone']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "No customer found. Please add new.";
    }
}
$conn->close();
?>
