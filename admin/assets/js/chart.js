// Data for the employee chart
const employeeChartData = {
  labels: ['Faculty', 'Non-Faculty', 'Admin', 'Dept Head'],
  data: [48, 33, 20, 8], // Sample data for each category
  backgroundColors: ['#800000', '#800000', '#800000', '#800000'] // Custom background colors
};

// Data for the department chart
const facultyChartData = {
  labels: ['Elementary', 'High School', 'College', 'Part-time'],
  data: [10, 15, 15, 8], // Sample data for each category
  backgroundColors: ['#800000', '#FF0202', '#FC5858', '#FFB7B7'] // Custom background colors
};

// Sample data for different months (replace with actual data as needed)
const monthlyFormSubmissionData = [
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [23, 9, 1, 14] }, // January
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [15, 7, 2, 10] }, // February
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [18, 8, 3, 12] }, // March
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [12, 14, 5, 3] }, // April
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [20, 2, 1, 17] }, // May
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [14, 12, 4, 15] }, // June
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [12, 11, 3, 14] }, // July
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [19, 3, 1, 2] }, // August
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [13, 4, 3, 12] }, // September
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [11, 1, 5, 10] }, // October
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [12, 2, 2, 8] }, // November
  { labels: ['Leave Submission', 'Travel Order', 'Make-up Class', 'Log Submissions'], data: [16, 4, 0, 9] }, // December
  // Add data for other months...
];

// Data for the employee chart
const EmployeeDemographicData = {
  labels: ['Male', 'Female'],
  data: [55, 68], // Sample data for each category
  backgroundColors: ['#800000', '#FFB7B7'] // Custom background colors
};

// Function to create the bar chart
function createEmployeeChart() {
  const ctx = document.getElementById('employeeChart').getContext('2d');
  const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: employeeChartData.labels,
      datasets: [{
        label: 'Number of Employees',
        data: employeeChartData.data,
        backgroundColor: employeeChartData.backgroundColors,
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

// Function to create the faculty employee chart
function createFacultyChart() {
  const ctx = document.getElementById('facultyChart').getContext('2d');
  const myChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: facultyChartData.labels,
      datasets: [{
        label: 'Faculty Employee Distribution',
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

let formSubmissionChart;

function createFormSubmissionChart(data) {
  const ctx = document.getElementById('FormSubmissionChart').getContext('2d');
  if (formSubmissionChart) {
    formSubmissionChart.destroy();
  }
  formSubmissionChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Number of Employees',
        data: data.data,
        backgroundColor: ['#800000', '#800000', '#800000', '#800000'],
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

// Event listener for month selection
document.getElementById('monthSelector').addEventListener('change', function() {
  const selectedMonth = this.value;
  const data = monthlyFormSubmissionData[selectedMonth];
  createFormSubmissionChart(data);
});

// Call the function to create the chart when the page loads
document.addEventListener('DOMContentLoaded', function() {
  createEmployeeChart();
  createFacultyChart();
  createFormSubmissionChart(monthlyFormSubmissionData[0]);
  createEmployeeDemographicChart();
});
