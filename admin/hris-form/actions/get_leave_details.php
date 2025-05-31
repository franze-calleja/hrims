<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

session_start();
include("../../includes/database.php");

header('Content-Type: application/json');

// Function to send an error message as a JSON response
function sendError($message) {
    $output = ob_get_clean(); // Get any output that was generated
    if (!empty($output)) {
        error_log("Unexpected output: " . $output);
    }
    echo json_encode(['error' => $message]);
    exit;
}

try {
    // Check if the user is logged in
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        sendError('Unauthorized access');
    }

    // Check if the leave_id is provided
    if (!isset($_GET['leave_id'])) {
        sendError('Leave ID not provided');
    }

    // Escape the leave_id to prevent SQL injection
    $leave_id = mysqli_real_escape_string($conn, $_GET['leave_id']);

    // Query to get leave details with employee and department head information
    $sql = "SELECT lf.leave_id, lf.leave_type, lf.start_date, lf.end_date, lf.place, lf.reason, lf.status, 
                   lf.dept_head_id, lf.employee_id, 
                   ud.firstName AS emp_firstName, ud.lastName AS emp_lastName, ud.department, ud.ID AS employee_id,
                   dh.firstName AS dh_firstName, dh.lastName AS dh_lastName
            FROM leave_forms lf
            JOIN user_details ud ON lf.employee_id = ud.ID
            LEFT JOIN user_details dh ON lf.dept_head_id = dh.ID
            WHERE lf.leave_id = '$leave_id'";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }

    // Fetch the leave form data and return it as a JSON response
    if ($row = mysqli_fetch_assoc($result)) {
        $row['dept_head_name'] = $row['dh_firstName'] . ' ' . $row['dh_lastName'];
        unset($row['dh_firstName'], $row['dh_lastName']);
        echo json_encode($row);
    } else {
        sendError('Leave details not found');
    }

} catch (Exception $e) {
    error_log("Error in get_leave_details.php: " . $e->getMessage());
    sendError('An unexpected error occurred: ' . $e->getMessage());
} finally {
    mysqli_close($conn);
    ob_end_flush();
}
?>
