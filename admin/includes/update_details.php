<?php
session_start();

// Include the database connection
include("../../includes/database.php");

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit;
}

// Get user ID (username) from session
$username = $_SESSION['username'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the posted form data
    $ID = $_POST['ID'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $department = $_POST['department'];
    $jobTitle = $_POST['jobTitle'];
    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $birthDate = $_POST['birthDate'];
    $age = $_POST['age'];
    $birthPlace = $_POST['birthPlace'];
    $sex = $_POST['sex'];
    $religion = $_POST['religion'];
    $civilStatus = $_POST['civilStatus'];
    $nationality = $_POST['nationality'];
    $educationalQualification = $_POST['educationalQualification'];
    $dateHired = $_POST['dateHired'];
    $sssNumber = $_POST['sssNumber'];
    $tinNumber = $_POST['tinNumber'];
    $pagibigNumber = $_POST['pagibigNumber'];
    $philhealthID = $_POST['philhealthID'];

    // Handle profile image upload
    $profileImage = $_FILES['profileImage']['name'];
    if ($profileImage) {
        // Upload the profile image to a specific directory
        $targetDir = "../../uploads/";
        $profileImagePath = $targetDir . basename($profileImage);
        move_uploaded_file($_FILES['profileImage']['tmp_name'], $profileImagePath);
    } else {
        // Keep the existing profile image if not updated
        $profileImage = $_POST['currentProfileImage'];
    }

    // Handle signature upload
    $signature = $_FILES['signature']['name'];
    if ($signature) {
        // Upload the signature image
        $targetDir = "../../uploads/";
        $signaturePath = $targetDir . basename($signature);
        move_uploaded_file($_FILES['signature']['tmp_name'], $signaturePath);
    } else {
        // Keep the existing signature if not updated
        $signature = $_POST['currentSignature'];
    }

    // Handle password update
    $password = $_POST['password'];
    if (!empty($password)) {

        // Determine the correct table based on department
        if ($department === 'Admin') {
            // Hash the new password before saving it
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Prepare the SQL query for updating the password in the admin_credentials table
            $sqlAdminCredentials = "UPDATE admin_credentials SET password = ? WHERE ID = ?";
            if ($stmt = $conn->prepare($sqlAdminCredentials)) {
                $stmt->bind_param("ss", $hashedPassword, $ID);

                // Execute the query to update the password
                if (!$stmt->execute()) {
                    // Error updating the password
                    $stmt->close();
                    header("Location: ../update_employee.php?id=" . urlencode($ID) . "&status=error");

                    exit;
                }
                $stmt->close();
            }
        } elseif ($department === 'Department Head' || $department === 'Dean of Studies') {
            // Hash the new password before saving it
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Prepare the SQL query for updating the password in the admin_credentials table
            $sqlAdminCredentials = "UPDATE deptHead_credentials SET password = ? WHERE ID = ?";
            if ($stmt = $conn->prepare($sqlAdminCredentials)) {
                $stmt->bind_param("ss", $hashedPassword, $ID);

                // Execute the query to update the password
                if (!$stmt->execute()) {
                    // Error updating the password
                    $stmt->close();
                    header("Location: ../update_employee.php?id=" . urlencode($ID) . "&status=error");

                    exit;
                }
                $stmt->close();
            }
        } elseif ($department === 'Elementary' || $department === 'Highschool' || $department === 'College' || $department === 'Non-Faculty') {
            // Hash the new password before saving it
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Prepare the SQL query for updating the password in the admin_credentials table
            $sqlAdminCredentials = "UPDATE employee_credentials SET password = ? WHERE ID = ?";
            if ($stmt = $conn->prepare($sqlAdminCredentials)) {
                $stmt->bind_param("ss", $hashedPassword, $ID);

                // Execute the query to update the password
                if (!$stmt->execute()) {
                    // Error updating the password
                    $stmt->close();
                    header("Location: ../update_employee.php?id=" . urlencode($ID) . "&status=error");

                    exit;
                }
                $stmt->close();
            }
        }



    }

    // Prepare the SQL query for updating the user details
    $sqlUserDetails = "UPDATE user_details SET firstName = ?, middleName = ?, lastName = ?, department = ?, jobTitle = ?, contactNumber = ?, email = ?, address = ?, birthDate = ?, age = ?, birthPlace = ?, sex = ?, religion = ?, civilStatus = ?, nationality = ?, educationalQualification = ?, dateHired = ?, sssNumber = ?, tinNumber = ?, pagibigNumber = ?, philhealthID = ?, profileImage = ?, signature = ? WHERE ID = ?";

    // Prepare the query
    if ($stmt = $conn->prepare($sqlUserDetails)) {
        $stmt->bind_param("sssssssssissssssssssssss", $firstName, $middleName, $lastName, $department, $jobTitle, $contactNumber, $email, $address, $birthDate, $age, $birthPlace, $sex, $religion, $civilStatus, $nationality, $educationalQualification, $dateHired, $sssNumber, $tinNumber, $pagibigNumber, $philhealthID, $profileImage, $signature, $ID);

        // Execute the query
        if ($stmt->execute()) {
            // Close statement before redirecting
            $stmt->close();
            // Redirect with success message
            header("Location: ../update_employee.php?id=" . urlencode($ID) . "&status=success");
            exit;
        } else {
            // Close statement before redirecting
            $stmt->close();
            // Redirect with error message
            header("Location: ../update_employee.php?id=" . urlencode($ID) . "&status=error");

            exit;
        }
    }

    // Close the database connection
    $conn->close();
}

?>