<?php
ini_set('display_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("includes/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize form inputs
  $ID = filter_input(INPUT_POST, "ID", FILTER_SANITIZE_SPECIAL_CHARS);
  $lastName = filter_input(INPUT_POST, "lastName", FILTER_SANITIZE_SPECIAL_CHARS);
  $firstName = filter_input(INPUT_POST, "firstName", FILTER_SANITIZE_SPECIAL_CHARS);
  
  // First, validate against employee_validation table
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
      echo "Error: Invalid employee credentials or already registered. Please contact your administrator.";
      exit();
  }
  
  // If validation passes, update isRegistered status
  $updateSql = "UPDATE employee_validation 
                SET isRegistered = TRUE 
                WHERE ID = ?";
  $updateStmt = $conn->prepare($updateSql);
  $updateStmt->bind_param("s", $ID);
  $updateStmt->execute();

  // Continue with the rest of your registration logic
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

  // Handle file upload
  $image = $_FILES['profileImage']['name'];
  $tmpName = $_FILES['profileImage']['tmp_name'];
  $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION)); 
  $allowedTypes = array("jpg", "jpeg", "png", "gif");
  $uniqueImageName = uniqid() . '.' . $ext;
  $targetPath = "uploads/" . $uniqueImageName;

  

  if (in_array($ext, $allowedTypes)) {
      if (move_uploaded_file($tmpName, $targetPath)) {
          // Insert into user_details with prepared statement
          $sql = "INSERT INTO user_details 
                  (ID, lastName, firstName, middleName, suffix, birthDate, 
                  birthPlace, age, sex, department, jobTitle, contactNumber, civilStatus, 
                  address, nationality, email, profileImage, educationalQualification, religion, 
                  sssNumber, tinNumber, pagibigNumber, philhealthID, dateHired)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

          try {
              $stmt = $conn->prepare($sql);
              $stmt->bind_param("sssssssissssssssssssssss", 
                  $ID, $lastName, $firstName, $middleName, $suffix, $birthDate, 
                  $birthPlace, $age, $sex, $department, $jobTitle, $contactNumber, 
                  $civilStatus, $address, $nationality, $email, $uniqueImageName, 
                  $educationalQualification, $religion, $sssNumber, $tinNumber, 
                  $pagibigNumber, $philhealthID, $dateHired);

              if ($stmt->execute()) {
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
                  throw new Exception($stmt->error);
              }
          } catch (Exception $e) {
              echo "Error: " . $e->getMessage();
          }
      } else {
          echo "Error: Image not uploaded.";
      }
  } else {
      echo "Error: Invalid file type.";
  }
}
?>



<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("includes/database.php");

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
        echo "Error: Invalid employee credentials or already registered. Please contact your administrator.";
        exit();
    }

    // Update isRegistered status
    $updateSql = "UPDATE employee_validation 
                  SET isRegistered = TRUE 
                  WHERE ID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("s", $ID);
    $updateStmt->execute();

      // Continue with the rest of your registration logic
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
    $allowedTypes = array("jpg", "jpeg", "png", "gif");
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
                $stmt->bind_param("sssssssisssssssssssssssss", 
                    $ID, $lastName, $firstName, $middleName, $suffix, $birthDate, 
                    $birthPlace, $age, $sex, $department, $jobTitle, $contactNumber, 
                    $civilStatus, $address, $nationality, $email, $uniqueImageName, 
                    $uniqueSignatureName, $educationalQualification, $religion, 
                    $sssNumber, $tinNumber, $pagibigNumber, $philhealthID, $dateHired);

                if ($stmt->execute()) {
                    // Additional logic for department-based redirection
                    //...
                    exit();
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Error: Signature not uploaded or invalid file type.";
        }
    } else {
        echo "Error: Profile image not uploaded or invalid file type.";
    }
}
?>






updating profile

<?php
session_start();
include("../../includes/database.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ID = $_POST['ID'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $department = $_POST['department'];
    $jobTitle = $_POST['jobTitle'];
    $contactNumber = $_POST['contactNumber'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $birthDate = $_POST['birthDate'];
    $birthPlace = $_POST['birthPlace'];
    $address = $_POST['address'];
    $nationality = $_POST['nationality'];
    $civilStatus = $_POST['civilStatus'];
    $email = $_POST['email'];
    $educationalQualification = $_POST['educationalQualification'];
    $religion = $_POST['religion'];
    $sssNumber = $_POST['sssNumber'];
    $tinNumber = $_POST['tinNumber'];
    $pagIbigNumber = $_POST['pagibigNumber'];
    $philHealthID = $_POST['philhealthID'];
    $dateHired = $_POST['dateHired'];

    // Update the SQL query
    $sql = "UPDATE user_details SET firstName = ?, lastName = ?, department = ?, jobTitle = ?, contactNumber = ?, age = ?, sex = ?, birthDate = ?, birthPlace = ?, address = ?, nationality = ?, civilStatus = ?, email = ?, educationalQualification = ?, religion = ?, sssNumber = ?, tinNumber = ?, pagIbigNumber = ?, philHealthID = ?, dateHired = ? WHERE ID = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param("sssssssssssssssssssss", $firstName, $lastName, $department, $jobTitle, $contactNumber, $age, $sex, $birthDate, $birthPlace, $address, $nationality, $civilStatus, $email, $educationalQualification, $religion, $sssNumber, $tinNumber, $pagIbigNumber, $philHealthID, $dateHired, $ID);

// Handle profile image upload if provided
if (!empty($_FILES['profileImage']['name'])) {
    $targetDir = "../../uploads/";
    $profileImage = basename($_FILES["profileImage"]["name"]);
    $imageExt = pathinfo($profileImage, PATHINFO_EXTENSION);
    $uniqueImageName = uniqid() . '.' . $imageExt;
    $targetFilePath = $targetDir . $uniqueImageName;

    // Upload the file and update the image path in the database
    if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFilePath)) {
        $sql = "UPDATE user_details SET firstName = ?, lastName = ?, department = ?, jobTitle = ?, contactNumber = ?, age = ?, sex = ?, birthDate = ?, birthPlace = ?, address = ?, nationality = ?, civilStatus = ?, email = ?, profileImage = ?, educationalQualification = ?, religion = ?, sssNumber = ?, tinNumber = ?, pagIbigNumber = ?, philHealthID = ?, dateHired = ? WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssssssssss", $firstName, $lastName, $department, $jobTitle, $contactNumber, $age, $sex, $birthDate, $birthPlace, $address, $nationality, $civilStatus, $email, $uniqueImageName, $educationalQualification, $religion, $sssNumber, $tinNumber, $pagIbigNumber, $philHealthID, $dateHired, $ID);
    }
}


    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Redirect back to profile page
    header("Location: ../admin_profile.php");
    exit;
}
?>


<div id="editProfileModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeEditModal()">&times;</span>
      <form action="includes/update_profile.php" method="POST" enctype="multipart/form-data">

        <div class="input-group">
          <div>
            <label for="ID">Admin ID</label>
            <input type="text" name="ID" id="ID" value="<?php echo htmlspecialchars($deptHeadDetails['ID']); ?>"
              readonly>
          </div>
          <div>
            <label for="lastName">Last Name</label>
            <input type="text" name="lastName" id="lastName"
              value="<?php echo htmlspecialchars($deptHeadDetails['lastName']); ?>">
          </div>
          <div>
            <label for="firstName">First Name</label>
            <input type="text" name="firstName" id="firstName"
              value="<?php echo htmlspecialchars($deptHeadDetails['firstName']); ?>">
          </div>
          <div>
            <label for="middleName">Middle Name</label>
            <input type="text" id="middleName" name="middleName" placeholder="Middle Name"
              value="<?php echo htmlspecialchars($deptHeadDetails['middleName']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="birthDate">Birthdate</label>
            <input type="date" name="birthDate" id="birthDate"
              value="<?php echo htmlspecialchars($deptHeadDetails['birthDate']); ?>">
          </div>
          <div>
            <label for="birthPlace">Birthplace</label>
            <input type="text" name="birthPlace" id="birthPlace"
              value="<?php echo htmlspecialchars($deptHeadDetails['birthPlace']); ?>">
          </div>
          <div>
            <label for="age">Age</label>
            <input type="text" name="age" id="age" value="<?php echo htmlspecialchars($deptHeadDetails['age']); ?>">
          </div>
          <div>
            <label for="sex">Gender</label>
            <input type="text" name="sex" id="sex" value="<?php echo htmlspecialchars($deptHeadDetails['sex']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="department">Department</label>
            <input type="text" name="department" id="department"
              value="<?php echo htmlspecialchars($deptHeadDetails['department']); ?>">
          </div>
          <div>
            <label for="jobTitle">Job Title</label>
            <input type="text" name="jobTitle" id="jobTitle"
              value="<?php echo htmlspecialchars($deptHeadDetails['jobTitle']); ?>">
          </div>
          <div>
            <label for="nationality">Nationality</label>
            <input type="text" name="nationality" id="nationality"
              value="<?php echo htmlspecialchars($deptHeadDetails['nationality']); ?>">
          </div>
          <div>
            <label for="civilStatus">Civil Status</label>
            <input type="text" name="civilStatus" id="civilStatus"
              value="<?php echo htmlspecialchars($deptHeadDetails['civilStatus']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="contactNumber">Contact</label>
            <input type="text" name="contactNumber" id="contactNumber"
              value="<?php echo htmlspecialchars($deptHeadDetails['contactNumber']); ?>">
          </div>
          <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email"
              value="<?php echo htmlspecialchars($deptHeadDetails['email']); ?>">
          </div>
          <div>
            <label for="address">Address</label>
            <input type="text" name="address" id="address"
              value="<?php echo htmlspecialchars($deptHeadDetails['address']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="educationalQualification">Educational Qualification</label>
            <input type="text" name="educationalQualification" id="educationalQualification"
              value="<?php echo htmlspecialchars($deptHeadDetails['educationalQualification']); ?>">
          </div>
          <div>
            <label for="religion">Religion</label>
            <input type="text" name="religion" id="religion"
              value="<?php echo htmlspecialchars($deptHeadDetails['religion']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="sssNumber">SSS Number</label>
            <input type="text" name="sssNumber" id="sssNumber"
              value="<?php echo htmlspecialchars($deptHeadDetails['sssNumber']); ?>">
          </div>
          <div>
            <label for="tinNumber">TIN Number</label>
            <input type="text" name="tinNumber" id="tinNumber"
              value="<?php echo htmlspecialchars($deptHeadDetails['tinNumber']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="pagibigNumber">Pag-ibig Number</label>
            <input type="text" name="pagibigNumber" id="pagibigNumber"
              value="<?php echo htmlspecialchars($deptHeadDetails['pagibigNumber']); ?>">
          </div>
          <div>
            <label for="philhealthID">PhilHealth ID</label>
            <input type="text" name="philhealthID" id="philhealthID"
              value="<?php echo htmlspecialchars($deptHeadDetails['philhealthID']); ?>">
          </div>
        </div>

        <div class="input-group">
          <div>
            <label for="dateHired">Date Hired</label>
            <input type="date" name="dateHired" id="dateHired"
              value="<?php echo htmlspecialchars($deptHeadDetails['dateHired']); ?>">
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

        <div class="input-group">
          <div>
            <label for="signatureImage">Signature</label>
            <div class="custom-file-input">
              <label for="signatureImage">Upload Signature</label>
              <input type="file" name="signatureImage" id="signatureImage" accept="image/*">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <!-- Submit Button -->
          <button type="submit" class="btn btn-danger" style="background-color: #800000 !important;">Save
            Changes</button>
        </div>
        <div id="error" class="error"></div>
      </form>
    </div>
  </div>