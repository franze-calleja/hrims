<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Analytics Print</title>
    <link rel="stylesheet" href="assets/css/print-form.css">
    <style>
      
    </style>
</head>

<body>

<div class="month-selection">
    <select name="month" id="month" onchange="alert('This is a static page and cannot submit.')">
        <option value="">All Months</option>
        <option value="2023-01">January 2023</option>
        <option value="2023-02">February 2023</option>
        <option value="2023-03">March 2023</option>
       
    </select>

    <button class="print-button" onclick="printPage()">Print Report</button>
</div>

<div class="form-container">
    <table>
        <tr>
            <td colspan="4" rowspan="2" class="no-border header-section">
                <img src="assets/images/seal_logo.png" alt="Logo">
                <div>
                    Manuel S. Enverga University Foundation Candelaria, Inc. Quezon, Philippines
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="4"><strong>MSEUF Candelaria Website</strong></td>
            <td rowspan="2"><strong>Date: November 2, 2024</strong></td>
        </tr>
        
        <tr>
            <td colspan="2"><strong>Latest Views Analytics as of November 2, 2024</strong></td>
        </tr>
    </table>

    <div class="content">
        <p style="text-align: center;"><strong>News Views Report</strong></p>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>News Title</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Sample News Title 1</td>
                    <td>100</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Sample News Title 2</td>
                    <td>80</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Sample News Title 3</td>
                    <td>60</td>
                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>

        <h3>Top Views Chart</h3>
        <div id="top-views-chart-container">
            <canvas id="topViewsChart"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="mostViewedMonthChart"></canvas>
        </div>
       
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function printPage() {
        window.print();
    }

    // Static data for charts
    const mostViewedMonthData = {
        labels: ['January (300 views)', 'February (150 views)', 'March (200 views)'],
        datasets: [{
            label: 'Monthly Views',
            data: [300, 150, 200],
            backgroundColor: 'rgba(171, 0, 0, 0.80)',
            borderColor: 'rgba(255, 99, 71, 0)',
            borderWidth: 1
        }]
    };

    const mostViewedMonthCtx = document.getElementById('mostViewedMonthChart').getContext('2d');
    new Chart(mostViewedMonthCtx, {
        type: 'bar',
        data: mostViewedMonthData,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Most Viewed Months'
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + ' views';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true
                }
            }
        }
    });

    const topViewsChartData = {
        labels: [1, 2, 3],
        datasets: [{
            label: 'Top News Views',
            data: [100, 80, 60],
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    };
    
    const topViewsCtx = document.getElementById('topViewsChart').getContext('2d');
    new Chart(topViewsCtx, {
        type: 'line',
        data: topViewsChartData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
