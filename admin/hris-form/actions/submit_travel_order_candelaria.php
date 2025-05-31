<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    $destination = $_POST['destination'];
    $purpose = $_POST['purpose'];
    $travelStartDate = $_POST['travelStartDate'];
    $travelTime = $_POST['travelTime'];
    $returnTime = $_POST['returnTime'];
    $deptHeadID = $_POST['deptHeadReport'];  // Ensure this maps to the correct dept_head_id
    $deanID = $_POST['deanReport'];  // Ensure this maps to the correct dean_id
    $status = 'pending';
    $adminApproval = 'pending';
    $deptHeadApproval = 'pending';
    $deanApproval = 'pending';
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO travel_order_candelaria_forms (employee_id, destination, purpose, travel_start_date, travel_time, return_time, dept_head_id, dean_id, status, admin_approval, dept_head_approval, dean_approval, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssssssssss", $empID, $destination, $purpose, $travelStartDate, $travelTime, $returnTime, $deptHeadID, $deanID, $status, $adminApproval, $deptHeadApproval, $deanApproval, $createdAt, $updatedAt);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Travel order form submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting travel order form: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or missing data.']);
}

$conn->close();
?>
