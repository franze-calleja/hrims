<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="assets/css/sidebar-header.css">
<link rel="stylesheet" href="assets/css/page-content.css">



<!-- Sidebar -->
<div class="col-auto p-0 bg-dark" id="sidebar">
    <div class="d-flex flex-column align-items-center align-items-sm-start text-white min-vh-100">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Logo" class="logo">
            <a href="#"
                class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none admin-title">
                <span class="fs-5">Menu</span>
            </a>
        </div>
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
            <li>
                <a href="deptHead_dashboard.php" class="nav-link px-0 align-middle text-white">
                    <h8><i class="fas fa-tachometer-alt icon-size"></i> <span class="ms-1">Dashboard</span></h8>
                </a>
            </li>
            <li>
                <a href="deptHead_profile.php" class="nav-link px-0 align-middle text-white">
                    <h8><i class="fas fa-user-alt icon-size"></i> <span class="ms-1">Profile</span></h8>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link px-0 align-middle text-white" data-bs-toggle="collapse"
                    data-bs-target="#formsSubmenu" aria-expanded="false">
                    <h8>
                        <i class="fas fa-file icon-size"></i>
                        <span class="ms-1">Forms</span>
                        <i class="fas fa-chevron-right arrow-icon ms-auto"></i>
                    </h8>
                </a>
                <div class="collapse" id="formsSubmenu">
                    <ul class="nav flex-column ms-4">
                        <li><a href="leave_pending.php" class="nav-link px-0 text-white"><span
                                    class="ms-1">Leave</span></a></li>
                        <li><a href="travel_order_out_pending.php" class="nav-link px-0 text-white"><span
                                    class="ms-1">Travel Order</span></a></li>
                        <li><a href="travel_order_cande_pending.php" class="nav-link px-0 text-white"><span
                                    class="ms-1">Travel Order Candelaria</span></a></li>
                        <li><a href="make_up_class_pending.php" class="nav-link px-0 text-white"><span
                                    class="ms-1">Make-up Class</span></a></li>
                        <li><a href="log_pending.php" class="nav-link px-0 text-white"><span class="ms-1">Log</span></a>
                        </li>
                    </ul>
                </div>
            </li>

            <li>
                <a href="submit_forms.php" class="nav-link px-0 align-middle text-white">
                    <h8><i class="fas fa-file icon-size"></i> <span class="ms-1">Submit Forms</span></h8>
                </a>
            </li>

            <li>
                <a href="deptHead_certificate.php" class="nav-link px-0 align-middle text-white">
                    <h8><i class="fas fa-certificate icon-size"></i> <span class="ms-1">Certificates</span></h8>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- End Sidebar -->


<script src="assets/js/sidebar.js">


</script>