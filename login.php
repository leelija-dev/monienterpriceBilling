<?php
session_start();

// If user is already logged in, redirect to index.php
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';  // Contains your database connection code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM customers WHERE cust_email='$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // if (password_verify($password, $user['cust_password'])) {
            if ($password == $user['cust_password']) {
            // Set session variables
            $_SESSION['customer_id'] = $user['cust_id'];
            $_SESSION['customer_name'] = $user['cust_name'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="border rounded p-4" style="width:30%;box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
  <h2 class="text-center mb-4">Customer Login</h2>
  <?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="POST">
      <div class="mb-3">
          <label for="email" class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
          <label for="password" class="form-label fw-bold">Password</label>
          <input type="password" name="password" class="form-control" required>
      </div>
      <div class=" d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Login</button>
      </div>
  </form>
  <p class="mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
  </div>
</body>
</html>
