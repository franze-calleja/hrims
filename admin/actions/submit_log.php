<?php
session_start();
include("../../includes/database.php");

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['empID'])) {
    // Get the form data
    $empID = $_POST['empID'];
    $deptHeadID = $_POST['deptHeadReport']; // Ensure this maps to the correct dept_head_id
    $deanID = $_POST['deanReport']; // Ensure this maps to the correct dean_id
    $logDate = $_POST['logDate']; // Log entry date
    $logTimeIn = $_POST['logTimeIn']; // Log time-in
    $logTimeOut = $_POST['logTimeOut']; // Log time-out
    $logDetails = $_POST['logDetails']; // Log details
    $status = 'pending'; // Default status
    $adminApproval = 'pending'; // Default admin approval status
    $deptHeadApproval = 'pending'; // Default department head approval status
    $deanApproval = 'pending'; // Default dean approval status
    $createdAt = date('Y-m-d H:i:s'); // Current timestamp
    $updatedAt = date('Y-m-d H:i:s'); // Current timestamp

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO log_forms (employee_id, dept_head_id, dean_id, log_date, log_time_in, log_time_out, log_details, status, admin_approval, dept_head_approval, dean_approval, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssssssssss", $empID, $deptHeadID, $deanID, $logDate, $logTimeIn, $logTimeOut, $logDetails, $status, $adminApproval, $deptHeadApproval, $deanApproval, $createdAt, $updatedAt);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Log form submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting log form: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or missing data.']);
}

$conn->close();
?>
