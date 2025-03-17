<?php
  session_start();
  if (!isset($_SESSION['customer_id'])) {
      header("Location: login.php");
      exit();
  }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- <link rel="stylesheet" href="sidebar/sidebar.css"> -->
    <style>
    /* Your sidebar and content styles here */
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
  </style>
</head>
<body>
<div class="d-flex w-100">
<?php include 'sidebar/sidebar.php'; ?>
<div class=" w-100">
<nav class="navbar navbar-light bg-light w-100" >
<div class="container-fluid">
          <span class="navbar-text fw-bold">
            Welcome, <?php echo $_SESSION['customer_name']; ?>
          </span>
          <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        </div>
</nav>

    <div class="container mt-4">
        <h2>Customer Details</h2>
        
        <?php
        include 'db.php'; 

        $sql = "SELECT * FROM customers ORDER BY cust_id";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped table-bordered'>";
            echo "<thead class='thead-dark'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <!-- <th>Email</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Pincode</th> -->
                  </tr>";
            echo "</thead><tbody>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['cust_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cust_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cust_phone']) . "</td>";
                // echo "<td>" . htmlspecialchars($row['cust_email']) . "</td>";
                // echo "<td>" . htmlspecialchars($row['cust_address']) . "</td>";
                // echo "<td>" . htmlspecialchars($row['cust_city']) . "</td>";
                // echo "<td>" . htmlspecialchars($row['cust_state']) . "</td>";
                // echo "<td>" . htmlspecialchars($row['cust_pincode']) . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-info'>No customers found in the database.</div>";
        }

        $conn->close();
        ?>
    </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
