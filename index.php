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
  <title>My Billing System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
    <div class="w-100">
      <nav class="navbar navbar-light bg-light w-100">
        <div class="container-fluid">
          <span class="navbar-text fw-bold">
            Welcome, <?php echo $_SESSION['customer_name']; ?>
          </span>
          <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        </div>
      </nav>
      <div class="content">
        <h1>Welcome to My Billing System</h1>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
