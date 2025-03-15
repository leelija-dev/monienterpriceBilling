<?php
// billing.php - Billing form page
// Update these credentials as needed.
$host = "localhost";
$dbname = "trueacc_billing";
$user = "root";
$pass = "";

// Create connection (using mysqli)
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Billing</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .item-row { margin-bottom: 15px; }
    /* Position the auto-complete dropdown on top of other elements */
    #customerList {
      position: absolute;
      z-index: 1000;
      width: 100%;
    }
  </style>
</head>
<body>
<div class="container mt-4">
  <h2>Billing</h2>
  <form action="submit_billing.php" method="post" id="billingForm">
    <!-- Customer Information -->
    <div class="form-group position-relative">
      <label for="customerName">Customer Name</label>
      <input type="text" class="form-control" id="customerName" name="customer_name" autocomplete="off" placeholder="Enter customer name">
      <!-- Auto-complete suggestions will be appended here -->
      <div id="customerList" class="list-group"></div>
    </div>
    <div class="form-group">
      <label for="customerPhone">Customer Phone</label>
      <!-- By default, this field is editable. It will be filled & set to readonly if an existing customer is selected -->
      <input type="text" class="form-control" id="customerPhone" name="customer_phone" placeholder="Enter phone if new, or auto-filled if exists">
    </div>
    <hr>
    <!-- Billing Items -->
    <h4>Items</h4>

    <button type="button" id="addItemBtn" class="btn btn-secondary mb-3">Add Item</button>
    <br>
    <div id="itemsContainer">
      <!-- Dynamic item rows will be appended here -->
    </div>
    <div class="col-12 d-flex justify-content-end">
        <div class="col-md-2 d-flex jusitfy-content-end">
                <input type="text" name="total[]" class="form-control item-total" placeholder="Total Amount" readonly>
        </div>
    </div>    
    <button type="submit" class="btn btn-primary">Submit Billing</button>
  </form>
</div>

<!-- jQuery for AJAX and dynamic item addition -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function(){
    // Auto-complete for customer name
    $("#customerName").on("keyup", function(){
        var query = $(this).val();
        if(query.length > 1){
            $.ajax({
                url: "fetch_customer.php",
                method: "POST",
                data: {query: query},
                success: function(data){
                    $("#customerList").fadeIn();
                    $("#customerList").html(data);
                }
            });
        } else {
            $("#customerList").fadeOut();
            $("#customerList").html("");
            // Clear phone and make it editable when no customer name is provided
            $("#customerPhone").val("");
            $("#customerPhone").prop("readonly", false);
        }
    });

    // When a customer is selected from the auto-complete suggestions
    $(document).on("click", ".customer-item", function(e){
        e.preventDefault();
        var name = $(this).data("name") || "";
        var phone = $(this).data("phone") || "";
        $("#customerName").val(name);
        // If a phone is provided, fill and lock the field; otherwise clear and allow manual input.
        if(phone !== "") {
            $("#customerPhone").val(phone);
            $("#customerPhone").prop("readonly", true);
        } else {
            $("#customerPhone").val("");
            $("#customerPhone").prop("readonly", false);
        }
        $("#customerList").fadeOut();
    });

    // Add item dynamically with computed GST and total fields
    $("#addItemBtn").click(function(){
        var itemHtml = `
        <div class="item-row row">
            <div class="col-md-3">
                <input type="text" name="item_name[]" class="form-control" placeholder="Item Name" required>
            </div>
            <div class="col-md-1">
                <input type="number" name="quantity[]" class="form-control item-quantity" placeholder="Qty" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="price[]" class="form-control item-price" placeholder="Price" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="gst[]" class="form-control item-gst" placeholder="GST%" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="gst_amount[]" class="form-control item-gst-amount" placeholder="GST Amount" readonly>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-item">Remove</button>
            </div>
        </div>
        </div>`;
        $("#itemsContainer").append(itemHtml);
    });

    // Remove an item row
    $(document).on("click", ".remove-item", function(){
        $(this).closest(".item-row").remove();
    });

    // Calculate GST and total amount for each item row when quantity, price, or GST changes
    $(document).on("input", ".item-quantity, .item-price, .item-gst", function(){
        var row = $(this).closest(".item-row");
        var quantity = parseFloat(row.find(".item-quantity").val()) || 0;
        var price = parseFloat(row.find(".item-price").val()) || 0;
        var gstPercent = parseFloat(row.find(".item-gst").val()) || 0;
        var baseAmount = quantity * price;
        var gstAmount = baseAmount * gstPercent / 100;
        var totalAmount = baseAmount + gstAmount;
        row.find(".item-gst-amount").val(gstAmount.toFixed(2));
        row.find(".item-total").val(totalAmount.toFixed(2));
    });
});
</script>
</body>
</html>
