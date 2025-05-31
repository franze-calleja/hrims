<?php

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Return JSON response for AJAX call
    if (isset($_GET['ajax_logout'])) {
        echo json_encode(['status' => 'not_logged_in']);
        exit;
    }
    // Regular redirect for non-AJAX requests
    header("Location: ../login.php");
    exit;
}

// Handle logout
if (isset($_GET['ajax_logout'])) {
    // Destroy all session variables
    session_unset();
    session_destroy();
    
    // Return success response for AJAX
    echo json_encode(['status' => 'success']);
    exit;
}
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
<link rel="icon" href="../../assets/img/logo.png" type="image/x-icon">
<link rel="stylesheet" href="assets/css/sidebar-header.css">
<link rel="stylesheet" href="assets/css/page-content.css">

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light custom-navbar">
  <div class="container-fluid">
    <button type="button" id="sidebarCollapse" class="btn btn-info">
      <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand" href="admin_profile.php">
      <div class="header-right">
        <a href="#">
          <i class="fas fa-user"></i>
          <span class="account-name"><?php echo $_SESSION['username']; ?></span>
        </a>
        <i class="fas fa-chevron-down"></i>

        <!-- Dropdown Menu -->
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item profile" href="admin_profile.php">Profile</a>
          </li>
          <li>
            <a class="dropdown-item logout" href="#" onclick="confirmLogout()">Log Out</a>
          </li>
        </ul>
      </div>
    </a>
  </div>
</nav>
<!-- End Header -->

<script src="assets/js/header.js"></script>
<script>
function confirmLogout() {
    var confirmation = confirm("Are you sure you want to log out?");
    if (confirmation) {
        // Make an AJAX call to logout
        fetch('?ajax_logout=true')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Redirect to login page
                    window.location.href = '../login.php?logout_success=1';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                // Fallback redirect
                window.location.href = '../login.php';
            });
    }
}

// DOM ready handler
document.addEventListener('DOMContentLoaded', function () {
    const headerRight = document.querySelector('.header-right');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    headerRight.addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior
        dropdownMenu.classList.toggle('show');
    });

    // Close the dropdown when clicking outside
    window.addEventListener('click', function (e) {
        if (!headerRight.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
    });
    
    // Handle dropdown links
    const dropdownLinks = dropdownMenu.querySelectorAll('a');
    dropdownLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent event bubbling
        });
    });
});
</script>
