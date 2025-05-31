<?php
session_start();
include("../includes/database.php");

// Check if the user is logged in as an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = 'Unauthorized access. Please log in as an admin.';
    header("Location: login.php"); // Redirect to login if not an admin
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $employee_id = isset($_POST['employee_id']) ? mysqli_real_escape_string($conn, $_POST['employee_id']) : '';
    $certificate_name = isset($_POST['certificate_name']) ? mysqli_real_escape_string($conn, $_POST['certificate_name']) : '';
    $certificate_type = isset($_POST['certificate_type']) ? mysqli_real_escape_string($conn, $_POST['certificate_type']) : '';

    // File upload handling
    if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] == 0) {
        $file = $_FILES['certificate_file'];
        
        // Define allowed file types and max file size (5MB)
        $allowed_types = array('pdf', 'jpg', 'jpeg', 'png');
        $max_size = 5 * 1024 * 1024; // 5MB in bytes
        
        // Get file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check file extension
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error_message'] = 'Invalid file type. Allowed types are: ' . implode(', ', $allowed_types);
            header("Location: certificate.php");
            exit();
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            $_SESSION['error_message'] = 'File size exceeds maximum limit of 5MB.';
            header("Location: certificate.php");
            exit();
        }

        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/certificates/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename using uniqid() and employee_id
        $unique_filename = uniqid() . '_' . $employee_id . '.' . $file_extension;
        $file_path = $upload_dir . $unique_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Use prepared statement for better security
            $sql = "INSERT INTO employee_certificates 
                    (employee_id, certificate_name, certificate_type, file_path) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $employee_id, $certificate_name, $certificate_type, $unique_filename);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Certificate uploaded successfully!';
            } else {
                $_SESSION['error_message'] = 'Error storing certificate details: ' . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $_SESSION['error_message'] = 'Failed to upload file.';
        }
    } else {
        $_SESSION['error_message'] = 'No file uploaded or upload error occurred.';
    }

    // Close database connection
    $conn->close();
    
    // Redirect back to the certificates page
    header("Location: certificate.php");
    exit();
}
?>
