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

// Check if 'id' parameter is present in the URL
if (isset($_GET['id'])) {
  $employeeID = $_GET['id'];

  // Fetch the employee details from the database based on the provided ID
  $query = "SELECT * FROM user_details WHERE ID = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $employeeID);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();

    // Determine the credentials table based on the employee's department
    $department = $employee['department'];
    $passwordQuery = "";

    // Check which table to fetch password from
    if ($department === 'Admin') {
      $passwordQuery = "SELECT password FROM admin_credentials WHERE ID = ?";
    } elseif ($department === 'Department Head' || $department === 'Dean of Studies') {
      $passwordQuery = "SELECT password FROM deptHead_credentials WHERE ID = ?";
    } elseif ($department === 'Elementary' || $department === 'Highschool' || $department === 'College' || $department === 'Non-Faculty') {
      $passwordQuery = "SELECT password FROM employee_credentials WHERE ID = ?";
    }

    // Fetch the password if the department matches one of the roles
    if ($passwordQuery !== "") {
      $stmt = $conn->prepare($passwordQuery);
      $stmt->bind_param("s", $employeeID);
      $stmt->execute();
      $passwordResult = $stmt->get_result();

      if ($passwordResult->num_rows > 0) {
        $passwordRow = $passwordResult->fetch_assoc();
        $employeePassword = $passwordRow['password'];
      } else {
        $employeePassword = "Password not found";
      }
    } else {
      $employeePassword = "Invalid department";
    }

  } else {
    // If no employee found, redirect back to the employee list page
    header("Location: employee_details.php");
    exit();
  }
} else {
  // If no ID is provided in the URL, redirect back to the employee list page
  header("Location: employee_details.php");
  exit();
}

mysqli_close($conn);
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HRIMS User Registration</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
      background-clip: text;
      /* Standard property for compatibility */
      -webkit-text-fill-color: transparent;
      font-size: 2rem;
    }

    .form-container {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-custom {
      background: rgb(128, 0, 0);
      background: linear-gradient(262deg, rgb(139, 0, 0) 0%, rgba(150, 0, 0, 1) 44%, rgba(153, 0, 0, 1) 99%);
      color: white !important;
      border: none;
    }

    .btn-custom:hover {
      background: rgb(153, 0, 0);
      background: linear-gradient(262deg, rgba(153, 0, 0, 1) 0%, rgba(139, 0, 0, 1) 44%, rgba(128, 0, 0, 1) 99%);
      color: #fff !important;
    }

    .signature-container img {
      width: 200px;
      height: auto;
      border: 1px solid #ddd;
      padding: 5px;
    }
  </style>


</head>

<body>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>


      <div class="col p-0">
        <?php include("includes/header.php"); ?>


        <div class="container-fluid page-content mt-3">

          <h1>Edit Profile</h1>

          <!-- Display alert message -->
          <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Your profile has been updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php elseif ($_GET['status'] == 'error'): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> There was an issue updating your profile. Please try again later.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <div class="form-container">
            <form action="includes/update_details.php" method="POST" enctype="multipart/form-data">
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="firstName" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="firstName" name="firstName"
                    value="<?php echo htmlspecialchars($employee['firstName']); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="middleName" class="form-label">Middle Name</label>
                  <input type="text" class="form-control" id="middleName" name="middleName"
                    value="<?php echo htmlspecialchars($employee['middleName']); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="lastName" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="lastName" name="lastName"
                    value="<?php echo htmlspecialchars($employee['lastName']); ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="ID" class="form-label">ID</label>
                  <input type="text" class="form-control" id="ID" name="ID"
                    value="<?php echo htmlspecialchars($employee['ID']); ?>" required readonly>
                </div>
                <div class="col-md-4">
                  <label for="department" class="form-label">Department</label>
                  <select name="department" id="department" class="form-control">
                    <option value="Admin" <?php echo ($employee['department'] === 'Admin') ? 'selected' : ''; ?>>
                      Admin
                    </option>
                    <option value="Department Head" <?php echo ($employee['department'] === 'Department Head') ? 'selected' : ''; ?>>
                      Department Head</option>
                    <option value="Dean of Studies" <?php echo ($employee['department'] === 'Dean of Studies') ? 'selected' : ''; ?>>
                      Dean of Studies
                    </option>
                    <option value="Elementary" <?php echo ($employee['department'] === 'Elementary') ? 'selected' : ''; ?>>
                      Elementary
                    </option>
                    <option value="Highschool" <?php echo ($employee['department'] === 'Highschool') ? 'selected' : ''; ?>>
                      Highschool
                    </option>
                    <option value="College" <?php echo ($employee['department'] === 'College') ? 'selected' : ''; ?>>
                      College
                    </option>
                    <option value="Non-Faculty" <?php echo ($employee['department'] === 'Non-Faculty') ? 'selected' : ''; ?>>
                      Non-Faculty
                    </option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="jobTitle" class="form-label">Job Title</label>
                  <input type="text" class="form-control" id="jobTitle" name="jobTitle"
                    value="<?php echo htmlspecialchars($employee['jobTitle']); ?>" required>
                </div>
              </div>



              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="contactNumber" class="form-label">Contact Number</label>
                  <input type="text" class="form-control" id="contactNumber" name="contactNumber"
                    value="<?php echo htmlspecialchars($employee['contactNumber']); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="address" class="form-label">Address</label>
                  <input type="text" class="form-control" id="address" name="address"
                    value="<?php echo htmlspecialchars($employee['address']); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="birthDate" class="form-label">Birth Date</label>
                  <input type="date" class="form-control" id="birthDate" name="birthDate"
                    value="<?php echo htmlspecialchars($employee['birthDate']); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="age" class="form-label">Age</label>
                  <input type="number" class="form-control" id="age" name="age"
                    value="<?php echo htmlspecialchars($employee['age']); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="birthPlace" class="form-label">Birth Place</label>
                  <input type="text" class="form-control" id="birthPlace" name="birthPlace"
                    value="<?php echo htmlspecialchars($employee['birthPlace']); ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="sex" class="form-label">Gender</label>
                  <select class="form-select" id="sex" name="sex">
                    <option value="Male" <?php echo $employee['sex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $employee['sex'] == 'Female' ? 'selected' : ''; ?>>Female
                    </option>
                    <option value="Other" <?php echo $employee['sex'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="religion" class="form-label">Religion</label>
                  <select class="form-select" id="religion" name="religion" required>
                    <option value="" disabled <?php echo empty($employee['religion']) ? 'selected' : ''; ?>>Select
                      Religion</option>
                    <option value="Roman Catholic" <?php echo $employee['religion'] == 'Roman Catholic' ? 'selected' : ''; ?>>Roman Catholic</option>
                    <option value="Iglesia ni Kristo" <?php echo $employee['religion'] == 'Iglesia ni Kristo' ? 'selected' : ''; ?>>Iglesia ni Kristo</option>
                    <option value="Islam" <?php echo $employee['religion'] == 'Islam' ? 'selected' : ''; ?>>Islam
                    </option>
                    <option value="Seventh-day Adventist Church" <?php echo $employee['religion'] == 'Seventh-day Adventist Church' ? 'selected' : ''; ?>>Seventh-day Adventist Church</option>
                    <option value="Jehovah's Witnesses" <?php echo $employee['religion'] == 'Jehovah\'s Witnesses' ? 'selected' : ''; ?>>Jehovah's Witnesses</option>
                    <option value="Others" <?php echo $employee['religion'] == 'Others' ? 'selected' : ''; ?>>Others
                    </option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="civilStatus" class="form-label">Civil Status</label>
                  <select class="form-select" id="civilStatus" name="civilStatus">
                    <option value="Single" <?php echo $employee['civilStatus'] == 'Single' ? 'selected' : ''; ?>>
                      Single</option>
                    <option value="Married" <?php echo $employee['civilStatus'] == 'Married' ? 'selected' : ''; ?>>
                      Married</option>
                    <option value="Divorced" <?php echo $employee['civilStatus'] == 'Divorced' ? 'selected' : ''; ?>>
                      Divorced</option>
                    <option value="Widowed" <?php echo $employee['civilStatus'] == 'Widowed' ? 'selected' : ''; ?>>
                      Widowed</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">

                <div class="col-md-4">
                  <label for="nationality" class="form-label">Nationality</label>
                  <input type="text" class="form-control" id="nationality" name="nationality"
                    value="<?php echo htmlspecialchars($employee['nationality']); ?>">
                </div>
                <div class="col-md-4">
                  <label for="educationalQualification" class="form-label">Educational Qualification</label>
                  <input type="text" class="form-control" id="educationalQualification" name="educationalQualification"
                    value="<?php echo htmlspecialchars($employee['educationalQualification']); ?>">
                </div>
                <div class="col-md-4">
                  <label for="dateHired" class="form-label">Date Hired</label>
                  <input type="date" class="form-control" id="dateHired" name="dateHired"
                    value="<?php echo htmlspecialchars($employee['dateHired']); ?>" required>
                </div>
              </div>

              <div class="row mb-3">

                <div class="col-md-3">
                  <label for="sssNumber" class="form-label">SSS Number</label>
                  <input type="text" class="form-control" id="sssNumber" name="sssNumber"
                    value="<?php echo htmlspecialchars($employee['sssNumber']); ?>">
                </div>
                <div class="col-md-3">
                  <label for="tinNumber" class="form-label">Tin Number</label>
                  <input type="text" class="form-control" id="tinNumber" name="tinNumber"
                    value="<?php echo htmlspecialchars($employee['tinNumber']); ?>">
                </div>
                <div class="col-md-3">
                  <label for="pagibigNumber" class="form-label">Pagibig Number</label>
                  <input type="text" class="form-control" id="pagibigNumber" name="pagibigNumber"
                    value="<?php echo htmlspecialchars($employee['pagibigNumber']); ?>">
                </div>
                <div class="col-md-3">
                  <label for="philhealthID" class="form-label">Philhealth ID</label>
                  <input type="text" class="form-control" id="philhealthID" name="philhealthID"
                    value="<?php echo htmlspecialchars($employee['philhealthID']); ?>">
                </div>

              </div>

              <div class="row mb-3">
                <!-- Signature Section -->

                <div class="col-md-4">
                  <label for="profileImage" class="form-label">Profile Image</label>
                  <input type="file" class="form-control" id="profileImage" name="profileImage">
                </div>
                <div class="col-md-4">
                  <label for="signature" class="form-label">Signature</label>
                  <input type="file" class="form-control" name="signature" accept="image/*">
                </div>
                <?php if ($employee['signature']): ?>
                  <div class="col-md-4">
                    <label class="form-label">Current Signature</label>
                    <img src="../uploads/<?php echo $employee['signature']; ?>" alt="Current Signature" class="img-fluid"
                      style="max-width: 200px;">
                  </div>
                <?php endif; ?>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="password" class="form-label">Password (Leave blank to keep current password)</label>
                  <input type="password" class="form-control" id="password" name="password">
                </div>
              </div>
              <input type="hidden" name="currentProfileImage"
                value="<?php echo htmlspecialchars($employee['profileImage']); ?>">
              <input type="hidden" name="currentSignature"
                value="<?php echo htmlspecialchars($employee['signature']); ?>">





              <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-custom">Save Changes</button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>





  <script>
  </script>

</body>

</html>