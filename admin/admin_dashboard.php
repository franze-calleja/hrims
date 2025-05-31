<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
  // Redirect to login page if not logged in
  header("Location: ../login.php");
  exit;
}

// Include the database connection file
include("../includes/database.php");


// Get user details from session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch admin details from the database
if ($role === 'admin') {
  // Prepare and execute the query
  $sql = "SELECT * FROM user_details WHERE ID = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username); // Assuming 'adminID' is the username
  $stmt->execute();
  $result = $stmt->get_result();

  // Fetch the details
  if ($result->num_rows > 0) {
    $adminDetails = $result->fetch_assoc();
  } else {
    $adminDetails = null; // No details found
  }
  $stmt->close();
} else {
  $adminDetails = null;
}

// Fetch the total number of employees
$totalEmployeesQuery = "SELECT COUNT(*) as total FROM user_details";
$totalEmployeesResult = $conn->query($totalEmployeesQuery);
$totalEmployees = $totalEmployeesResult->fetch_assoc()['total'];

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

$query = "SELECT COUNT(*) as pending_count FROM leave_forms WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$pending_count = $row['pending_count'];


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/admin_dashboard.css">
</head>

<style>
  .print-button {
    background-color: maroon !important; 
    height: auto;
    width: 150px;
    margin-top: 10px;
    margin-right: 20px;
    margin-bottom: 10px;
    align-self: flex-end;
  }
</style>


<body>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>


      <div class="col p-0">
        <?php include("includes/header.php"); ?>


        <div class="container-fluid page-content mt-3">

          <!-- Main Container Area -->
          <div class="main-container">
            <!-- Content Area -->
            <div class="content">
              <!-- Container for Rectangle and Squares -->
              <div style="display: flex; flex-wrap: wrap">
                <!-- Existing Rectangle and Squares -->
                <div class="profile-dashboard">
                  <img src="../uploads/<?php echo htmlspecialchars($adminDetails['profileImage']); ?>"
                    alt="Profile Image" class="profile-image">
                  <div class="profile-info">
                    <div class="greeting">Good Day,</div>
                    <div><span
                        class="name"><?php echo htmlspecialchars($adminDetails['firstName'] . ' ' . $adminDetails['lastName']); ?></span>
                    </div>
                    <div class="date"><?php echo date('F j, Y'); ?></div>
                    <a href="admin_profile.php" class="btn btn-danger edit-button">
                      <i class="fas fa-pencil-alt"></i> Edit Profile
                    </a>

                  </div>
                </div>
                <div class="leave-details">
                  <div class="title">Pending <br />Leaves</div>
                  <a href="leave_pending.php" class="view-link">Click here to view</a>
                  <div class="line"></div>
                  <div class="number"><?php echo $pending_count; ?></div>
                </div>
                <div class="employee-details">
                  <div class="title">Employee <br />Details</div>
                  <a href="employee_details.php" class="view-link">Click here to view</a>
                  <div class="line"></div>
                  <div class="number"><?php echo $totalEmployees; ?></div>
                </div>

                <!-- New Rectangles -->
                <div class="demo-1">
                  <h1>Employee Chart</h1>
                  <a href="hris-form/employee-report.php" class="btn btn-danger print-button" target="_blank">
                    <i class="fas fa-file-alt"></i> Print Report
                  </a>
                  <div class="chart-container">
                    <canvas id="employeeChart" height="400"></canvas>
                  </div>
                </div>
                <div class="demo-2">
                  <h1>Faculty Employee Chart</h1>
                  <a href="hris-form/faculty-report.php" class="btn btn-danger print-button" target="_blank">
                    <i class="fas fa-file-alt"></i> Print Report
                  </a>
                  <div class="chart-container">
                    <canvas id="facultyChart" height="380"></canvas>
                  </div>
                </div>
                <div class="demo-3">
                  <h1>Forms submitted this month</h1>
                  <a href="hris-form/forms-report.php" class="btn btn-danger print-button" target="_blank">
                    <i class="fas fa-file-alt"></i> Print Report
                  </a>
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
                      <!-- Populate the years dynamically based on the data in the database -->
                      <?php
                      $currentYear = date('Y');
                      for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                        echo "<option value='$i'>$i</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <div class="chart-container">
                    <canvas id="FormSubmissionChart" height="380"></canvas>
                  </div>
                </div>
                <div class="demo-4">
                  <h1>Employee Structure</h1>
                  <a href="hris-form/demographic-report.php" class="btn btn-danger print-button" target="_blank">
                    <i class="fas fa-file-alt"></i> Print Report
                  </a>
                  <div class="chart-container">
                    <canvas id="EmployeeDemographicChart" height="400"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- End Content Area -->

          </div>
          <!-- End Main Container -->

        </div>
      </div>
    </div>
  </div>



  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- <script src="assets/js/chart.js"></script> -->
  <script>
    // Data for the employee chart
    const departmentChartData = {
      labels: <?php echo json_encode(array_column($departmentChartData, 'label')); ?>,
      data: <?php echo json_encode(array_column($departmentChartData, 'count')); ?>,
      backgroundColors: ['#800000', '#FF0202', '#FC5858', '#FFB7B7'] // Custom background colors
    };

    const facultyChartData = {
      labels: <?php echo json_encode(array_column($facultyChartData, 'label')); ?>,
      data: <?php echo json_encode(array_column($facultyChartData, 'count')); ?>,
      backgroundColors: ['#800000', '#FF0202', '#FC5858', '#FFB7B7'] // Custom background colors
    };



    // Data for the employee chart
    const EmployeeDemographicData = {
      labels: <?php echo json_encode(array_column($employeeDemographicData, 'gender')); ?>,
      data: <?php echo json_encode(array_column($employeeDemographicData, 'count')); ?>,
      backgroundColors: ['#800000', '#FFB7B7'] // Custom background colors
    };

    // Create the department distribution chart
    function createEmployeeChart() {
      const ctx = document.getElementById('employeeChart').getContext('2d');
      const myChart = new Chart(ctx, {
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
    // Replace the existing formSubmissionChart variable and related code with this:
    let formSubmissionChart;

    // Function to fetch and update form data
    async function updateFormChart(month, year) {
      try {
        const response = await fetch(`form_counts.php?month=${month}&year=${year}`);
        const data = await response.json();

        // If chart exists, destroy it before creating a new one
        if (formSubmissionChart) {
          formSubmissionChart.destroy();
        }

        // Create new chart
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

    // Add event listeners for month and year selectors
    document.getElementById('monthSelector').addEventListener('change', function () {
      updateFormChart(this.value, document.getElementById('yearSelector').value);
    });

    document.getElementById('yearSelector').addEventListener('change', function () {
      updateFormChart(document.getElementById('monthSelector').value, this.value);
    });


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


    // Call the function to create the chart when the page loads
    document.addEventListener('DOMContentLoaded', function () {
      createEmployeeChart();
      createFacultyChart();
      createEmployeeDemographicChart();
    });

    // Initial chart creation with current month and year
    document.addEventListener('DOMContentLoaded', function () {
      // Set initial month and year values to current month and year
      const currentMonth = new Date().getMonth();
      const currentYear = new Date().getFullYear();
      document.getElementById('monthSelector').value = currentMonth;
      document.getElementById('yearSelector').value = currentYear;
      updateFormChart(currentMonth, currentYear);
    });
  </script>


</body>

</html>