<?php
session_start();
include("../includes/database.php"); // Adjust the path to your database connection file

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

// Retrieve the leave_id from a GET or POST request
$leave_id = isset($_GET['leave_id']) ? $_GET['leave_id'] : null;

if ($leave_id) {
    // Prepare the SQL query
    $sql = "SELECT 
               lf.leave_id, 
               lf.employee_id, 
               lf.leave_type, 
               lf.dept_head_id, 
               lf.dean_id, 
               lf.start_date, 
               lf.end_date, 
               lf.place,
               lf.reason, 
               lf.status, 
               lf.admin_approval, 
               lf.dept_head_approval, 
               lf.dean_approval, 
               lf.created_at, 
               lf.updated_at, 
               lf.days,
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
        FROM leave_forms lf
        JOIN user_details emp ON lf.employee_id = emp.ID
        LEFT JOIN user_details dh ON lf.dept_head_id = dh.ID
        LEFT JOIN user_details dean ON lf.dean_id = dean.ID
        LEFT JOIN user_details admin ON admin.department = 'admin'
        WHERE lf.leave_id = ?";


    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    if ($result->num_rows > 0) {
        $leaveForm = $result->fetch_assoc();
    } else {
        echo "Leave form not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "Leave ID is not provided.";
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
    <title>Academic Request for Leave or Absence</title>
    <link rel="stylesheet" href="assets/css/leave-form.css">
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
        <div
            style="background-color: white; width: 770px; padding: 10px; border-radius: 5px; display: flex; justify-content: center;">
            <button class="no-print" onclick="window.print()"
                style="background-color: maroon; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Print this form
            </button>
        </div>
    </div>

    <div class="form-container">

        <table>
            <tr>
                <td colspan="4" rowspan="2" class="no-border header-section">
                    <img src="assets/images/logo.png" alt="Logo">
                    <div>
                        Manuel S. Enverga University Foundation Candelaria, Inc.Quezon, Philippines
                    </div>
                </td>


                <td colspan="2" rowspan="2"><strong>Document Code & Title: ARFLA</strong></td>
                <td rowspan="2"><strong>Date: Jan. 2014</strong></td>
                <td rowspan="2"><strong>Revision: 0</strong></td>


            </tr>
            <tr>
                <td colspan="4"> <strong>Section: No. & Title: 1.0 REQUEST FOR LEAVE OR ABSENCE</strong></td>
            </tr>

            <tr>
                <td colspan="2"><strong>CONTROLLED QUALITY DOCUMENTATION</strong></td>
                <td><strong>Control Number: <?php echo $leaveForm['leave_id']; ?></strong></td>
                <td><strong>Prepared by: JGA</strong></td>
                <td><strong>Approved by: DS</strong></td>
                <td><strong>Page: 1of1</strong></td>
            </tr>
        </table>


        <p style="text-align: center;"> <strong> ACADEMIC REQUEST FOR LEAVE OR ABSENCE</strong></p>



        <table>
            <tr>
                <td colspan="2">1. Name:
                    <strong><?php echo htmlspecialchars($leaveForm['empFirstName'] . ' ' . $leaveForm['empLastName']); ?></strong>
                </td>
                <td colspan="2">2. Designation: <strong><?php echo $leaveForm['empJobTitle']; ?></strong></td>
            </tr>
            <tr>
                <td>3. Department: <strong><?php echo $leaveForm['empDepartment']; ?></strong></td>
                <td colspan="3">4. Department Head Reporting to:
                    <strong><?php echo htmlspecialchars($leaveForm['deptHeadFirstName'] . ' ' . $leaveForm['deptHeadLastName']); ?></strong>
                </td>
            </tr>
        </table>


        <table>
            <tr>
                <td colspan="4"><strong>5. TYPE OF LEAVE/ABSENCE</strong> (Check the appropriate boxes and indicate the
                    time frame)</td>
            </tr>
            <tr>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Duration</th>
            </tr>

            <!-- Vacation Leave -->
            <tr>
                <td><input type="checkbox" name="vacation" <?php echo ($leaveForm['leave_type'] == 'Vacation') ? 'checked' : ''; ?>> Vacation</td>
                <td><input type="date" name="vacation_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Vacation') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="vacation_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Vacation') ? $leaveForm['end_date'] : ''; ?>">
                </td>
                <td><input type="text" name="vacation_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Vacation') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

            <!-- Sick Leave -->
            <tr>
                <td><input type="checkbox" name="sick" <?php echo ($leaveForm['leave_type'] == 'Sick') ? 'checked' : ''; ?>> Sick</td>
                <td><input type="date" name="sick_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Sick') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="sick_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Sick') ? $leaveForm['end_date'] : ''; ?>"></td>
                <td><input type="text" name="sick_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Sick') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

            <!-- Birthday Leave -->
            <tr>
                <td><input type="checkbox" name="birthday" <?php echo ($leaveForm['leave_type'] == 'Birthday') ? 'checked' : ''; ?>> Birthday</td>
                <td><input type="date" name="birthday_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Birthday') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="birthday_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Birthday') ? $leaveForm['end_date'] : ''; ?>">
                </td>
                <td><input type="text" name="birthday_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Birthday') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

            <!-- Solo Parent Leave -->
            <tr>
                <td><input type="checkbox" name="solo_parent" <?php echo ($leaveForm['leave_type'] == 'Solo parent') ? 'checked' : ''; ?>> Solo Parent</td>
                <td><input type="date" name="solo_parent_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Solo parent') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="solo_parent_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Solo parent') ? $leaveForm['end_date'] : ''; ?>">
                </td>
                <td><input type="text" name="solo_parent_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Solo parent') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

            <!-- Maternity/Paternity Leave -->
            <tr>
                <td><input type="checkbox" name="maternity" <?php echo ($leaveForm['leave_type'] == 'Maternity/Paternity') ? 'checked' : ''; ?>> Maternity/Paternity</td>
                <td><input type="date" name="maternity_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Maternity/Paternity') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="maternity_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Maternity/Paternity') ? $leaveForm['end_date'] : ''; ?>">
                </td>
                <td><input type="text" name="maternity_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Maternity/Paternity') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

            <!-- Leave Without Pay -->
            <tr>
                <td><input type="checkbox" name="leave_without_pay" <?php echo ($leaveForm['leave_type'] == 'Leave Without Pay') ? 'checked' : ''; ?>> Leave without pay</td>
                <td><input type="date" name="leave_without_pay_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Leave Without Pay') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="leave_without_pay_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Leave Without Pay') ? $leaveForm['end_date'] : ''; ?>">
                </td>
                <td><input type="text" name="leave_without_pay_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Leave Without Pay') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

            <!-- Long Leave -->
            <tr>
                <td><input type="checkbox" name="long_leave" <?php echo ($leaveForm['leave_type'] == 'Long Leave') ? 'checked' : ''; ?>> Long Leave (further studies, travel)</td>
                <td><input type="date" name="long_leave_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Long Leave') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="long_leave_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Long Leave') ? $leaveForm['end_date'] : ''; ?>">
                </td>
                <td><input type="text" name="long_leave_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Long Leave') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

            <!-- Other Leave -->
            <tr>
                <td><input type="checkbox" name="other" <?php echo ($leaveForm['leave_type'] == 'Other') ? 'checked' : ''; ?>> Other, Please specify: <input type="text" name="other_specify" placeholder="Specify"></td>
                <td><input type="date" name="other_from"
                        value="<?php echo ($leaveForm['leave_type'] == 'Other') ? $leaveForm['start_date'] : ''; ?>">
                </td>
                <td><input type="date" name="other_to"
                        value="<?php echo ($leaveForm['leave_type'] == 'Other') ? $leaveForm['end_date'] : ''; ?>"></td>
                <td><input type="text" name="other_duration"
                        value="<?php echo ($leaveForm['leave_type'] == 'Other') ? $leaveForm['days'] : ''; ?>"
                        placeholder="Days"></td>
            </tr>

        </table>


        <table>
            <tr>
                <td>6. Reasons for leave/absence: <strong><?php echo $leaveForm['reason']; ?></strong></td>
                <td>7. Place where to spend this leave: <strong><?php echo $leaveForm['place']; ?></strong></td>
            </tr>
        </table>

        <table>
            <tr>
                <td colspan="4">
                    <strong>8. CERTIFICATION:</strong> I certify that the leave/absence requested above is for the
                    purpose(s) indicated. I understand that I must comply with the MSEUF procedures for requesting
                    leave/approved absence (and provide additional documentation to support this application, including
                    medical certification if required) and that falsification of information on this form may be grounds
                    for disciplinary action, including dismissal.
                </td>
            </tr>
            <tr>
                <td style="border: 1px !important;display: flex; justify-content: flex-start; align-items: center;">
                    <strong>9A. Employee's Signature:</strong>
                    <img class="signature"
                        src="../../uploads/<?php echo htmlspecialchars($leaveForm['empSignature']); ?>"
                        alt="Employee Signature">
                </td>
                <td>
                    <strong>9B. Date Signed:</strong>
                    <input type="date" name="date_signed"
                        value="<?php echo htmlspecialchars(substr($leaveForm['created_at'], 0, 10)); ?>">
                </td>

            </tr>

        </table>

        <table>
            <tr>
                <td colspan="5" class="approval-header"><strong>VERIFICATION (to be filled by the Personnel
                        Office)</strong></td>
            </tr>
            <tr>
                <td colspan="5"><strong>10. Remaining number of the Annual Leave Benefit prior to this request:</strong>
                </td>
            </tr>
            <tr>
                <td>Vacation: <input type="text" name="vacation_remaining"></td>
                <td>Sick: <input type="text" name="sick_remaining"></td>
                <td>Solo Parent: <input type="text" name="solo_parent_remaining"></td>
                <td>Date verified: <input type="date" name="date_verified"></td>
                <td>Verified by: <input type="text" name="verified_by"></td>
            </tr>

            <tr>
                <td colspan="3"><strong>11. Compliance of Requirement</strong></td>
                <td><input type="checkbox" name="completed"> Completed</td>
                <td><input type="checkbox" name="not_completed"> Not Completed</td>
            </tr>

            <tr>
                <td colspan="5"><strong>12. Compensatory Leave/Absence Unpaid Leave/Absence</strong></td>
            </tr>
            <tr>
                <td>From: <input type="date" name="compensatory_from"></td>
                <td>To: <input type="date" name="compensatory_to"></td>
                <td colspan="3">Duration: <input type="text" name="compensatory_duration"></td>
            </tr>
        </table>

        <p style="font-size: 10px; text-align: center;">You must seek approval for leaves, other than sick and long
            leaves, 3 days prior to
            your first day of absence.</p>

        <div class="approval-section">
            <div class="approval-header">RECOMMENDING APPROVAL</div>
            <div class="approval-content">
                <div class="approval-column">
                    <p>13 A. Signature:</p>
                    <img class="signature"
                        src="../../uploads/<?php echo htmlspecialchars($leaveForm['deptHeadSignature']); ?>"
                        alt="Department Head Signature">
                    <p><strong><?php echo htmlspecialchars($leaveForm['deptHeadFirstName'] . ' ' . $leaveForm['deptHeadLastName']); ?></strong>
                        <br>Department Head
                    </p>

                    <p style="text-align: center;"><strong>Date:</strong>
                        <input type="date" class="date-signed" style="text-align: center; margin-top: 5px;"
                            value="<?php echo htmlspecialchars(substr($leaveForm['updated_at'], 0, 10)); ?>">
                    </p>
                </div>
                <div class="approval-column">
                    <p>13. B. Signature</p>
                    <img class="signature" src="assets/images/admin_signature.png" alt="Admin Signature">
                    <p><strong>Gaspar Hector C. Tapire</strong><br>Administrative
                        / Personnel Officer
                    </p>

                    <p style="text-align: center;"><strong>Date:</strong>
                        <input type="date" class="date-signed" style="text-align: center; margin-top: 5px;"
                            value="<?php echo htmlspecialchars(substr($leaveForm['updated_at'], 0, 10)); ?>">
                    </p>
                </div>
            </div>
        </div>

        <div class="approval-section">
            <div class="approval-header">FINAL APPROVAL</div>
            <div class="approval-content">
                <div class="approval-column">
                    <p>14.A For Non-Acad Employee</p>
                    <p>(For Academic Employee)</p>
                    <img class="signature"
                        src="../../uploads/<?php echo htmlspecialchars($leaveForm['deanSignature']); ?>"
                        alt="Dean Signature">
                    <p><strong><?php echo htmlspecialchars($leaveForm['deanFirstName'] . ' ' . $leaveForm['deanLastName']); ?></strong><br>Dean
                        of Studies
                    </p>
                    <p style="text-align: center;"><strong>Date:</strong>
                        <input type="date" class="date-signed" style="text-align: center; margin-top: 5px;"
                            value="<?php echo htmlspecialchars(substr($leaveForm['updated_at'], 0, 10)); ?>">
                    </p>
                </div>
                <div class="approval-column">
                    <p>14.C. (For Officer & for those filing long leave of absence)</p>
                    <p><strong>NAILA E. LEVERIZA</strong></p>
                    <p>President/ COO</p>
                    <p>Date: ____________________</p>
                </div>
            </div>
            <div class="approval-column">
                <p>14.B. (For Officer & for those filing long leave of absence) (For Non-Acad Employee) (For Academic
                    Employee)</p>
                <div class="finalapp">
                    <div>
                        <p><strong>CELSO D. JABALLA</strong></p>
                        <p>VP For External Relation/</p>
                        <p>Coordinator for Affiliate School</p>
                        <p>Date: ____________________</p>
                    </div>
                    <div>
                        <p><strong>DARIO R. OPISTAN</strong></p>
                        <p>VP for Administration</p>
                        <p>Date: ____________________</p>
                    </div>
                    <div>
                        <p><strong>BENILDA N. VILLENAS, PhD</strong></p>
                        <p>Senior Vice President</p>
                        <p>VP For Academics & Research</p>
                        <p>Date: ____________________</p>
                    </div>
                </div>
            </div>
        </div>

    </div>




</body>

</html>