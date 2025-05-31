<?php
session_start();
include("../includes/database.php"); // Adjust the path to your database connection file

// Fetch department distribution for the employee chart
$departmentChartData = [];
$sql = "SELECT 
          CASE 
            WHEN department IN ('Elementary', 'Highschool', 'College') THEN 'Faculty'
            WHEN department IN ('Dean of Studies', 'Department Head') THEN 'Dean'
            ELSE department 
          END AS department, 
          COUNT(*) as count 
        FROM user_details 
        GROUP BY 
          CASE 
            WHEN department IN ('Elementary', 'Highschool', 'College') THEN 'Faculty'
            WHEN department IN ('Dean of Studies', 'Department Head') THEN 'Dean'
            ELSE department 
          END";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
  $departmentChartData[] = ['label' => $row['department'], 'count' => $row['count']];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Report</title>
    <link rel="stylesheet" href="assets/css/print-form.css">
</head>
<body>

<div class="month-selection">
    <button class="print-button" onclick="window.print();">Print Report</button>
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
        </tr>
        <tr>
            <td colspan="4"><strong>MSEUCI Human Resource Management System</strong></td>
            <td rowspan="2"><strong>Date: <?php echo date("F j, Y"); ?></strong></td>
        </tr>
        
        <tr>
            <td colspan="2"><strong>Latest Employee Report as of <?php echo date("F j, Y"); ?></strong></td>
        </tr>
    </table>

    <div class="content">
        <p style="text-align: center;"><strong>Employee Report</strong></p>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Employee</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departmentChartData as $data): ?>
                <tr>
                    <td><?php echo $data['label']; ?></td>
                    <td><?php echo $data['count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Employee Chart</h3>
        <div id="chart-container">
            <canvas id="employeeChart" height="400"></canvas>
        </div>
       
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare the data for the chart
const departmentChartData = {
    labels: <?php echo json_encode(array_column($departmentChartData, 'label')); ?>,
    data: <?php echo json_encode(array_column($departmentChartData, 'count')); ?>,
    backgroundColors: ['#800000', '#FF0202', '#FC5858', '#FFB7B7']
};

// Function to create the employee chart
function createEmployeeChart() {
    const ctx = document.getElementById('employeeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: departmentChartData.labels,
            datasets: [{
                label: 'Number of Employees',
                data: departmentChartData.data,
                backgroundColor: departmentChartData.backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)',
                        lineWidth: 1
                    },
                    ticks: {
                        font: {
                            size: 14,
                            weight: 'bold'
                        },
                        beginAtZero: true
                    }
                }
            }
        }
    });
}

// Call the function to create the chart
createEmployeeChart();
</script>
</body>
</html>
