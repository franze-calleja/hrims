<?php
session_start();
include("../../includes/database.php");
// Function to check and update Log status
function checkAndUpdateLogStatus($conn, $log_id) {
    // Query to get current approval statuses
    $sql = "SELECT dept_head_approval, dean_approval, admin_approval, status 
            FROM log_form 
            WHERE log_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $log_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Check if all approvals are 'approved'
        if ($row['dept_head_approval'] == 'approved' &&
            $row['dean_approval'] == 'approved' &&
            $row['admin_approval'] == 'approved' &&
            $row['status'] != 'approved') {
            
            // Update the overall status to 'approved'
            $update_sql = "UPDATE log_form SET status = 'approved' WHERE log_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $log_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                mysqli_stmt_close($update_stmt);
                return ['updated' => true, 'new_status' => 'approved'];
            }
        }
        // Check if any approval is 'declined'
        elseif ($row['dept_head_approval'] == 'declined' ||
                $row['dean_approval'] == 'declined' ||
                $row['admin_approval'] == 'declined') {
            
            // Update the overall status to 'declined'
            $update_sql = "UPDATE log_form SET status = 'declined' WHERE log_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $log_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                mysqli_stmt_close($update_stmt);
                return ['updated' => true, 'new_status' => 'declined'];
            }
        }
    }
    
    mysqli_stmt_close($stmt);
    return ['updated' => false];
}

// Main script logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get log_id and action from the request
    $log_id = $_POST['log_id'];
    $action = $_POST['action'];
    $decline_reason = isset($_POST['decline_reason']) ? $_POST['decline_reason'] : null;

    // Determine the new status based on the action
    $new_status = ($action === 'approve') ? 'approved' : 'declined';

    // Get the user's role from the session
    $role = $_SESSION['role'];

    // Prepare the SQL statement based on the user's role
    $sql = "";
    switch ($role) {
        case 'deptHead':
            $sql = "UPDATE log_form SET dept_head_approval = ?, decline_reason = ? WHERE log_id = ?";
            break;
        case 'dean':
            $sql = "UPDATE log_form SET dean_approval = ?, decline_reason = ? WHERE log_id = ?";
            break;
        case 'admin':
            $sql = "UPDATE log_form SET admin_approval = ?, decline_reason = ? WHERE log_id = ?";
            break;
        default:
            echo json_encode(['error' => 'Invalid role']);
            exit;
    }

    // Execute the update query
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $decline_reason, $log_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        // Check and update overall status if necessary
        $status_update = checkAndUpdateLogStatus($conn, $log_id);

        if ($status_update['updated']) {
            $message = ($status_update['new_status'] === 'approved') 
                ? 'Log form has been approved and status updated to Approved'
                : 'Log form has been declined and status updated to Declined';
            echo json_encode(['success' => $message]);
        } else {
            echo json_encode(['success' => 'Log form has been updated']);
        }
    } else {
        echo json_encode(['error' => 'Failed to update the Log form']);
    }

    mysqli_close($conn);
}
?>