<?php
session_start();
include("../includes/database.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_SESSION['username'];
    $certificate_name = isset($_POST['certificate_name']) ? mysqli_real_escape_string($conn, $_POST['certificate_name']) : '';
    $certificate_type = isset($_POST['certificate_type']) ? mysqli_real_escape_string($conn, $_POST['certificate_type']) : '';
    
    // File upload handling
    $upload_dir = '../uploads/certificates/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] == 0) {
        $file = $_FILES['certificate_file'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_types = array('pdf', 'jpg', 'jpeg', 'png');

        // Check file type and size
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error'] = "Only PDF, JPG, JPEG, & PNG files are allowed.";
            header("Location: certificates.php");
            exit;
        }

        if ($file['size'] > 5000000) {
            $_SESSION['error'] = "File size exceeds 5MB limit.";
            header("Location: certificates.php");
            exit;
        }

        // Generate unique filename using employee_id
        $unique_filename = uniqid() . '_' . $employee_id . '.' . $file_extension;
        $file_path = $upload_dir . $unique_filename;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Insert into database with prepared statement
            $sql = "INSERT INTO employee_certificates (employee_id, certificate_name, certificate_type, file_path) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $employee_id, $certificate_name, $certificate_type, $unique_filename);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Certificate uploaded successfully!";
            } else {
                $_SESSION['error'] = "Database error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $_SESSION['error'] = "Error uploading file.";
        }
    } else {
        $_SESSION['error'] = "No file uploaded or upload error.";
    }

    $conn->close();
    header("Location: employee_certificate.php");
    exit;
}
?>
