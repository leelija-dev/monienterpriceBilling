<div class="sidebar pe-1">
  <a class="navbar-brand h4 fw-bold" href="index.php">Billing System</a>
  <hr>
  <ul class="nav flex-column mt-5 gap-2">
    <li class="nav-item bg-secondary bg-opacity-25 px-1 rounded">
      <a class="nav-link fw-bold" href="fetch_customer.php">Customer</a>
    </li>
    <li class="nav-item bg-secondary bg-opacity-25 px-1 rounded">
      <!-- Toggle for Billing submenu -->
      <a class="nav-link d-flex justify-content-between" data-bs-toggle="collapse" href="#billingSubmenu" role="button" aria-expanded="false" aria-controls="billingSubmenu">
        <div class="fw-bold">Billing</div> 
        <div><i class="fas fa-chevron-down"></i></div>
      </a>
      <!-- Collapsible submenu -->
      <div class="collapse" id="billingSubmenu">
        <ul class="nav flex-column ms-3">
          <li class="nav-item">
            <a class="nav-link" href="billing.php">Billing</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="billing_details.php">Billing Details</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item bg-secondary bg-opacity-25 px-1 rounded">
      <a class="nav-link fw-bold" href="products.php">Products</a>
    </li>
  </ul>
</div>
