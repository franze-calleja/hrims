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
    $deptHeadID = $_POST['deptHeadReport'];
    $deanID = $_POST['deanReport'];
    $subject = $_POST['subject'];
    $regClassDate = $_POST['regClassDate'];
    $regClassTime = $_POST['regClassTime'];
    $regClassRoom = $_POST['regClassRoom'];
    $makeupClassDate = $_POST['makeupClassDate'];
    $makeupClassTime = $_POST['makeupClassTime'];
    $makeupClassRoom = $_POST['makeupClassRoom'];
    $reason = $_POST['reason'];
    $status = 'pending';
    $adminApproval = 'pending';
    $deptHeadApproval = 'pending';
    $deanApproval = 'pending';
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO make_up_forms (employee_id, dept_head_id, dean_id, subject, regular_class_date, 
        regular_class_time, regular_class_room, makeup_class_date, makeup_class_time, makeup_class_room, reason, 
        status, admin_approval, dept_head_approval, dean_approval, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssssssssssssssss", 
        $empID, 
        $deptHeadID, 
        $deanID, 
        $subject, 
        $regClassDate, 
        $regClassTime, 
        $regClassRoom, 
        $makeupClassDate, 
        $makeupClassTime, 
        $makeupClassRoom, 
        $reason, 
        $status, 
        $adminApproval, 
        $deptHeadApproval, 
        $deanApproval, 
        $createdAt, 
        $updatedAt
    );

    // Execute and check for success
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Make-up class form submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting make-up class form: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or missing data.']);
}

$conn->close();
?>