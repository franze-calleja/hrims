<?php
session_start();
include("../includes/database.php"); // Adjust the path to your database connection file

// Fetch faculty job title distribution for the faculty chart
$facultyChartData = [];
$sql = "SELECT department, COUNT(*) as count 
        FROM user_details 
        WHERE department IN ('Elementary', 'Highschool', 'College')
          AND department NOT IN ('Admin', 'Department Head', 'Dean of Studies') 
        GROUP BY department";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
  $facultyChartData[] = ['label' => $row['department'], 'count' => $row['count']];
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
                    <th>Faculty</th>
                    <th>Employee</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facultyChartData as $data): ?>
                <tr>
                    <td><?php echo $data['label']; ?></td>
                    <td><?php echo $data['count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Employee Chart</h3>
        <div id="chart-container">
            <canvas id="facultyChart" height="400"></canvas>
        </div>
       
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare the data for the chart
const facultyChartData = {
      labels: <?php echo json_encode(array_column($facultyChartData, 'label')); ?>,
      data: <?php echo json_encode(array_column($facultyChartData, 'count')); ?>,
      backgroundColors: ['#800000', '#FF0202', '#FC5858', '#FFB7B7'] // Custom background colors
    };

    // Create the faculty job title distribution chart
    function createFacultyChart() {
      const ctx = document.getElementById('facultyChart').getContext('2d');
      const myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: facultyChartData.labels,
          datasets: [{
            label: 'Faculty Job Title Distribution',
            data: facultyChartData.data,
            backgroundColor: facultyChartData.backgroundColors,
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
createFacultyChart();
</script>
</body>
</html>
