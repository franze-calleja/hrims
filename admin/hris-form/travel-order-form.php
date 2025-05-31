<?php
session_start();
include("../includes/database.php"); // Adjust the path to your database connection file

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

// Retrieve the travel_order_id from a GET or POST request
$travel_order_id = isset($_GET['travel_order_id']) ? $_GET['travel_order_id'] : null;

if ($travel_order_id) {
    // Prepare the SQL query
    $sql = "SELECT toc.travel_order_id, toc.employee_id, toc.destination, toc.purpose, toc.dept_head_id, toc.dean_id, toc.travel_start_date, 
                   toc.travel_time, toc.return_time, toc.status, toc.admin_approval, toc.dept_head_approval, 
                   toc.dean_approval, toc.created_at, toc.updated_at,
                   emp.firstName AS empFirstName, emp.lastName AS empLastName, emp.department AS empDepartment, emp.jobTitle AS empJobTitle, emp.signature AS empSignature,
                   dh.firstName AS deptHeadFirstName, dh.lastName AS deptHeadLastName, dh.signature AS deptHeadSignature,
                   dean.firstName AS deanFirstName, dean.lastName AS deanLastName, dean.signature AS deanSignature,
                   admin.firstName AS adminFirstName, 
               admin.lastName AS adminLastName, 
               admin.signature AS adminSignature
            FROM travel_order_candelaria_forms toc
            JOIN user_details emp ON toc.employee_id = emp.ID
            LEFT JOIN user_details dh ON toc.dept_head_id = dh.ID
            LEFT JOIN user_details dean ON toc.dean_id = dean.ID
            LEFT JOIN user_details admin ON admin.department = 'admin'
            WHERE toc.travel_order_id = ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $travel_order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    if ($result->num_rows > 0) {
        $travelOrderCForm = $result->fetch_assoc();
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
    <title>Travel Order Form</title>
    <link rel="stylesheet" href="assets/css/travel-order.css">

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
                <td rowspan="2" class="header-text">
                    <div> <img class="logo" src="assets/images/logo.png" alt="Logo"></div>
                    MANUEL S. ENVERGA UNIVERSITY FOUNDATION - CANDELARIA, INC.<br>
                    Candelaria, Quezon<br>
                    <br>
                    AFFILIATE SCHOOL<br>
                    QUALITY FORM
                </td>
                <td class="document-info">
                    <strong>Document Code: <?php echo $travelOrderCForm['travel_order_id']; ?></strong><br>
                    Document Title: Travel Order<br>
                    Page No. 1 of 1<br>
                    Revision No. 1<br>
                    Effectivity: November 2016
                </td>
            </tr>
            <tr>
                <td class="document-info">
                    Prepared by: PAAS<br>
                    Reviewed by: QMR<br>
                    Approved by: President
                </td>
            </tr>
        </table>

        <h1>TRAVEL ORDER</h1>

        <div class="form-field">
            <label>Date: </label>
            <input type="text" value="<?php echo date('Y-m-d', strtotime($travelOrderCForm['created_at'])); ?>"
                readonly>

        </div>

        <p>The Dean of Studies<br>Manuel S. Enverga University Foundation</p>

        <p>Permission is hereby requested to go to <input type="text" style="width: 250px;"
                value="<?php echo $travelOrderCForm['destination']; ?>">, leaving Leaving MSEUFCI - Candelaria on
            <input type="text" style="width: 100px;"
                value="<?php echo date('Y-m-d', strtotime($travelOrderCForm['travel_start_date'])); ?>" readonly> at
            <input type="text" style="width: 100px;"
                value="<?php echo date('h:i A', strtotime($travelOrderCForm['travel_time'])); ?>" readonly> and
            returning to
            <input type="text" style="width: 100px;"
                value="<?php echo date('h:i A', strtotime($travelOrderCForm['return_time'])); ?>" readonly>for the
            purpose of
            <input type="text" style="width: 100px;" value="<?php echo $travelOrderCForm['purpose']; ?>">.
        </p>

        <div class="form-field">
            <label>Applicant's Name and Signature:</label>
            <p><img class="signature"
                    src="../../uploads/<?php echo htmlspecialchars($travelOrderCForm['empSignature']); ?>"
                    alt="Employee Signature"><br><strong><?php echo htmlspecialchars($travelOrderCForm['empFirstName'] . ' ' . $travelOrderCForm['empLastName']); ?></strong>
            </p>

            <p></p>

        </div>

        <div class="form-field">
            <label>Position:</label>
            <p><strong><?php echo $travelOrderCForm['empJobTitle']; ?><strong></strong></p>

        </div>

        <div class="signatures">
            <div class="signature">
                <strong>Indorsed by:</strong><br><br>

                <input type="text" style="width: 100px;">
            </div>
            <div class="signature">
                <strong>Recommending Approval:</strong>
                <p><img class="signature" src="assets/images/admin_signature.png" alt="Employee Signature"></p>

                <p>Gaspar Hector C. Tapire</strong><br>Administrative Personnel</p>

            </div>
            <div class="signature">
                <strong>Approved by:</strong>
                <p><img class="signature"
                        src="../../uploads/<?php echo htmlspecialchars($travelOrderCForm['deanSignature']); ?>"
                        alt="Employee Signature"></p>

                <p><strong><?php echo htmlspecialchars($travelOrderCForm['deanFirstName'] . ' ' . $travelOrderCForm['deanLastName']); ?></strong><br>Acting
                    Dean of Studies</p>

            </div>

        </div>


    </div>
</body>

</html>