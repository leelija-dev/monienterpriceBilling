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

// Fetch all customers for the autocomplete dropdown
$customerQuery = "SELECT cust_id, cust_name, cust_phone FROM customers ORDER BY cust_name";
$customerResult = $conn->query($customerQuery);
$customers = array();
if ($customerResult) {
  while ($row = $customerResult->fetch_assoc()) {
    $customers[] = $row;
  }
}

$itemQuery = "SELECT * FROM product";
$itemResult = $conn->query($itemQuery);
$items = array();
if ($itemResult) {
  while ($row = $itemResult->fetch_assoc()) {
    $items[] = $row;
  }
}

// If a billing record was just created, fetch its details for the invoice modal.
if (isset($_GET['billing_id'])) {
  $billing_id = $_GET['billing_id'];

  // Fetch billing info (including customer details, now including customer id)
  $sql = "SELECT b.billing_date, b.billing_id, c.cust_id, c.cust_name, c.cust_phone FROM billing b 
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
  while ($row = $itemsResult->fetch_assoc()) {
    $billing_items[] = $row;
  }
  $stmt->close();

  // Calculate totals
  $totalBase = 0;
  $totalGST = 0;
  foreach ($billing_items as $item) {
    $itemBase = $item['quantity'] * $item['unit_price'];
    $itemGST = $itemBase * $item['gst'] / 100;
    $totalBase += $itemBase;
    $totalGST += $itemGST;
  }
  $totalAmount = $totalBase + $totalGST;
  $cgst = $totalGST / 2;
  $sgst = $totalGST / 2;
}
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
        <h2>Bill Generate</h2>
        <hr>
        <form action="submit_billing.php" method="post" id="billingForm">
          <!-- Customer Information -->
          <div class="row bg-secondary bg-opacity-25 p-4 rounded customer-select-container">
            <div class="form-group col-md-6">
              <label for="customerName">Customer Name</label>
              <input type="text" class="form-control" id="customerName" name="customer_name" required>
              <div id="customerList"></div>
              <input type="hidden" id="customerId" name="customer_id">
            </div>
            <div class="form-group col-md-6">
              <label for="customerPhone">Customer Phone</label>
              <input type="text" class="form-control" id="customerPhone" name="customer_phone" required>
            </div>
          </div>
          <hr>
          <!-- New Item Input Section -->
          <div class="card mb-3">
            <div class="card-header">Add New Item</div>
            <div class="card-body">
              <div class="row g-2">
                <!-- <div class="col-md-3">
                <input type="text" id="newItemName" class="form-control" placeholder="Item Name">
              </div> -->
                <!-- <div class="col-md-3">
                  <select id="newItemName" class="form-control">
                    <option value="">Select Item</option>

                    < ?php foreach ($items as $item) {  ?>
                      <option value="< ?php echo $item['product_name']; ?>" data-price="< ?php echo $item['price']; ?>" 
                      data-gst="< ?php echo $item['gst']; ?>">< ?php echo $item['product_name'] ?></option>
                    < ?php  } ?>
                  </select>
                </div> -->

                <div class="col-md-3">
                  <input type="text" id="newItemName" class="form-control" placeholder="Type product name..." autocomplete="off">
                  <div id="suggestions" class="list-group position-absolute w-100"></div>
                </div>
                <div class="col-md-2">
                  <input type="number" id="newItemQty" class="form-control" placeholder="Quantity">
                </div>
                <div class="col-md-2">
                  <input type="number" id="newItemPrice" class="form-control" placeholder="Price">
                </div>
                <div class="col-md-2">
                  <input type="number" id="newItemGST" class="form-control" placeholder="GST (%)">
                </div>
                <div class="col-md-3 d-flex justify-content-end">
                  <button type="button" id="addItemToList" class="btn btn-secondary">
                    <i class="fas fa-plus me-2"></i> Add
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!-- Items Summary Table with scrollable container -->
          <h4>Items Added</h4>
          <div id="tableContainer">
            <table class="table" id="itemsTable">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>GST (%)</th>
                  <th>GST Amount</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="itemsList">
                <!-- Added items will appear here -->
              </tbody>
            </table>
          </div>
          <!-- Total Amount Display and Submit Button (disabled by default) -->
          <div class="d-flex justify-content-end align-items-center gap-5 position-fixed bg-secondary text-white p-2" style="bottom: 1%; right: 3%; width: 83%;">
            <div class="fw-bold" id="displayTotalAmount">Total Amount : 0.00</div>
            <button type="submit" class="btn btn-danger fw-bold" id="submitButton" disabled>Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Invoice Modal (displayed if billing_id is set) -->
  <?php if (isset($_GET['billing_id'])): ?>
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content" id="printableArea">
          <div class="modal-header">
            <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
            <!-- Close button now redirects to billing.php -->
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href='billing.php'"></button>
          </div>
          <div class="modal-body shadow m-3 p-4" id="invoiceModal">
            <div class="row mb-3">
              <!-- Company Details -->
              <div class="col-md-6">
                <h6>Company Details</h6>
                <p>
                  <strong><?php echo $admin_info['admin_name']; ?></strong><br>
                  <?php echo $admin_info['email']; ?><br>
                  Phone: <?php echo $admin_info['phone_no']; ?>
                </p>
              </div>
              <!-- Customer Details (including customer id) -->
              <div class="col-md-6 text-end">
                <h6>Customer Details</h6>
                <p>
                  <strong><?php echo $billing_info['cust_name']; ?></strong><br>
                  Billing ID: <?php echo $billing_id; ?><br>
                  Phone: <?php echo $billing_info['cust_phone']; ?><br>
                  Date: <?php echo $billing_info['billing_date']; ?>
                </p>
              </div>
            </div>
            <!-- Items Table -->
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
                <?php foreach ($billing_items as $item):
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
            <!-- Totals Section -->
            <div class="row">
              <div class="col-md-6">
                <!-- Additional notes or terms can go here -->
              </div>
              <div class="col-md-6 text-end">
                <p><strong>Sub Total:</strong> <?php echo number_format($totalBase, 2); ?></p>
                <p><strong>Total GST:</strong> <?php echo number_format($totalGST, 2); ?></p>
                <p><strong>CGST:</strong> <?php echo number_format($cgst, 2); ?></p>
                <p><strong>SGST:</strong> <?php echo number_format($sgst, 2); ?></p>
                <p><strong>Total Amount:</strong> <?php echo number_format($totalAmount, 2); ?></p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <!-- Print Invoice Button -->
            <button type="button" class="btn btn-primary" onclick="printInvoice()">Print Invoice</button>
            <!-- Close Button that redirects to billing.php -->
            <button type="button" class="btn btn-secondary" onclick="window.location.href='billing.php'">Close</button>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- jQuery and Bootstrap Bundle JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script>
    $(document).ready(function() {
      $('#newItemName').change(function() {
        var selectedOption = $(this).find(':selected');
        console.log(selectedOption.data);
        var price = selectedOption.data('price');
        console.log(price);
        var gst = selectedOption.data('gst');

        $('#newItemPrice').val(price);
        $('#newItemGST').val(gst);
      });
    });
  </script> -->

  <script>
    $(document).ready(function() {
      $("#newItemName").keyup(function() {
        let query = $(this).val();
        if (query.length > 1) {
          $.ajax({
            url: "search_product.php",
            method: "POST",
            data: {
              query: query
            },
            success: function(data) {
              $("#suggestions").html(data).fadeIn();
            }
          });
        } else {
          $("#suggestions").fadeOut();
        }
      });

      // Select product from the suggestion list
      $(document).on("click", ".suggestion-item", function() {
        let productName = $(this).data("name");
        let price = $(this).data("price");
        let gst = $(this).data("gst");

        $("#newItemName").val(productName);
        $("#newItemPrice").val(price);
        $("#newItemGST").val(gst);
        $("#suggestions").fadeOut();
      });

      // Hide suggestions when clicking outside
      $(document).click(function(e) {
        if (!$(e.target).closest("#newItemName, #suggestions").length) {
          $("#suggestions").fadeOut();
        }
      });
    });
  </script>
  <script>
    $(document).ready(function() {
      var customers = <?php echo json_encode($customers); ?>;

      // Function to toggle new item inputs based on customer details
      function toggleNewItemInputs() {
        var customerName = $("#customerName").val().trim();
        var customerPhone = $("#customerPhone").val().trim();
        $("#newItemName, #newItemQty, #newItemPrice, #newItemGST, #addItemToList")
          .prop("disabled", (customerName === "" || customerPhone === ""));
      }
      toggleNewItemInputs();
      $("#customerName, #customerPhone").on("input", toggleNewItemInputs);

      // Customer autocomplete functionality
      $("#customerName").on("input", function() {
        var input = $(this).val().toLowerCase();
        var matches = [];
        if (input.length > 0) {
          matches = customers.filter(function(customer) {
            return customer.cust_name.toLowerCase().includes(input);
          });
          if (matches.length > 0) {
            var html = '';
            matches.forEach(function(customer) {
              html += '<div class="customer-item" data-id="' + customer.cust_id +
                '" data-name="' + customer.cust_name +
                '" data-phone="' + customer.cust_phone + '">' +
                customer.cust_name + ' - ' + customer.cust_phone + '</div>';
            });
            $("#customerList").html(html).show();
          } else {
            $("#customerList").hide();
            $("#customerPhone").prop("readonly", false).val("");
            $("#customerId").val("");
          }
        } else {
          $("#customerList").hide();
        }
      });

      // Handle customer selection from the autocomplete list
      $(document).on("click", ".customer-item", function() {
        var id = $(this).data("id");
        var name = $(this).data("name");
        var phone = $(this).data("phone");
        $("#customerId").val(id);
        $("#customerName").val(name);
        $("#customerPhone").val(phone).prop("readonly", false);
        $("#customerList").hide();
        toggleNewItemInputs();
      });

      // Clear customer list when clicking outside
      $(document).on("click", function(e) {
        if (!$(e.target).closest(".customer-select-container").length) {
          $("#customerList").hide();
        }
      });

      // Function to recalc total amount
      function recalcTotal() {
        var total = 0;
        $("#itemsList tr").each(function() {
          var qty = parseFloat($(this).find(".display-qty").text()) || 0;
          var price = parseFloat($(this).find(".display-price").text()) || 0;
          var gstPerc = parseFloat($(this).find(".display-gst").text()) || 0;
          var base = qty * price;
          var gstAmt = base * gstPerc / 100;
          total += base + gstAmt;
        });
        $("#displayTotalAmount").text("Total Amount : " + total.toFixed(2));
      }

      // Function to update the submit button state based on item count
      function updateSubmitButton() {
        if ($("#itemsList tr").length > 0) {
          $("#submitButton").prop("disabled", false);
        } else {
          $("#submitButton").prop("disabled", true);
        }
      }

      // Add new item to the list (table)
      $("#addItemToList").click(function() {
        var name = $("#newItemName").val().trim();
        var qty = $("#newItemQty").val().trim();
        var price = $("#newItemPrice").val().trim();
        var gst = $("#newItemGST").val().trim();
        if (name === "" || qty === "" || price === "" || gst === "") {
          alert("Please fill in all item fields.");
          return;
        }
        qty = parseFloat(qty);
        price = parseFloat(price);
        gst = parseFloat(gst);
        var baseAmount = qty * price;
        var gstAmount = baseAmount * gst / 100;
        var newRow = `
        <tr>
          <td>
            <span class="display-name">${name}</span>
            <input type="hidden" name="item_name[]" value="${name}">
          </td>
          <td>
            <span class="display-qty">${qty}</span>
            <input type="hidden" name="quantity[]" value="${qty}">
          </td>
          <td>
            <span class="display-price">${price.toFixed(2)}</span>
            <input type="hidden" name="price[]" value="${price.toFixed(2)}">
          </td>
          <td>
            <span class="display-gst">${gst}</span>
            <input type="hidden" name="gst[]" value="${gst}">
          </td>
          <td>
            <span class="display-gst-amount">${gstAmount.toFixed(2)}</span>
            <input type="hidden" name="gst_amount[]" value="${gstAmount.toFixed(2)}">
          </td>
          <td>
            <button type="button" class="btn btn-danger remove-item">Remove</button>
          </td>
        </tr>`;
        $("#itemsList").append(newRow);
        recalcTotal();
        updateSubmitButton();
        $("#newItemName").val("");
        $("#newItemQty").val("");
        $("#newItemPrice").val("");
        $("#newItemGST").val("");
      });

      // Remove an item row and recalc total
      $(document).on("click", ".remove-item", function() {
        $(this).closest("tr").remove();
        recalcTotal();
        updateSubmitButton();
      });

      <?php if (isset($_GET['billing_id'])): ?>
        // Automatically show the invoice modal if a billing record exists
        var invoiceModal = new bootstrap.Modal(document.getElementById('invoiceModal'));
        invoiceModal.show();
      <?php endif; ?>
    });

    // Function to print only the invoice modal
    function printInvoice() {
      window.print();
    }
  </script>
</body>

</html>