<?php
session_start();
include("../includes/database.php"); // Adjust the path to your database connection file

// Fetch data for Employee Demographic Chart
$employeeDemographicQuery = "SELECT sex, COUNT(*) as count FROM user_details GROUP BY sex";
$employeeDemographicResult = $conn->query($employeeDemographicQuery);

$employeeDemographicData = [];
while ($row = $employeeDemographicResult->fetch_assoc()) {
  $employeeDemographicData[] = [
    'gender' => $row['sex'],
    'count' => (int) $row['count']
  ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Report</title>
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
                    <th>Gender</th>
                    <th>Employee</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employeeDemographicData as $data): ?>
                <tr>
                    <td><?php echo $data['gender']; ?></td>
                    <td><?php echo $data['count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Employee Chart</h3>
        <div id="chart-container">
            <canvas id="EmployeeDemographicChart" height="400"></canvas>
        </div>
       
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare the data for the chart
    // Data for the employee chart
    const EmployeeDemographicData = {
      labels: <?php echo json_encode(array_column($employeeDemographicData, 'gender')); ?>,
      data: <?php echo json_encode(array_column($employeeDemographicData, 'count')); ?>,
      backgroundColors: ['#800000', '#FFB7B7'] // Custom background colors
    };

    function createEmployeeDemographicChart() {
      const ctx = document.getElementById('EmployeeDemographicChart').getContext('2d');
      const myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: EmployeeDemographicData.labels,
          datasets: [{
            label: 'Employee Demographic Distribution',
            data: EmployeeDemographicData.data,
            backgroundColor: EmployeeDemographicData.backgroundColors,
            borderColor: 'white', // Add border color for better contrast
            borderWidth: 4, // Increase border width for visibility
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false, // Disable aspect ratio to fit the container
          cutout: '70%', // Adjust the cutout percentage to control the inner radius
          plugins: {
            legend: {
              display: true,
              position: 'right',
              labels: {
                font: {
                  size: 14,
                  weight: 'bold'
                },
              }
            }
          }
        }
      });
    }
// Call the function to create the chart
createEmployeeDemographicChart();
</script>
</body>
</html>
