<?php
// get_form_counts.php
header('Content-Type: application/json');
include("../includes/database.php");

$month = isset($_GET['month']) ? intval($_GET['month']) : date('n') - 1;
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$monthNum = $month + 1;

// Initialize counts array
$counts = [
    'Leave Forms' => 0,
    'Log Forms' => 0,
    'Make Up Forms' => 0,
    'Travel Order Forms' => 0,
    'Travel Order Candelaria' => 0
];

// Query for each form type
$formTables = [
    'Leave Forms' => 'leave_forms',
    'Log Forms' => 'log_form',
    'Make Up Forms' => 'make_up_forms',
    'Travel Order Forms' => 'travel_order_forms',
    'Travel Order Candelaria' => 'travel_order_candelaria_forms'
];

foreach ($formTables as $formName => $tableName) {
    $sql = "SELECT COUNT(*) as count FROM $tableName WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $monthNum, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $counts[$formName] = $result->fetch_assoc()['count'];
    $stmt->close();
}

$conn->close();

echo json_encode([
    'labels' => array_keys($counts),
    'data' => array_values($counts)
]);
?>