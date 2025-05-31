<?php
// Create a new file named process_add_employee.php
session_start();
include("../includes/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $employeeId = mysqli_real_escape_string($conn, $_POST['employeeID']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    // Check if employee ID already exists
    $check_query = "SELECT ID FROM employee_validation WHERE ID = '$employeeId'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Employee ID already exists!'); window.location.href='employee_details.php';</script>";
        exit();
    }

    // Insert new employee
    $insert_query = "INSERT INTO employee_validation (ID, firstName, lastName, department) 
                    VALUES ('$employeeId', '$firstName', '$lastName', '$department')";

    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('Employee added successfully!'); window.location.href='employee_details.php';</script>";
    } else {
        echo "<script>alert('Error adding employee: " . mysqli_error($conn) . "'); window.location.href='employee_details.php';</script>";
    }

    mysqli_close($conn);
    exit();
}
?>
