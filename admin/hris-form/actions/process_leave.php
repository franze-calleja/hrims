<?php
session_start();
include("../../includes/database.php");

// Function to check and update leave status
function checkAndUpdateLeaveStatus($conn, $leave_id) {
    // Query to get current approval statuses
    $sql = "SELECT dept_head_approval, dean_approval, admin_approval, status 
            FROM leave_forms 
            WHERE leave_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $leave_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Check if all approvals are 'approved'
        if ($row['dept_head_approval'] == 'approved' &&
            $row['dean_approval'] == 'approved' &&
            $row['admin_approval'] == 'approved' &&
            $row['status'] != 'approved') {
            
            // Update the overall status to 'approved'
            $update_sql = "UPDATE leave_forms SET status = 'approved' WHERE leave_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $leave_id);
            
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
            $update_sql = "UPDATE leave_forms SET status = 'declined' WHERE leave_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $leave_id);
            
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
    // Get leave_id and action from the request
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];

    // Determine the new status based on the action
    $new_status = ($action === 'approve') ? 'approved' : 'declined';

    // Get the user's role from the session
    $role = $_SESSION['role'];

    // Prepare the SQL statement based on the user's role
    $sql = "";
    switch ($role) {
        case 'deptHead':
            $sql = "UPDATE leave_forms SET dept_head_approval = ? WHERE leave_id = ?";
            break;
        case 'dean':
            $sql = "UPDATE leave_forms SET dean_approval = ? WHERE leave_id = ?";
            break;
        case 'admin':
            $sql = "UPDATE leave_forms SET admin_approval = ? WHERE leave_id = ?";
            break;
        default:
            echo json_encode(['error' => 'Invalid role']);
            exit;
    }

    // Execute the update query
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $leave_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        // Check and update overall status if necessary
        $status_update = checkAndUpdateLeaveStatus($conn, $leave_id);

        if ($status_update['updated']) {
            $message = ($status_update['new_status'] === 'approved') 
                ? 'Leave form has been approved and status updated to Approved'
                : 'Leave form has been declined and status updated to Declined';
            echo json_encode(['success' => $message]);
        } else {
            echo json_encode(['success' => 'Leave form has been updated']);
        }
    } else {
        echo json_encode(['error' => 'Failed to update the leave form']);
    }

    mysqli_close($conn);
}
?>