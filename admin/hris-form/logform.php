<?php
session_start();
include("../includes/database.php"); // Adjust the path to your database connection file

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

// Retrieve the log_id from a GET or POST request
$log_id = isset($_GET['log_id']) ? $_GET['log_id'] : null;

if ($log_id) {
    // Prepare the SQL query
    $sql = "SELECT 
               logf.log_id, 
               logf.employee_id, 
               logf.dept_head_id, 
               logf.dean_id, 
               logf.log_in_out, 
               logf.log_date, 
               logf.log_time,
               logf.reason, 
               logf.status, 
               logf.admin_approval, 
               logf.dept_head_approval, 
               logf.dean_approval, 
               logf.created_at, 
               logf.updated_at, 
               emp.firstName AS empFirstName, 
               emp.lastName AS empLastName, 
               emp.department AS empDepartment, 
               emp.jobTitle AS empJobTitle, 
               emp.signature AS empSignature,
               dh.firstName AS deptHeadFirstName, 
               dh.lastName AS deptHeadLastName, 
               dh.signature AS deptHeadSignature,
               dean.firstName AS deanFirstName, 
               dean.lastName AS deanLastName, 
               dean.signature AS deanSignature,
               admin.firstName AS adminFirstName, 
               admin.lastName AS adminLastName, 
               admin.signature AS adminSignature
        FROM log_form logf
        JOIN user_details emp ON logf.employee_id = emp.ID
        LEFT JOIN user_details dh ON logf.dept_head_id = dh.ID
        LEFT JOIN user_details dean ON logf.dean_id = dean.ID
        LEFT JOIN user_details admin ON admin.department = 'admin'
        WHERE logf.log_id = ?";


    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $log_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    if ($result->num_rows > 0) {
        $logForm = $result->fetch_assoc();
    } else {
        echo "Log form not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "Log ID is not provided.";
    exit;
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel & Administrative Form</title>
    <link rel="stylesheet" href="assets/css/logform.css">

    <style>
        @media print {

            /* Hide navigation, buttons, and other non-printable elements */
            .no-print {
                display: none;
            }

            /* Adjust styles for a cleaner print */
            body {
                font-size: 8px;
            }
        }

        .signature {
            width: 100px;
            /* Adjust this width as needed */
            height: auto;
            /* Maintains the aspect ratio */
        }
    </style>
</head>

<body>
<div style="display: flex; justify-content: center; align-items: center; margin-top: 10px; margin-bottom: 10px;">
    <div style="background-color: white; width: 300px; padding: 10px; border-radius: 5px; display: flex; justify-content: center;">
        <button class="no-print" onclick="window.print()" style="background-color: maroon; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            Print this form
        </button>
    </div>
</div>
    <div class="form-container">
        <div class="header">
            <img src="assets/images/logo.png" class="logo" alt="University Logo">
            MANUEL S. ENVERGA UNIVERSITY FOUNDATION CANDELARIA, INC.<br>
            Quezon, Philippines
        </div>

        <div class="subheader">
            PERSONNEL & ADMINISTRATIVE OFFICE
        </div>

        <div class="form-group">
            <label>Date:</label>
            <?php echo $logForm['created_at']; ?>
        </div>

        <div class="form-group">
            <p>To the Personnel & Administrative Office:</p>
            <p>Please be informed that I failed to
                <label class="checkbox-label">
                    <input type="checkbox" <?php echo ($logForm['log_in_out'] === 'Log-In') ? 'checked' : ''; ?> disabled>
                    Log-in
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" <?php echo ($logForm['log_in_out'] === 'Log-Out') ? 'checked' : ''; ?>
                        disabled> Log-out
                </label>
                at the Biometric Machine on
            </p>
            <div class="form-inline">
                <div class="input-container">
                    <input type="text" class="input-field" value="<?php echo $logForm['log_date']; ?>" readonly>
                    <span class="label-below">(Date)</span>
                </div>

                <span>at</span>

                <div class="input-container">
                    <input type="text" class="input-field" value="<?php echo $logForm['log_time']; ?>" readonly>
                    <span class="label-below">(Time)</span>
                </div>

                <span>because</span>

                <div class="input-container" style="width: 250px;">
                    <input type="text" class="input-field" style="width: 100%;"
                        value="<?php echo $logForm['reason']; ?>" readonly>
                    <span class="label-below">(Reason)</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <p>I fully understand that as an employee of Enverga University, it is my obligation to abide by the
                policies of this situation. Thus, if I incur failures with my obligation to log-in/log-out at the
                Biometric machine which is the primary basis of my attendance, I may be subjected to disciplinary
                action, including dismissal.</p>
        </div>

        <!-- <div class="box">
            <p><strong>For Personnel & Administrative Office use:</strong></p>
            <p>No. of Failure to Log-in/Log-out within 90 days:</p>
            <p>Disciplinary Action:</p>
            <p>Verified by:</p>
            <p>Date:</p>
        </div> -->

        <div class="signature-section">

            <div class="signature-box">
                <p>
                    <img class="signature" src="../../uploads/<?php echo htmlspecialchars($logForm['empSignature']); ?>"
                        alt="Employee Signature"><br>
                    <strong><?php echo htmlspecialchars($logForm['empFirstName'] . ' ' . $logForm['empLastName']); ?></strong>

                </p>
                (Signature over printed name)
            </div>
            <div class="signature-box">
                Attested by:
                <p>
                    <img class="signature" src="../../uploads/<?php echo htmlspecialchars($logForm['deptHeadSignature']); ?>"
                        alt="Department Head Signature"><br>
                    <strong><?php echo htmlspecialchars($logForm['deptHeadFirstName'] . ' ' . $logForm['deptHeadLastName']); ?></strong>

                </p>
                (Department Head)
            </div>
        </div>

        <div class="footer">
            (This notice will be accepted only until the next working day of this incident.)
        </div>
    </div>
</body>

</html>