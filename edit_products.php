<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

//fetch admin details 
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

$product_id = $_GET['id'];
$sql = "SELECT * FROM product WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_info = $stmt->get_result()->fetch_assoc();
$stmt->close();
// print_r($product_info);  die;
if (isset($_POST['product_name'])) {
    // $product = $_POST['product_name'];
    // $design_no = $_POST['design_no'];
    // $mrp = $_POST['mrp'];
    // $gst = $_POST['gst_percentage'];
    // $price = ($mrp * $gst)/100;
    // $product = $_POST['product_name'];

    $product = htmlspecialchars($_POST['product_name']);
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $design_no = htmlspecialchars($_POST['design_no']);
    $mrp = floatval($_POST['mrp']);
    $gst = floatval($_POST['gst_percentage']);
    $admin_id = intval($_POST['admin_id']); // Make sure you have this variable

    // Calculate total price including GST
    $gst_amount = ($mrp * $gst) / 100;
    $total = $mrp + $gst_amount;

    if ($product_id > 0) {
        // Update existing product
        $stmt = $conn->prepare("UPDATE product SET product_name=?, design_no=?, price=?, gst=?, total_price=?, admin_id=? WHERE id=?");
        $stmt->bind_param("ssdiiii", $product, $design_no, $mrp, $gst, $total, $admin_id, $product_id);
    } else {
        // Insert new product
        $stmt = $conn->prepare("INSERT INTO product (product_name, design_no, price, gst, total_price, admin_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiii", $product, $design_no, $mrp, $gst, $total, $admin_id);
    }

    // Execute query and check success
    if ($stmt->execute()) {
        header("Location: products.php"); // Redirect to product list page after success
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();

    //  print_r($_POST); die;
}
// If a billing record was just created, fetch its details for the invoice modal.
// if (isset($_GET['billing_id'])) {
//   $billing_id = $_GET['billing_id'];

//   // Fetch billing info (including customer details, now including customer id)
//   $sql = "SELECT b.billing_date, b.billing_id, c.cust_id, c.cust_name, c.cust_phone FROM billing b 
//             JOIN customers c ON b.cust_id = c.cust_id 
//             WHERE b.billing_id = ?";
//   $stmt = $conn->prepare($sql);
//   $stmt->bind_param("i", $billing_id);
//   $stmt->execute();
//   $result = $stmt->get_result();
//   $billing_info = $result->fetch_assoc();
//   $stmt->close();

//   // Fetch billing items
//   $sql = "SELECT * FROM billing_details WHERE billing_id = ?";
//   $stmt = $conn->prepare($sql);
//   $stmt->bind_param("i", $billing_id);
//   $stmt->execute();
//   $itemsResult = $stmt->get_result();
//   $billing_items = array();
//   while ($row = $itemsResult->fetch_assoc()) {
//     $billing_items[] = $row;
//   }
//   $stmt->close();

//   // Calculate totals
//   $totalBase = 0;
//   $totalGST = 0;
//   foreach ($billing_items as $item) {
//     $itemBase = $item['quantity'] * $item['unit_price'];
//     $itemGST = $itemBase * $item['gst'] / 100;
//     $totalBase += $itemBase;
//     $totalGST += $itemGST;
//   }
//   $totalAmount = $totalBase + $totalGST;
//   $cgst = $totalGST / 2;
//   $sgst = $totalGST / 2;
// }
?>
<!DOCTYPE html>
<html>

<head>
    <title>Billing</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Customer Autocomplete Styles */
        .customer-select-container {
            margin-bottom: 20px;
            position: relative;
        }

        #customerList {
            position: absolute;
            width: 40%;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            background: white;
            box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }

        .customer-item {
            padding: 8px 15px;
            cursor: pointer;
        }

        .customer-item:hover {
            background-color: #f8f9fa;
        }

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

        /* Container for items table for vertical scrolling */
        #tableContainer {
            max-height: 250px;
            /* Adjust height as needed */
            overflow-y: auto;
        }

        /* Print Styles - Only show the invoice modal */
        @media print {
            body * {
                visibility: hidden;
            }

            #invoiceModal #invoiceModal * {
                visibility: visible;
            }

            #invoiceModal {
                position: absolute;
                left: 0;
                top: 0;
                box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
                width: 100%;
            }
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
                <h2>Add Product</h2>
                <hr>
                <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" id="billingForm">
                    <!-- Customer Information -->
                    <div class="row bg-secondary bg-opacity-25 p-4 rounded customer-select-container">
                        <div class="form-group col-md-6">
                            <label for="productName">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="product_name" value="<?php echo $product_info['product_name'] ?>" required>

                            <label for="designNo">Design No</label>
                            <input type="text" class="form-control" id="designNo" name="design_no" value="<?php echo $product_info['design_no'] ?>" required>

                            <label for="mrp">MRP</label>
                            <input type="text" class="form-control" id="mrp" name="mrp" value="<?php echo $product_info['price'] ?>" required>

                            <label for="gstPercentage">GST(%)</label>
                            <input type="text" class="form-control" id="gstPercentage" name="gst_percentage" value="<?php echo $product_info['gst'] ?>" required>

                            <input type="hidden" id="productId" name="product_id" value="<?php echo  $product_info['id'] ?>">
                        </div>

                        <div class="form-group col-md-12 mt-3">
                            <button type="submit" class="btn btn-danger fw-bold" id="submitButton" disabled>Submit</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- jQuery and Bootstrap Bundle JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>



    <script>
        // Enable the submit button when all fields are filled
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("billingForm");
            const inputs = form.querySelectorAll("input[required]");
            const submitButton = document.getElementById("submitButton");

            function checkFormValidity() {
                let allFilled = true;
                inputs.forEach(input => {
                    if (input.value.trim() === "") {
                        allFilled = false;
                    }
                });
                submitButton.disabled = !allFilled;
            }

            inputs.forEach(input => {
                input.addEventListener("input", checkFormValidity);
            });
        });
    </script>
</body>

</html>