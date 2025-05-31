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
if ($role === 'admin') {
  // Prepare and execute the query
  $sql = "SELECT * FROM user_details WHERE ID = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username); // Assuming 'adminID' is the username
  $stmt->execute();
  $result = $stmt->get_result();

  // Fetch the details
  if ($result->num_rows > 0) {
    $adminDetails = $result->fetch_assoc();
  } else {
    $adminDetails = null; // No details found
  }
  $stmt->close();
} else {
  $adminDetails = null;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
  .page-content {
    background-color: #ececec;
    max-height: calc(100vh - 56px);
    overflow-y: auto;
    padding: 20px;
    height: 100vh;
  }

  .page-content h1 {
    background: rgb(128, 0, 0);
    background: linear-gradient(to right, rgb(128, 0, 0) 0%, rgb(128, 0, 0) 100%);
    -webkit-background-clip: text;
    /* For WebKit-based browsers (Safari, Chrome, etc.) */
    background-clip: text;
    /* Standard property for compatibility */
    -webkit-text-fill-color: transparent;
    /* For WebKit-based browsers */
    font-size: 2rem;
  }


  .profile-header {
    background-color: #ffffff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .profile-header .profile-image {
    border-radius: 12px;
    width: 92%;
    height: 82%;
    object-fit: cover;
    border: 3px solid #ddd;
  }

  .doctor-info {
    padding-left: 20px;
  }

  .doctor-info h3 {
    font-size: 1.8rem;
  }

  .doctor-info p {
    font-size: 1rem;
    margin: 5px 0;
  }

  .about-container {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .about-container h3 {
    color: rgb(128, 0, 0);
  }

  .d-flex.justify-content-end {
    margin-top: 20px;
  }

  .btn-custom {
    background: rgb(128, 0, 0);
    /* Maroon color */
    background: linear-gradient(262deg, rgb(139, 0, 0) 0%, rgba(150, 0, 0, 1) 44%, rgba(153, 0, 0, 1) 99%);
    color: white !important;
    border: none;
  }

  .btn-custom:hover {
    background: rgb(153, 0, 0);
    /* Darker maroon for hover effect */
    background: linear-gradient(262deg, rgba(153, 0, 0, 1) 0%, rgba(139, 0, 0, 1) 44%, rgba(128, 0, 0, 1) 99%);
    color: #fff !important;
  }
</style>

<body>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>


      <div class="col p-0">
        <?php include("includes/header.php"); ?>


        <div class="container-fluid page-content mt-3">

          <h1>Your Profile</h1>

          <div class="profile-header mb-4">
            <div class="row">
              <div class="col-md-2 text-center">
                <!-- Display profile image if exists, otherwise default image -->
                <img
                  src="../uploads/<?php echo htmlspecialchars($adminDetails['profileImage']) ? htmlspecialchars($adminDetails['profileImage']) : 'default-profile.jpg'; ?>"
                  alt="Profile Image" class="profile-image">
              </div>
              <div class="doctor-info col-md-9 text-center text-md-start">
                <!-- Display dynamic user details -->
                <h3>
                  <?php echo htmlspecialchars($adminDetails['firstName']) . ' ' . htmlspecialchars($adminDetails['lastName']); ?>
                </h3>
                <p><strong>ID:</strong> <?php echo htmlspecialchars($adminDetails['ID']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($adminDetails['department']); ?></p>
                <p><strong>Job Title:</strong> <?php echo htmlspecialchars($adminDetails['jobTitle']); ?></p>
                <p><strong>Contact Number:</strong>
                  <?php echo htmlspecialchars($adminDetails['contactNumber']) ? htmlspecialchars($adminDetails['contactNumber']) : 'Not Provided'; ?>
                </p>
              </div>
            </div>
          </div>

          <div class="about-container mb-4">
            <h3>Other Information</h3>
            <div class="row">
              <div class="col-md-6">
                <p><strong>Address:</strong>
                  <?php echo htmlspecialchars($adminDetails['address']) ? htmlspecialchars($adminDetails['address']) : 'Not Provided'; ?>
                </p>
                <p><strong>Birthdate:</strong> <?php echo date("F j, Y", strtotime($adminDetails['birthDate'])); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($adminDetails['sex']); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($adminDetails['age']); ?></p>
                <p><strong>Religion:</strong>
                  <?php echo htmlspecialchars($adminDetails['religion']) ? htmlspecialchars($adminDetails['religion']) : 'Not Provided'; ?>
                </p>
                <p><strong>Email:</strong>
                  <?php echo htmlspecialchars($adminDetails['email']) ? htmlspecialchars($adminDetails['email']) : 'Not Provided'; ?>
                </p>
              </div>

              <div class="col-md-6">
                <p><strong>Civil Status:</strong>
                  <?php echo htmlspecialchars($adminDetails['civilStatus']) ? htmlspecialchars($adminDetails['civilStatus']) : 'Not Provided'; ?>
                </p>
                <p><strong>Nationality:</strong>
                  <?php echo htmlspecialchars($adminDetails['nationality']) ? htmlspecialchars($adminDetails['nationality']) : 'Not Provided'; ?>
                </p>
                <p><strong>SSS Number:</strong>
                  <?php echo htmlspecialchars($adminDetails['sssNumber']) ? htmlspecialchars($adminDetails['sssNumber']) : 'Not Provided'; ?>
                </p>
                <p><strong>TIN Number:</strong>
                  <?php echo htmlspecialchars($adminDetails['tinNumber']) ? htmlspecialchars($adminDetails['tinNumber']) : 'Not Provided'; ?>
                </p>
                <p><strong>PAGIBIG Number:</strong>
                  <?php echo htmlspecialchars($adminDetails['pagibigNumber']) ? htmlspecialchars($adminDetails['pagibigNumber']) : 'Not Provided'; ?>
                </p>
                <p><strong>PhilHealth ID:</strong>
                  <?php echo htmlspecialchars($adminDetails['philhealthID']) ? htmlspecialchars($adminDetails['philhealthID']) : 'Not Provided'; ?>
                </p>
                <p><strong>Date Hired:</strong>
                  <?php echo htmlspecialchars($adminDetails['dateHired']) ? date("F j, Y", strtotime($adminDetails['dateHired'])) : 'Not Provided'; ?>
                </p>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-3">
            <a href="edit_profile.php" class="btn btn-custom">Edit Profile</a>
          </div>
        </div>
        <!-- End Main Container -->

      </div>
    </div>
  </div>

</body>

</html>