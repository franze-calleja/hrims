<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Gov ID Print</title>
    <link rel="stylesheet" href="assets/css/print-form.css">
    <style>
        /* Add any additional styles here */
    </style>
</head>

<body>

<div class="month-selection">
    <select name="details" id="details" onchange="fetchGovernmentID()">
        <option value="sssNumber">SSS Number</option>
        <option value="tinNumber">TIN Number</option>
        <option value="pagibigNumber">Pag-ibig Number</option>
        <option value="philhealthID">PhilHealth ID</option>
    </select>

    <button class="print-button" onclick="printPage()">Print Report</button>
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
            <td colspan="4"><strong>MSEUF Candelaria Website</strong></td>
            <td rowspan="2"><strong>Date: November 2, 2024</strong></td>
        </tr>
        
        <tr>
            <td colspan="2"><strong>Latest Employee Gov IDs as of November 2, 2024</strong></td>
        </tr>
    </table>

    <div class="content">
        <p style="text-align: center;"><strong id="gov-id-title">SSS Number</strong></p>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>ID</th>
                </tr>
            </thead>
            <tbody id="gov-id-table">
                <!-- Fetched data will be inserted here -->
            </tbody>
        </table>
    </div>
</div>

<script>
    function printPage() {
        window.print();
    }

    function fetchGovernmentID() {
        const selectedValue = document.getElementById('details').value;
        const titleMap = {
            'sssNumber': 'SSS Number',
            'tinNumber': 'TIN Number',
            'pagibigNumber': 'Pag-ibig Number',
            'philhealthID': 'PhilHealth ID'
        };

        document.getElementById('gov-id-title').innerText = titleMap[selectedValue];

        fetch(`fetch_government_id.php?type=${selectedValue}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('gov-id-table');
                tbody.innerHTML = ''; // Clear existing rows

                data.forEach(employee => {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td>${employee.name}</td><td>${employee.id}</td>`;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching data:', error));
    }
</script>

</body>
</html>
