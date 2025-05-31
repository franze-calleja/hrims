<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("includes/database.php");

$alertMessages = []; // Initialize an array to store alert messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize form inputs
  $ID = filter_input(INPUT_POST, "ID", FILTER_SANITIZE_SPECIAL_CHARS);
  $lastName = filter_input(INPUT_POST, "lastName", FILTER_SANITIZE_SPECIAL_CHARS);
  $firstName = filter_input(INPUT_POST, "firstName", FILTER_SANITIZE_SPECIAL_CHARS);

  // Validate against employee_validation table
  $validateSql = "SELECT * FROM employee_validation 
                    WHERE ID = ? 
                    AND firstName = ? 
                    AND lastName = ? 
                    AND isRegistered = FALSE";

  $stmt = $conn->prepare($validateSql);
  $stmt->bind_param("sss", $ID, $firstName, $lastName);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
    // If no matching record or the employee is already registered, show an error and stop further execution
    $alertMessages[] = 'Error: Invalid employee credentials or already registered. Please contact your administrator.';
  } else {
    // Credentials are valid, continue with the rest of the registration logic

    // Continue with other form inputs
    $middleName = filter_input(INPUT_POST, "middleName", FILTER_SANITIZE_SPECIAL_CHARS);
    $suffix = filter_input(INPUT_POST, "suffix", FILTER_SANITIZE_SPECIAL_CHARS);
    $birthDate = filter_input(INPUT_POST, "birthDate", FILTER_SANITIZE_SPECIAL_CHARS);
    $birthPlace = filter_input(INPUT_POST, "birthPlace", FILTER_SANITIZE_SPECIAL_CHARS);
    $age = filter_input(INPUT_POST, "age", FILTER_SANITIZE_SPECIAL_CHARS);
    $sex = filter_input(INPUT_POST, "sex", FILTER_SANITIZE_SPECIAL_CHARS);
    $department = filter_input(INPUT_POST, "department", FILTER_SANITIZE_SPECIAL_CHARS);
    $jobTitle = filter_input(INPUT_POST, "jobTitle", FILTER_SANITIZE_SPECIAL_CHARS);
    $contactNumber = filter_input(INPUT_POST, "contactNumber", FILTER_SANITIZE_SPECIAL_CHARS);
    $civilStatus = filter_input(INPUT_POST, "civilStatus", FILTER_SANITIZE_SPECIAL_CHARS);
    $address = filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
    $nationality = filter_input(INPUT_POST, "nationality", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "eMail", FILTER_SANITIZE_EMAIL);

    // New fields
    $educationalQualification = filter_input(INPUT_POST, "educationalQualification", FILTER_SANITIZE_SPECIAL_CHARS);
    $religion = filter_input(INPUT_POST, "religion", FILTER_SANITIZE_SPECIAL_CHARS);
    $sssNumber = filter_input(INPUT_POST, "sssNumber", FILTER_SANITIZE_SPECIAL_CHARS);
    $tinNumber = filter_input(INPUT_POST, "tinNumber", FILTER_SANITIZE_SPECIAL_CHARS);
    $pagibigNumber = filter_input(INPUT_POST, "pagibigNumber", FILTER_SANITIZE_SPECIAL_CHARS);
    $philhealthID = filter_input(INPUT_POST, "philhealthID", FILTER_SANITIZE_SPECIAL_CHARS);
    $dateHired = filter_input(INPUT_POST, "dateHired", FILTER_SANITIZE_SPECIAL_CHARS);

    // Profile image upload
    $image = $_FILES['profileImage']['name'];
    $tmpName = $_FILES['profileImage']['tmp_name'];
    $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    $allowedTypes = array("jpg", "jpeg", "png", "gif", "jfif", "apng", "avif", "pjpeg", "pjp", "svg", "webp");
    $uniqueImageName = uniqid() . '.' . $ext;
    $targetPath = "uploads/" . $uniqueImageName;

    if (in_array($ext, $allowedTypes) && move_uploaded_file($tmpName, $targetPath)) {
      // Signature upload
      $signature = $_FILES['signature']['name'];
      $signatureTmpName = $_FILES['signature']['tmp_name'];
      $signatureExt = strtolower(pathinfo($signature, PATHINFO_EXTENSION));
      $uniqueSignatureName = uniqid() . '_signature.' . $signatureExt;
      $signaturePath = "uploads/" . $uniqueSignatureName;

      if (in_array($signatureExt, $allowedTypes) && move_uploaded_file($signatureTmpName, $signaturePath)) {
        // Insert into user_details with prepared statement
        $sql = "INSERT INTO user_details 
                      (ID, lastName, firstName, middleName, suffix, birthDate, 
                      birthPlace, age, sex, department, jobTitle, contactNumber, civilStatus, 
                      address, nationality, email, profileImage, signature, 
                      educationalQualification, religion, sssNumber, tinNumber, 
                      pagibigNumber, philhealthID, dateHired)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
          $stmt = $conn->prepare($sql);
          $stmt->bind_param(
            "sssssssisssssssssssssssss",
            $ID,
            $lastName,
            $firstName,
            $middleName,
            $suffix,
            $birthDate,
            $birthPlace,
            $age,
            $sex,
            $department,
            $jobTitle,
            $contactNumber,
            $civilStatus,
            $address,
            $nationality,
            $email,
            $uniqueImageName,
            $uniqueSignatureName,
            $educationalQualification,
            $religion,
            $sssNumber,
            $tinNumber,
            $pagibigNumber,
            $philhealthID,
            $dateHired
          );

          if ($stmt->execute()) {
            // Update isRegistered status only after successful insert
            $updateSql = "UPDATE employee_validation 
                                      SET isRegistered = TRUE 
                                      WHERE ID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("s", $ID);
            $updateStmt->execute();

            // Check if the user is admin or department head
            if ($department == "Admin") {
              $adminSql = "INSERT INTO admin_table (ID, firstName, lastName, department) VALUES (?, ?, ?, ?)";
              $adminStmt = $conn->prepare($adminSql);
              $adminStmt->bind_param("ssss", $ID, $firstName, $lastName, $department);
              $adminStmt->execute();
            } elseif ($department == "Department Head") {
              $deptHeadSql = "INSERT INTO dept_head_table (ID, firstName, lastName, department) VALUES (?, ?, ?, ?)";
              $deptHeadStmt = $conn->prepare($deptHeadSql);
              $deptHeadStmt->bind_param("ssss", $ID, $firstName, $lastName, $department);
              $deptHeadStmt->execute();
            } elseif ($department == "Dean of Studies") {
              $deanSql = "INSERT INTO dean_table (ID, firstName, lastName, department) VALUES (?, ?, ?, ?)";
              $deanStmt = $conn->prepare($deanSql);
              $deanStmt->bind_param("ssss", $ID, $firstName, $lastName, $department);
              $deanStmt->execute();
            }

            // Redirect based on department
            if ($department == "Admin") {
              header("Location: create_admin.php");
            } elseif ($department == "Elementary" || $department == "Highschool" || $department == "College") {
              header("Location: create_employee.php");
            } elseif ($department == "Department Head" || $department == "Dean of Studies") {
              header("Location: create_deptHead.php");
            }
            exit();
          } else {
            $alertMessages[] = 'Error: ' . $stmt->error;
          }
        } catch (Exception $e) {
          $alertMessages[] = 'Error: ' . $e->getMessage();
        }
      } else {
        $alertMessages[] = 'Error: Signature not uploaded or invalid file type.';
      }
    } else {
      $alertMessages[] = 'Error: Profile image not uploaded or invalid file type.';
    }
  }
}
?>









<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HRIMS Registration</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --maroon: #800000;
      --off-white: #faf9f6;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      background: url('assets/img/background.png') no-repeat center center fixed;
      background-size: cover;
      position: relative;
      height: 100vh;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(212, 189, 189, 0.479);
      z-index: 1;
    }

    .container-fluid {
      z-index: 5 !important;
    }


    .form-container {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      max-width: 80vw;
      height: 80vh;
      margin: auto;
      display: flex;
      flex-direction: column;
      margin-top: -75px;


    }

    .form-content {
      padding: 20px;
      overflow-y: auto;
      /* Keep scrolling functionality */
      flex-grow: 1;

      /* Hide scrollbar */
      scrollbar-width: none;
      /* For Firefox */
      -ms-overflow-style: none;
      /* For Internet Explorer and Edge */
    }

    .form-content::-webkit-scrollbar {
      display: none;
      /* For Chrome, Safari, and Edge */
    }

    .form-footer {
      position: fixed;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80vw;
      padding: 10px 20px;
      background-color: white;
      border-top: 1px solid #ddd;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
      text-align: center;
      margin-bottom: 82px;
    }

    .header {
      background-color: var(--maroon);
      color: white;
      padding: 13px;
      border-radius: 10px 10px 0 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      z-index: 5;
    }

    .logo {
      width: 60px;
      height: 60px;
      background-color: white;
      border-radius: 50%;
      padding: 5px;
    }

    .section-header {
      color: var(--maroon);
      font-size: 1rem;
      font-weight: 600;
      margin-top: 20px;
      margin-bottom: 10px;
      padding-bottom: 3px;
      border-bottom: 2px solid var(--maroon);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--maroon);
      box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
    }

    .btn-maroon {
      background-color: var(--maroon);
      color: white;
    }

    .btn-maroon:hover {
      background-color: #600000;
      color: white;
    }

    .custom-file-input input[type="file"] {
      display: none;
    }

    .custom-file-input label {
      background-color: var(--off-white);
      border: 1px solid #ddd;
      padding: 6px 10px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="form-container">
      <div class="header">
        <img src="assets/img/logo.png" alt="School Logo" class="logo">
        <h2 class="text-center mb-0">HRIMS Registration Form</h2>
      </div>

      <form class="form-content row g-3" action="register.php" method="POST" enctype="multipart/form-data">
        <!-- Display all alert messages -->
        <?php if (!empty($alertMessages)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
              <?php foreach ($alertMessages as $message): ?>
                <li><?= $message ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- System Information -->
        <div class="col-12">
          <h3 class="section-header">System Information</h3>
        </div>
        <div class="col-md-2">
          <label class="form-label" for="ID">ID</label>
          <input type="text" class="form-control" id="ID" name="ID" required>
        </div>

        <!-- Personal Information -->
        <div class="col-12">
          <h3 class="section-header">Personal Information</h3>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="lastName">Last Name</label>
          <input type="text" class="form-control" id="lastName" name="lastName" required placeholder="Last Name">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="firstName">First Name</label>
          <input type="text" class="form-control" id="firstName" name="firstName" required placeholder="First Name">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="middleName">Middle Name</label>
          <input type="text" class="form-control" id="middleName" name="middleName" required placeholder="Middle Name">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="suffix">Suffix</label>
          <input type="text" class="form-control" id="suffix" name="suffix">
        </div>

        <!-- Birth and Demographic Information -->
        <div class="col-12">
          <h3 class="section-header">Birth and Demographic Information</h3>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="birthDate">Birth Date</label>
          <input type="date" class="form-control" id="birthDate" name="birthDate">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="birthPlace">Birth Place</label>
          <input type="text" class="form-control" id="birthPlace" name="birthPlace">
        </div>
        <div class="col-md-2">
          <label class="form-label" for="age">Age</label>
          <input type="number" class="form-control" id="age" name="age" required placeholder="Age">
        </div>
        <div class="col-md-2">
          <label class="form-label" for="sex">Sex</label>
          <select class="form-select" id="sex" name="sex" required>
            <option value="" disabled selected>Select Sex</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label" for="civilStatus">Civil Status</label>
          <select class="form-select" id="civilStatus" name="civilStatus" required>
            <option value="" disabled selected>Select Civil Status</option>
            <option value="Married">Married</option>
            <option value="Single">Single</option>
            <option value="Separated/Divorced">Separated/Divorced</option>
            <option value="Widowed">Widowed</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="religion">Religion</label>
          <select class="form-select" id="religion" name="religion" required>
            <option value="" disabled selected>Select Religion</option>
            <option value="Roman Catholic">Roman Catholic</option>
            <option value="Iglesia ni Kristo">Iglesia ni Kristo</option>
            <option value="Islam">Islam</option>
            <option value="Seventh-day Adventist Church">Seventh-day Adventist Church</option>
            <option value="Jehovah's Witnesses">Jehovah's Witnesses</option>
            <option value="Others">Others</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="nationality">Nationality</label>
          <input type="text" class="form-control" id="nationality" name="nationality" required
            placeholder="Nationality">
        </div>

        <!-- Contact Information -->
        <div class="col-12">
          <h3 class="section-header">Contact Information</h3>
        </div>
        <div class="col-md-12">
          <label class="form-label" for="address">Complete Address</label>
          <input type="text" class="form-control" id="address" name="address" required placeholder="Complete Address">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="contactNumber">Contact Number</label>
          <input type="number" class="form-control" id="contactNumber" name="contactNumber" required
            placeholder="Contact Number">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="eMail">E-mail</label>
          <input type="email" class="form-control" id="eMail" name="eMail" required placeholder="sample@gmail.com">
        </div>

        <!-- Employment Information -->
        <div class="col-12">
          <h3 class="section-header">Employment Information</h3>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="department">Department</label>
          <select class="form-select" id="department" name="department" required>
            <option value="" disabled selected>Select Department</option>
            <option value="Elementary">Elementary</option>
            <option value="Highschool">Highschool</option>
            <option value="College">College</option>
            <option value="Admin">Admin</option>
            <option value="Department Head">Department Head</option>
            <option value="Dean of Studies">Dean of Studies</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="jobTitle">Job Title</label>
          <input type="text" class="form-control" id="jobTitle" name="jobTitle" required placeholder="Job Title">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="dateHired">Date Hired</label>
          <input type="date" class="form-control" id="dateHired" name="dateHired" required>
        </div>
        <div class="col-md-12">
          <label class="form-label" for="educationalQualification">Educational Qualification</label>
          <input type="text" class="form-control" id="educationalQualification" name="educationalQualification" required
            placeholder="Educational Qualification">
        </div>

        <!-- Government IDs -->
        <div class="col-12">
          <h3 class="section-header">Government IDs</h3>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="sssNumber">SSS Number</label>
          <input type="text" class="form-control" id="sssNumber" name="sssNumber" required placeholder="SSS Number">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="tinNumber">TIN Number</label>
          <input type="text" class="form-control" id="tinNumber" name="tinNumber" required placeholder="TIN Number">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="pagibigNumber">Pag-ibig Number</label>
          <input type="text" class="form-control" id="pagibigNumber" name="pagibigNumber" required
            placeholder="Pag-ibig Number">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="philhealthID">PhilHealth ID</label>
          <input type="text" class="form-control" id="philhealthID" name="philhealthID" required
            placeholder="PhilHealth ID">
        </div>

        <!-- Documents -->
        <div class="col-12">
          <h3 class="section-header">Documents</h3>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="profileImage">Profile Image</label>
          <div class="custom-file-input">
            <input type="file" id="profileImage" name="profileImage" accept="image/*" required>
            <label for="profileImage"><i class="fas fa-upload"></i> Choose File</label>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="signature">Signature (<a href="https://www.remove.bg/" target="_blank"
              style="font-size: smaller;">click here to remove
              image background</a>)</label>
          <div class="custom-file-input">
            <input type="file" id="signature" name="signature" accept="image/*" required>
            <label for="signature"><i class="fas fa-upload"></i> Choose File</label>
          </div>
        </div>

        <div class="form-footer col-12 text-center mt-3">
          <button type="button" class="btn btn-maroon px-4" onclick="window.location.href='index.html'">Cancel</button>
          <button type="submit" class="btn btn-maroon px-4">Signup</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>