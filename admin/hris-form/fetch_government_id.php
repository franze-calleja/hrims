<?php
session_start();
include("../includes/database.php");


// Get the selected type from the query parameter
$type = $_GET['type'];

// Prepare the SQL query based on the selected type
$sql = "SELECT CONCAT(firstName, ' ', lastName) AS name, $type AS id FROM user_details";
$result = $conn->query($sql);

$employees = [];
if ($result->num_rows > 0) {
    // Fetch the data
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($employees);

$conn->close();
?>
