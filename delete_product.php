<?php
include 'db_connection.php'; // Make sure to include your database connection file

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    // Prepare SQL statement
    $stmt = $conn->prepare("DELETE FROM product WHERE id = ?");
    $stmt->bind_param("i", $product_id);

    // Execute query and check success
    if ($stmt->execute()) {
        header("Location: product.php?msg=deleted"); // Redirect to product list after success
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "Invalid request!";
}
?>
