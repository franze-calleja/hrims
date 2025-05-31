<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
  // Redirect to login page if not logged in
  header("Location: ../login.php");
  exit;
}

// Include the database connection file
include("../includes/database.php");

// Get user details from session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch admin details from the database
if ($role === 'deptHead' || $role === 'dean') {
  // Prepare and execute the query
  $sql = "SELECT * FROM user_details WHERE ID = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username); // Assuming 'adminID' is the username
  $stmt->execute();
  $result = $stmt->get_result();

  // Fetch the details
  if ($result->num_rows > 0) {
    $deptHeadDetails = $result->fetch_assoc();
  } else {
    $deptHeadDetails = null; // No details found
  }
  $stmt->close();
} else {
  $deptHeadDetails = null;
}

// Count employee certificates
$sql = "SELECT COUNT(*) as cert_count FROM employee_certificates WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$certResult = $stmt->get_result();
$certCount = $certResult->fetch_assoc()['cert_count'];
$stmt->close();

// Count pending leave submissions
$sql = "SELECT COUNT(*) as leave_count FROM leave_forms WHERE employee_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$leaveResult = $stmt->get_result();
$pendingLeaveCount = $leaveResult->fetch_assoc()['leave_count'];
$stmt->close();

$conn->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">

</head>

<style>
    /* Announcements List */
.announcement-container {
  width: 98%;
  height: auto;
  min-height: 440px;
  padding: 0.5rem;
  background: #FFFF;
  border-radius: 1rem;
  margin: 1.25rem auto;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.443);
  display: flex;
  flex-direction: column;
  box-sizing: border-box;

}
.announcement-container p{
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 1.25rem;
  text-align: left;
  color: maroon;
}
</style>

<body>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>


      <div class="col p-0">
        <?php include("includes/header.php"); ?>


        <div class="container-fluid page-content mt-3">
          <!-- Main Container -->
          <div class="main-container">

            <div class="container-fluid">

              <!-- Content Area -->
              <div class="content">
                <!-- Container for Rectangle and Squares -->
                <div style="display: flex; flex-wrap: wrap;">

                  <!-- Existing Rectangle and Squares -->
                  <div class="profile-dashboard">
                    <img src="../uploads/<?php echo htmlspecialchars($deptHeadDetails['profileImage']); ?>"
                      alt="Profile Image" class="profile-image">
                    <div class="profile-info">
                      <div class="greeting">Good Day,</div>
                      <div><span
                          class="name"><?php echo htmlspecialchars($deptHeadDetails['firstName'] . ' ' . $deptHeadDetails['lastName']); ?></span>
                      </div>
                      <div class="date"><?php echo date('F j, Y'); ?></div>
                      <a href="deptHead_profile.php" class="btn btn-danger edit-button">
                        <i class="fas fa-pencil-alt"></i> Edit Profile
                      </a>
                    </div>
                  </div>
                  <div class="leave-details">
                    <div class="title">
                      Pending Leave <br> Submissions
                      <i class="fas fa-calendar-alt"></i>
                    </div>
                    <a href="submit_forms.php" class="view-link">Click here to view</a>
                    <div class="line"></div>
                    <div class="number"><?php echo htmlspecialchars($pendingLeaveCount); ?></div>
                  </div>

                  <div class="employee-details">
                    <div class="title">
                      Certificate <br> Locker
                      <i class="fas fa-certificate"></i>
                    </div>
                    <a href="deptHead_certificate.php" class="view-link">Click here to view</a>
                    <div class="line"></div>
                    <div class="number"><?php echo htmlspecialchars($certCount); ?></div>
                  </div>


                   <!-- New Rectangles -->
                   <div class="announcement-container">
                    <p>Announcements</p>
                    <ul class="announcement-list">
                      <li>
                        <strong>July 10, 2024:</strong> University will be closed on July 21st for a public holiday.
                      </li>
                      <li>
                        <strong>July 12, 2024:</strong> New health protocols have been implemented. Please check your
                        email for
                        details.
                      </li>
                      <li>
                        <strong>July 15, 2024:</strong> Faculty meeting scheduled for July 20th at 10:00 AM in Room 204.
                      </li>
                      <li>
                        <strong>July 18, 2024:</strong> Please submit your leave requests for the upcoming semester by
                        July
                        25th.
                      </li>
                    </ul>
                  </div>



                </div>
              </div>
              <!-- End Content Area -->

            </div>





          </div>
          <!-- End Main Container -->


        </div>
      </div>
    </div>
  </div>

</body>

</html>