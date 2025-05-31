<?php
session_start();
include("../includes/database.php");

// Function to get form counts
function getFormCounts($month = null, $year = null) {
    global $conn;
    
    $month = $month ?? (date('n') - 1); // Default to current month (0-based)
    $year = $year ?? date('Y');
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

    return [
        'labels' => array_keys($counts),
        'data' => array_values($counts)
    ];
}

// Get the form counts
$FormChartData = getFormCounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Submission Report</title>
  <link rel="stylesheet" href="assets/css/print-form.css">
</head>

<style>
  .month-selection {
    width: 100%;
    max-width: 21cm;
    height: 100px;
    background-color: #fff;
    margin-bottom: 1rem;
    border-radius: 10px;
    text-align: center; 
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .month-selection select {
    font-size: 13px;
    width: 150px;
    padding-left: 10px;
    outline: none;
    background: #FFFFFF;
    color: maroon;
    border: 0px solid #000000;
    border-radius: 5px;
    box-shadow: 2px 1px 2px 1px #E2E2E2;
    transition: .3s ease;
  }
  .month-selection select:focus {
    background: #F2F2F2;
    border: 1px solid #FFC900;
    border-radius: 10px;
  }
  .month-selection select::placeholder {
    color: #DDDDDD;
  }
  .month-selection {
    justify-content: center;
    margin-bottom: 20px;
  }
  .month-selection form {
    display: flex;
    gap: 10px;
  }
</style>

<body>
  <div class="month-selection">
    <div class="form-group">
      <h5><label for="monthSelector">Select Month:</label></h5>
      <select id="monthSelector" class="form-control">
        <option value="0">January</option>
        <option value="1">February</option>
        <option value="2">March</option>
        <option value="3">April</option>
        <option value="4">May</option>
        <option value="5">June</option>
        <option value="6">July</option>
        <option value="7">August</option>
        <option value="8">September</option>
        <option value="9">October</option>
        <option value="10">November</option>
        <option value="11">December</option>
      </select>
    </div>
    <div class="form-group">
      <h5><label for="yearSelector">Select Year:</label></h5>
      <select id="yearSelector" class="form-control">
        <?php
        $currentYear = date('Y');
        for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
          echo "<option value='$i'>$i</option>";
        }
        ?>
      </select>
    </div>
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
            <th>Form</th>
            <th>Employee</th>
          </tr>
        </thead>
        <tbody class="submission-data">
        </tbody>
      </table>

      <h3>Form Submission Chart</h3>
      <div id="chart-container">
        <canvas id="FormSubmissionChart" height="400"></canvas>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
        let formSubmissionChart;

// Function to fetch and update form data
async function updateFormChart(month, year) {
    try {
        const response = await fetch(`../form_counts.php?month=${month}&year=${year}`);
        const data = await response.json();
        
        // Update table data
        const tbody = document.querySelector('.submission-data');
        tbody.innerHTML = data.labels.map((label, index) => `
            <tr>
                <td>${label}</td>
                <td>${data.data[index]}</td>
            </tr>
        `).join('');

        // Update chart
        if (formSubmissionChart) {
            formSubmissionChart.destroy();
        }

        const ctx = document.getElementById('FormSubmissionChart').getContext('2d');
        formSubmissionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Number of Forms',
                    data: data.data,
                    backgroundColor: [
                        '#800000',
                        '#FF0202',
                        '#FC5858',
                        '#FFB7B7',
                        '#FCD9D9'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error fetching form data:', error);
    }
}

// Add event listeners and initial chart creation (remains the same)
document.addEventListener('DOMContentLoaded', function() {
    const currentMonth = new Date().getMonth();
    const currentYear = new Date().getFullYear();
    
    document.getElementById('monthSelector').value = currentMonth;
    document.getElementById('yearSelector').value = currentYear;
    
    updateFormChart(currentMonth, currentYear);
});

document.getElementById('monthSelector').addEventListener('change', function() {
    updateFormChart(this.value, document.getElementById('yearSelector').value);
});

document.getElementById('yearSelector').addEventListener('change', function() {
    updateFormChart(document.getElementById('monthSelector').value, this.value);
});
  </script>
</body>
</html>
