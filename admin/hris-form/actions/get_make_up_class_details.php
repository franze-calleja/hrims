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

    // Check if the makeup_class_id is provided
    if (!isset($_GET['makeup_class_id'])) {
        sendError('Make-up Class ID not provided');
    }

    // Escape the makeup_class_id to prevent SQL injection
    $makeup_class_id = mysqli_real_escape_string($conn, $_GET['makeup_class_id']);

    // Query to get leave details with employee and department head information
    $sql = "SELECT mc.makeup_class_id, ud.firstName, ud.lastName, mc.subject, mc.regular_class_date, 
    mc.regular_class_time, mc.regular_class_room, mc.makeup_class_date, 
    mc.makeup_class_time, mc.makeup_class_room, mc.status, mc.reason, mc.dept_head_id, mc.dean_id,
    ud.department, ud.ID AS employee_id,
    dh.firstName AS dh_firstName, dh.lastName AS dh_lastName,
    dn.firstName AS dean_firstName, dn.lastName AS dean_lastName
FROM make_up_forms mc
JOIN user_details ud ON mc.employee_id = ud.ID
LEFT JOIN user_details dh ON mc.dept_head_id = dh.ID
LEFT JOIN user_details dn ON mc.dean_id = dn.ID
WHERE mc.makeup_class_id = '$makeup_class_id'";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }

    // Fetch the leave form data and return it as a JSON response
    if ($row = mysqli_fetch_assoc($result)) {
      $row['dept_head_fullname'] = $row['dh_firstName'] . ' ' . $row['dh_lastName'];
      $row['dean_fullname'] = $row['dean_firstName'] . ' ' . $row['dean_lastName'];
      unset($row['dh_firstName'], $row['dh_lastName'], $row['dean_firstName'], $row['dean_lastName']);
      echo json_encode($row);
  } else {
      sendError('Make-Up Class details not found');
  }

} catch (Exception $e) {
    error_log("Error in get_make_up_class_details.php: " . $e->getMessage());
    sendError('An unexpected error occurred: ' . $e->getMessage());
} finally {
    mysqli_close($conn);
    ob_end_flush();
}
?>
