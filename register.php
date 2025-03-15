<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = $conn->real_escape_string($_POST['name']);
    $phone   = $conn->real_escape_string($_POST['phone']);
    $email   = $conn->real_escape_string($_POST['email']);
    $password= $conn->real_escape_string($_POST['password']);
    // $password= password_hash($_POST['password'], PASSWORD_BCRYPT);


    $sql = "INSERT INTO customers (cust_name, cust_phone, cust_email, cust_password) 
            VALUES ('$name', '$phone', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center" style="height:100vh;">
<div class="border rounded p-4" style="width:30%;box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
  <h2 class="text-center mb-4">Customer Registration</h2>
  <?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="POST">
      <div class="mb-3">
          <label for="name" class="form-label fw-bold">Full Name</label>
          <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
          <label for="phone" class="form-label fw-bold">Phone Number</label>
          <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="mb-3">
          <label for="email" class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
          <label for="password" class="form-label fw-bold">Password</label>
          <input type="password" name="password" class="form-control" required>
      </div>
      <div class=" d-flex justify-content-end">
      <button type="submit" class="btn btn-primary">Register</button>
      </div>
  </form>
  <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
  </div>
</body>
</html>
