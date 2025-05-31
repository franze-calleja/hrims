<?php
session_start();
include("../../includes/database.php");
// Function to check and update leave status
function checkAndUpdateMakeUpClassStatus($conn, $makeup_class_id) {
    // Query to get current approval statuses
    $sql = "SELECT dept_head_approval, dean_approval, admin_approval, status 
            FROM make_up_forms 
            WHERE makeup_class_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $makeup_class_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Check if all approvals are 'approved'
        if ($row['dept_head_approval'] == 'approved' &&
            $row['dean_approval'] == 'approved' &&
            $row['admin_approval'] == 'approved' &&
            $row['status'] != 'approved') {
            
            // Update the overall status to 'approved'
            $update_sql = "UPDATE make_up_forms SET status = 'approved' WHERE makeup_class_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $makeup_class_id);
            
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
            $update_sql = "UPDATE make_up_forms SET status = 'declined' WHERE makeup_class_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $makeup_class_id);
            
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
    // Get makeup_class_id and action from the request
    $makeup_class_id = $_POST['makeup_class_id'];
    $action = $_POST['action'];

    // Determine the new status based on the action
    $new_status = ($action === 'approve') ? 'approved' : 'declined';

    // Get the user's role from the session
    $role = $_SESSION['role'];

    // Prepare the SQL statement based on the user's role
    $sql = "";
    switch ($role) {
        case 'deptHead':
            $sql = "UPDATE make_up_forms SET dept_head_approval = ? WHERE makeup_class_id = ?";
            break;
        case 'dean':
            $sql = "UPDATE make_up_forms SET dean_approval = ? WHERE makeup_class_id = ?";
            break;
        case 'admin':
            $sql = "UPDATE make_up_forms SET admin_approval = ? WHERE makeup_class_id = ?";
            break;
        default:
            echo json_encode(['error' => 'Invalid role']);
            exit;
    }

    // Execute the update query
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $makeup_class_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        // Check and update overall status if necessary
        $status_update = checkAndUpdateMakeUpClassStatus($conn, $makeup_class_id);

        if ($status_update['updated']) {
            $message = ($status_update['new_status'] === 'approved') 
                ? 'Make-up Class form has been approved and status updated to Approved'
                : 'Make-up Class form has been declined and status updated to Declined';
            echo json_encode(['success' => $message]);
        } else {
            echo json_encode(['success' => 'Make-up Class form has been updated']);
        }
    } else {
        echo json_encode(['error' => 'Failed to update the Make-up Class form']);
    }

    mysqli_close($conn);
}
?>