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
    $leaveType = $_POST['leaveType'];
    $deptHeadReport = $_POST['deptHeadReport'];
    $deanReport = $_POST['deanReport']; // Make sure this field is in your form
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $place = $_POST['place'];
    $reason = $_POST['reason'];
    $status = 'pending';
    $adminApproval = 'pending';
    $deptHeadApproval = 'pending';
    $deanApproval = 'pending';
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO leave_forms (employee_id, leave_type, dept_head_id, dean_id, start_date, end_date, place, reason, status, admin_approval, dept_head_approval, dean_approval, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssssssssss", $empID, $leaveType, $deptHeadReport, $deanReport, $startDate, $endDate, $place, $reason, $status, $adminApproval, $deptHeadApproval, $deanApproval, $createdAt, $updatedAt);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Leave form submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting leave form: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or missing data.']);
}

$conn->close();
?>