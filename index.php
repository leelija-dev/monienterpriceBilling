<!DOCTYPE html>
<html>
<head>
  <title>My Billing System</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">My System</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item">
          <a class="nav-link" href="customer.php">Customer</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="billingDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Billing
          </a>
          <div class="dropdown-menu" aria-labelledby="billingDropdown">
            <a class="dropdown-item" href="billing.php">Billing</a>
            <a class="dropdown-item" href="billing_details.php">Billing Details</a>
          </div>
        </li>
      </ul>
    </div>
  </nav>
  <div class="container mt-4">
    <h1>Welcome to My Billing System</h1>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
