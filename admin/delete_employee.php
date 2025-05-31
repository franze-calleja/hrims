<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../includes/database.php';

// 1. Check connection
if ($conn->connect_error) {
    $_SESSION['error'] = 'DB connection failed: ' . $conn->connect_error;
    header('Location: employee_details.php');
    exit;
}

// 2. Grab & sanitize the string ID
//    (use FILTER_SANITIZE_STRING or FILTER_UNSAFE_RAW, depending on your PHP version)
$employee_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
if (!$employee_id) {
    $_SESSION['error'] = 'Invalid or missing employee ID.';
    header('Location: employee_details.php');
    exit;
}

// 3. Prepare / bind as STRING ("s")
$stmt = $conn->prepare("
    UPDATE user_details
       SET isDelete = 1
     WHERE ID = ?
");
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("s", $employee_id);

// 4. Execute & set flash message
if ($stmt->execute()) {
    $_SESSION['message'] = "Employee “{$employee_id}” deleted successfully.";
} else {
    $_SESSION['error'] = "Error deleting employee: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: employee_details.php");
exit();
?>