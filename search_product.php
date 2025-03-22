<?php
include 'db.php';

if (isset($_POST['query'])) {
    $search = "%" . $_POST['query'] . "%";
    $stmt = $conn->prepare("SELECT product_name, price, gst FROM product WHERE product_name LIKE ? LIMIT 5");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<a href='#' class='list-group-item list-group-item-action suggestion-item' 
                  data-name='" . htmlspecialchars($row['product_name']) . "' 
                  data-price='" . htmlspecialchars($row['price']) . "' 
                  data-gst='" . htmlspecialchars($row['gst']) . "'>
                  " . htmlspecialchars($row['product_name']) . "
                  </a>";
        }
    } else {
        echo "<div class='list-group-item'>No products found</div>";
    }
}
?>
