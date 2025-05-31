<?php
session_start();
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../includes/database.php"); // Adjust the path to your database connection file

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

// Retrieve the makeup_class_id from a GET or POST request
$makeup_class_id = isset($_GET['makeup_class_id']) ? $_GET['makeup_class_id'] : null;

if ($makeup_class_id) {
    // Prepare the SQL query
    $sql = "SELECT mc.makeup_class_id, mc.subject, mc.regular_class_date, 
                    mc.regular_class_time, mc.regular_class_room, mc.makeup_class_date, mc.reason,
                    mc.makeup_class_time, mc.makeup_class_room, mc.status, mc.reason, mc.dept_head_id, mc.dean_id, mc.admin_approval, mc.dept_head_approval, 
                   mc.dean_approval, mc.created_at, mc.updated_at,
                   emp.firstName AS empFirstName, emp.lastName AS empLastName, emp.department AS empDepartment, emp.jobTitle AS empJobTitle, emp.signature AS empSignature,
                   dh.firstName AS deptHeadFirstName, dh.lastName AS deptHeadLastName, dh.signature AS deptHeadSignature,
                   dean.firstName AS deanFirstName, dean.lastName AS deanLastName, dean.signature AS deanSignature,
                   admin.firstName AS adminFirstName, 
               admin.lastName AS adminLastName, 
               admin.signature AS adminSignature
            FROM make_up_forms mc
            JOIN user_details emp ON mc.employee_id = emp.ID
            LEFT JOIN user_details dh ON mc.dept_head_id = dh.ID
            LEFT JOIN user_details dean ON mc.dean_id = dean.ID
            LEFT JOIN user_details admin ON admin.department = 'admin'
            WHERE mc.makeup_class_id = ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $makeup_class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    if ($result->num_rows > 0) {
        $makeUpClassForm = $result->fetch_assoc();
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
    <title>Permit for Make-up Classes</title>
    <link rel="stylesheet" href="assets/css/make-up-form.css">

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
            style="background-color: white; width: 300px; padding: 10px; border-radius: 5px; display: flex; justify-content: center;">
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
                        Manuel S. Enverga University Foundation Candelaria, Inc. Quezon, Philippines
                    </div>
                </td>
                <td colspan="2" rowspan="2"><strong>Document Code & Title: PF-MUC</strong></td>
                <td rowspan="2"><strong>Date: November 2012</strong></td>
                <td rowspan="2"><strong>Revision: 0</strong></td>
            </tr>
            <tr>
                <td colspan="4"><strong>Section No. & Title: 1.0 PERMIT FOR MAKE-UP CLASSES</strong></td>
            </tr>
            <tr>
                <td colspan="2"><strong>CONTROLLED QUALITY DOCUMENT</strong></td>
                <td><strong>Control Number: <?php echo $makeUpClassForm['makeup_class_id']; ?></strong></td>
                <td><strong>Prepared by: JGA</strong></td>
                <td><strong>Approved by: DS</strong></td>
                <td><strong>Page: 1 of 1</strong></td>
            </tr>
        </table>



        <div class="table-content">
            <p>Date of filing: <strong><?php echo $makeUpClassForm['created_at']; ?></strong></p>
            <table>
                <tr>
                    <th colspan="4">REGULAR SCHEDULE</th>
                    <th colspan="4">MAKE-UP CLASSES</th>
                </tr>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Subject</th>
                </tr>
                <tr>
                    <td><strong><?php echo $makeUpClassForm['regular_class_date']; ?></strong></td>
                    <td><strong><?php echo $makeUpClassForm['regular_class_time']; ?></strong></td>
                    <td><strong><?php echo $makeUpClassForm['regular_class_room']; ?></strong></td>
                    <td><strong><?php echo $makeUpClassForm['subject']; ?></strong></td>
                    <td><strong><?php echo $makeUpClassForm['makeup_class_date']; ?></strong></td>
                    <td><strong><?php echo $makeUpClassForm['makeup_class_time']; ?></strong></td>
                    <td><strong><?php echo $makeUpClassForm['makeup_class_room']; ?></strong></td>
                    <td><strong><?php echo $makeUpClassForm['subject']; ?></strong></td>
                </tr>


            </table>
        </div>


        <div class="form-content">

            <p>REASON/S:</p>
            <p><?php echo $makeUpClassForm['reason']; ?></p>
            <input type="text" style="width: 100%; margin-bottom: 10px;">

            <div class="signature-section">
                <div>
                    <p>Requested by:</p>
                    <p><img class="signature"
                            src="../../uploads/<?php echo htmlspecialchars($makeUpClassForm['empSignature']); ?>"
                            alt="Employee Signature"></p>
                    <strong><?php echo htmlspecialchars($makeUpClassForm['empFirstName'] . ' ' . $makeUpClassForm['empLastName']); ?></strong>
                    <!-- <div class="signature-line"> </div> -->
                    <p>Instructor<br>Signature over printed name</p>
                </div>
                <div>
                    <p>This is to certify that the students and rooms are available on<br>the scheduled date & time of
                        the abovementioned make-up<br>class/classes.</p>
                    <p><img class="signature"
                            src="../../uploads/<?php echo htmlspecialchars($makeUpClassForm['deptHeadSignature']); ?>"
                            alt="Employee Signature"></p>
                    <strong><?php echo htmlspecialchars($makeUpClassForm['deptHeadFirstName'] . ' ' . $makeUpClassForm['deptHeadLastName']); ?></strong>
                    <!-- <div class="signature-line"></div> -->
                    <p>Department Chair</p>
                </div>
            </div>

            <div class="approval-section">
                <div>
                    <p>Approved by:</p>
                    <p><img class="signature"
                            src="../../uploads/<?php echo htmlspecialchars($makeUpClassForm['deanSignature']); ?>"
                            alt="Employee Signature"></p>
                    <p><strong><?php echo htmlspecialchars($makeUpClassForm['deanFirstName'] . ' ' . $makeUpClassForm['deanLastName']); ?></strong><br>Dean
                        of Studies</p>
                </div>
                <div>
                    <p>Verified by:</p>
                    <p><img class="signature" src="assets/images/admin_signature.png" alt="Employee Signature"></p>
                    <p><strong>Gaspar Hector Tapire</strong><br>Administrative /Personnel Officer</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>