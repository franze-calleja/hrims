<?php
if (isset($_GET['id'])) {
    $employeeID = $_GET['id'];
    header("Location: employee_view.php?id=$employeeID");
    exit();
} else {
    echo "No employee ID provided.";
}
?>
