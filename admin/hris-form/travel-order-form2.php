<?php
session_start();
include("../includes/database.php"); // Adjust the path to your database connection file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

// Retrieve the travel_order_id from a GET or POST request
$travel_order_id = isset($_GET['travel_order_id']) ? $_GET['travel_order_id'] : null;

if ($travel_order_id) {
    // Prepare the SQL query
    $sql = "SELECT 
    `to`.travel_order_id, 
    `to`.employee_id, 
    `to`.dept_head_id, 
    `to`.dean_id, 
    `to`.destination,
    `to`.start_date, 
    `to`.return_date, 
    `to`.purpose,
    `to`.cash_advance, 
    `to`.report_date,
    `to`.status, 
    `to`.admin_approval, 
    `to`.dept_head_approval, 
    `to`.dean_approval, 
    `to`.created_at, 
    `to`.updated_at, 
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
FROM travel_order_forms `to`
JOIN user_details emp ON `to`.employee_id = emp.ID
LEFT JOIN user_details dh ON `to`.dept_head_id = dh.ID
LEFT JOIN user_details dean ON `to`.dean_id = dean.ID
LEFT JOIN user_details admin ON admin.department = 'admin'
WHERE `to`.travel_order_id = ?";


    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $travel_order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    if ($result->num_rows > 0) {
        $travelOrderForm = $result->fetch_assoc();
    } else {
        echo "Travel Order form not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "Travel Order ID is not provided.";
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
    <div style="background-color: white; width: 300px; padding: 10px; border-radius: 5px; display: flex; justify-content: center;">
        <button class="no-print" onclick="window.print()" style="background-color: maroon; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            Print this form
        </button>
    </div>
</div>
    <div class="form-container">
        <table>
            <tr>
                <td rowspan="2" class="header-text">
                    <div > <img class="logo" src="assets/images/logo.png"  alt="Logo"></div>
                    MANUEL S. ENVERGA UNIVERSITY FOUNDATION - CANDELARIA, INC.<br>
                    Candelaria, Quezon<br>
                    <br>
                    AFFILIATE SCHOOL<br>
                    QUALITY FORM
                </td>
                <td class="document-info">
                    <strong>Document Code:  <?php echo $travelOrderForm['travel_order_id']; ?></strong><br>
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

        <div class="sideMargin">

        <h1>TRAVEL ORDER</h1>

        <div class="form-field">
            <label>Date:</label>
            <input type="text" value=" <?php echo $travelOrderForm['created_at']; ?>">
        </div>

        <p>The President<br>Manuel S. Enverga University Foundation</p>

        <p>Permission is hereby requested to go to <input type="text" style="width: 300px;" value="<?php echo $travelOrderForm['destination']; ?>" readonly> leaving Candelaria, Quezon on <input type="text" style="width: 100px;" value="<?php echo $travelOrderForm['start_date']; ?>" readonly> and returning on <input type="text" style="width: 100px;" value="<?php echo $travelOrderForm['return_date']; ?>" readonly> for the purpose of <input type="text" style="width: 300px;"  value="<?php echo $travelOrderForm['purpose']; ?>" readonly>.</p>

        <p>Authority is also requested to withdraw a cash advance of Php <input type="text" style="width: 100px;" value="<?php echo $travelOrderForm['cash_advance']; ?>" readonly> to cover the following:</p>

        <p>A complete report in accordance with the existing regulations including tickets, invoices/receipts pertinent papers and other data the administration may request shall be made until <input type="text" style="width: 100px;"  value="<?php echo $travelOrderForm['report_date']; ?>" readonly>. In case of failure to make this report at the specific time the cash advance shall be charged against my salary.</p>

        <div class="form-field">
            <label>Applicant's Name and Signature:</label>
            <p><img  class="signature" src="../../uploads/<?php echo htmlspecialchars($travelOrderForm['empSignature']); ?>" alt="Employee Signature" ><br><strong><?php echo htmlspecialchars($travelOrderForm['empFirstName'] . ' ' . $travelOrderForm['empLastName']); ?></strong></p>
        </div>

        <div class="form-field">
            <label>Position:</label>
            <input type="text" value="<?php echo $travelOrderForm['empJobTitle']; ?>">
        </div>

        </div>

        <div class="signatures">
            <div class="signature">
                <strong>Indorsed by:</strong>
                <p><img  class="signature" src="../../uploads/<?php echo htmlspecialchars($travelOrderForm['deanSignature']); ?>" alt="Employee Signature" ><br>
                <strong><?php echo htmlspecialchars($travelOrderForm['deanFirstName'] . ' ' . $travelOrderForm['deanLastName']); ?></strong><br>Acting Dean of Studies
            </p>
            
            </div>
            <div class="signature">
                <strong>Recommending Approval:</strong>
                <div class="signature-line"></div>
                <strong>CELSO D. JABALLA</strong><br>
                VP for External Relations/<br>
                Coordinator for Affiliate Schools
            </div>
            <div class="signature">
                <strong><br></strong>
                <div class="signature-line"></div>
                <strong>DARIO R. OPISTAN</strong><br>
                Vice President for Administration
            </div>
            <div class="signature">
                <strong><br></strong>
                <div class="signature-line"></div>
                <strong>BENILDA N. VILLENAS, PhD</strong><br>
                Senior Vice President /<br>
                VP For Academics & Research
            </div>
            <div class="signature">
                <strong>Approved by:</strong>
                <div class="signature-line"></div>
                <strong>MADAM NAILA E. LEVERIZA</strong><br>
                University President /<br>
                Chief Operating Officer
            </div>
        </div>

        <div class="sideMargin">
        
            <h2 style="font-size: 15px; margin-top: 3rem;">Travel Order List</h2>
            <table >
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Signature</th>
                </tr>
               
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
               
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td ></td>
                    <td></td>
                    <td></td>
                </tr>
               
               
            </table>
     
        </div>
        
    </div>
</body>
</html>