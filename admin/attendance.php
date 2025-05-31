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


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="assets/css/admin_profile.css">
  <link rel="stylesheet" href="assets/css/edit_employee.css">
</head>
<style>
  .btn-info{
    width: 48px;
  }


</style>

<body>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>


      <div class="col p-0">
        <?php include("includes/header.php"); ?>


        <div class="container-fluid page-content mt-3">

          <!-- Main Container Area -->
          <div class="main-container">
            <div class="profile-container">
              <!-- Profile Details -->
              <div class="profile-details">
                <div class="profile-image">
                  <img src="../uploads/<?php echo htmlspecialchars($adminDetails['profileImage']); ?>"
                    alt="Profile Image">
                </div>
                <div class="profile-info">
                  <div class="name" id="profile-name">
                    <?php echo htmlspecialchars($adminDetails['firstName'] . ' ' . $adminDetails['lastName']); ?>
                  </div>
                  <div class="info-item" id="profile-employee-id"><strong>ID:
                    </strong><?php echo htmlspecialchars($adminDetails['ID']); ?></div>
                  <div class="info-item" id="profile-department"><strong>Department: </strong>
                    <?php echo htmlspecialchars($adminDetails['department']); ?></div>
                  <div class="info-item" id="profile-job-title"><strong>Job Title: </strong>
                    <?php echo htmlspecialchars($adminDetails['jobTitle']); ?></div>
                  <div class="info-item" id="profile-contact"><strong>Contact:
                    </strong><?php echo htmlspecialchars($adminDetails['contactNumber']); ?></div>
                </div>
              </div>

              <!-- Separation Line -->
              <div class="separator"></div>

              <!-- Other Information -->
              <div class="other-info">
                <h3>Other Information</h3>
                <div class="info-row">
                  <div class="info-column">
                    <div class="info-item" id="profile-age"><strong>Age:
                      </strong><?php echo htmlspecialchars($adminDetails['age']); ?></div>
                    <div class="info-item" id="profile-gender"><strong>Gender:
                      </strong><?php echo htmlspecialchars($adminDetails['sex']); ?></div>
                    <div class="info-item" id="profile-birthdate"><strong>Birthdate:
                      </strong><?php echo htmlspecialchars($adminDetails['birthDate']); ?></div>
                    <div class="info-item" id="profile-birthplace"><strong>Birthplace:
                      </strong><?php echo htmlspecialchars($adminDetails['birthPlace']); ?></div>
                  </div>
                  <div class="info-column">
                    <div class="info-item" id="profile-address"><strong>Address:
                      </strong><?php echo htmlspecialchars($adminDetails['address']); ?></div>
                    <div class="info-item" id="profile-nationality"><strong>Nationality: </strong>
                      <?php echo htmlspecialchars($adminDetails['nationality']); ?></div>
                    <div class="info-item" id="profile-marital-status"><strong>Civil Status: </strong>
                      <?php echo htmlspecialchars($adminDetails['civilStatus']); ?></div>
                    <div class="info-item" id="profile-email"><strong>Email:
                      </strong><?php echo htmlspecialchars($adminDetails['email']); ?>/div>

                    </div>
                  </div>
                </div>

                <!-- Edit Button -->
                <button class="edit-button" onclick="openEditModal()">
                  <i class="fas fa-edit"></i> Edit
                </button>
              </div>
            </div>
          </div>
          <!-- End Main Container -->

        </div>
      </div>
    </div>
  </div>




  <div id="editProfileModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeEditModal()">&times;</span>
      <form action="includes/update_profile.php" method="POST" enctype="multipart/form-data">

        <div class="input-group">
          <div>
            <label for="ID">Admin ID</label>
            <input type="text" name="ID" id="ID" value="<?php echo htmlspecialchars($adminDetails['ID']); ?>" readonly>
          </div>
          <div>
            <label for="lastName">Last Name</label>
            <input type="text" name="lastName" id="lastName"
              value="<?php echo htmlspecialchars($adminDetails['lastName']); ?>">
          </div>
          <div>
            <label for="firstName">First Name</label>
            <input type="text" name="firstName" id="firstName"
              value="<?php echo htmlspecialchars($adminDetails['firstName']); ?>">
          </div>
          <div>
            <label for="middleName">Middle Name</label>
            <input type="text" id="middleName" name="middleName" placeholder="Middle Name"
              value="<?php echo htmlspecialchars($adminDetails['middleName']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="birthDate">Birthdate</label>
            <input type="date" name="birthDate" id="birthDate"
              value="<?php echo htmlspecialchars($adminDetails['birthDate']); ?>">
          </div>
          <div>
            <label for="birthPlace">Birthplace</label>
            <input type="text" name="birthPlace" id="birthPlace"
              value="<?php echo htmlspecialchars($adminDetails['birthPlace']); ?>">
          </div>
          <div>
            <label for="age">Age</label>
            <input type="text" name="age" id="age" value="<?php echo htmlspecialchars($adminDetails['age']); ?>">
          </div>
          <div>
            <label for="sex">Gender</label>
            <input type="text" name="sex" id="sex" value="<?php echo htmlspecialchars($adminDetails['sex']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="department">Department</label>
            <input type="text" name="department" id="department"
              value="<?php echo htmlspecialchars($adminDetails['department']); ?>">
          </div>
          <div>
            <label for="jobTitle">Job Title</label>
            <input type="text" name="jobTitle" id="jobTitle"
              value="<?php echo htmlspecialchars($adminDetails['jobTitle']); ?>">
          </div>
          <div>
            <label for="nationality">Nationality</label>
            <input type="text" name="nationality" id="nationality"
              value="<?php echo htmlspecialchars($adminDetails['nationality']); ?>">
          </div>
          <div>
            <label for="civilStatus">Civil Status</label>
            <input type="text" name="civilStatus" id="civilStatus"
              value="<?php echo htmlspecialchars($adminDetails['civilStatus']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="contactNumber">Contact</label>
            <input type="text" name="contactNumber" id="contactNumber"
              value="<?php echo htmlspecialchars($adminDetails['contactNumber']); ?>">
          </div>
          <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($adminDetails['email']); ?>">
          </div>
          <div>
            <label for="address">Address</label>
            <input type="text" name="address" id="address"
              value="<?php echo htmlspecialchars($adminDetails['address']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="profileImage">Profile Image</label>
            <div class="custom-file-input">
              <label for="profileImage">Profile Image</label>
              <input type="file" name="profileImage" id="profileImage">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <!-- Submit Button -->
          <button type="submit" class="btn">Save Changes</button>
        </div>
        <div id="error" class="error"></div>
      </form>
    </div>
  </div>


  <script>
    function openEditModal() {
      document.getElementById("editProfileModal").style.display = "block";
    }

    function closeEditModal() {
      document.getElementById("editProfileModal").style.display = "none";
    }

    // Close modal if the user clicks outside the modal content
    window.onclick = function (event) {
      var modal = document.getElementById("editProfileModal");
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }

  </script>




</body>

</html>