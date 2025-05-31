


<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../../includes/database.php");

header('Content-Type: application/json');

function sendError($message) {
    echo json_encode(['error' => $message]);
    exit;
}

try {
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        sendError('Unauthorized access');
    }

    if (!isset($_GET['log_id'])) {
        sendError('Log ID not provided');
    }

    $log_id = mysqli_real_escape_string($conn, $_GET['log_id']);

    $sql = "SELECT lf.log_id, ud.firstName, ud.lastName, lf.log_date, lf.log_time_in, 
            lf.log_time_out, lf.log_details, lf.status, lf.dept_head_id, lf.dean_id,
            dh.firstName AS dh_firstName, dh.lastName AS dh_lastName,
            dn.firstName AS dean_firstName, dn.lastName AS dean_lastName
            FROM log_forms lf
            JOIN user_details ud ON lf.employee_id = ud.ID
            LEFT JOIN user_details dh ON lf.dept_head_id = dh.ID
            LEFT JOIN user_details dn ON lf.dean_id = dn.ID
            WHERE lf.log_id = '$log_id'";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }

    if ($row = mysqli_fetch_assoc($result)) {
        $row['dept_head_fullname'] = $row['dh_firstName'] . ' ' . $row['dh_lastName'];
        $row['dean_fullname'] = $row['dean_firstName'] . ' ' . $row['dean_lastName'];
        unset($row['dh_firstName'], $row['dh_lastName'], $row['dean_firstName'], $row['dean_lastName']);
        echo json_encode($row);
    } else {
        sendError('Log details not found');
    }

} catch (Exception $e) {
    error_log("Error in get_log_details.php: " . $e->getMessage());
    sendError('An unexpected error occurred: ' . $e->getMessage());
} finally {
    mysqli_close($conn);
}
?>