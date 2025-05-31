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

// Number of results per page
$results_per_page = 10;

// Determine which page number visitor is currently on
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensuring page number is at least 1

// Determine the SQL LIMIT starting number for the results on the displaying page
$start_from = ($page - 1) * $results_per_page;

// Search functionality
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Modify the total count query to include search if applicable
$total_sql = "SELECT COUNT(*) as total 
              FROM travel_order_candelaria_forms toc
              JOIN user_details ud ON toc.employee_id = ud.ID
              WHERE toc.status = 'Declined'";

if (!empty($search_term)) {
  $total_sql .= " AND (
        toc.travel_order_id LIKE '%$search_term%' OR 
        ud.firstName LIKE '%$search_term%' OR 
        ud.lastName LIKE '%$search_term%' OR 
        toc.destination LIKE '%$search_term%' OR 
        toc.purpose LIKE '%$search_term%' OR 
        toc.travel_start_date LIKE '%$search_term%'
    )";
}

$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];

// Get the logged-in user's ID from the session
$user_id = $_SESSION['username']; // Assuming the dean's or department head's ID is stored in session as 'username'

// Calculate total pages needed
$total_pages = ceil($total_records / $results_per_page);

// Main query with search and pagination
$sql = "SELECT toc.travel_order_id, ud.firstName, ud.lastName, toc.destination, 
        toc.purpose, toc.travel_start_date, toc.travel_time, toc.return_time, 
        toc.status, toc.decline_reason, toc.dept_head_id, toc.dean_id 
        FROM travel_order_candelaria_forms toc
        JOIN user_details ud ON toc.employee_id = ud.ID
        WHERE toc.status = 'Declined'
        AND (toc.dept_head_id = '$user_id' OR toc.dean_id = '$user_id')";

if (!empty($search_term)) {
  $sql .= " AND (
        toc.travel_order_id LIKE '%$search_term%' OR 
        ud.firstName LIKE '%$search_term%' OR 
        ud.lastName LIKE '%$search_term%' OR 
        toc.destination LIKE '%$search_term%' OR 
        toc.purpose LIKE '%$search_term%' OR 
        toc.travel_start_date LIKE '%$search_term%'
    )";
}

$sql .= " ORDER BY toc.travel_order_id DESC LIMIT $start_from, $results_per_page";

// Execute the query
$result = mysqli_query($conn, $sql);

// Check for query execution errors
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/admin_table.css">
  <style>
    .btn-back {
      margin-top: 80px;
      margin-left: 260px;
      position: absolute;
      top: 10px;
      left: 10px;
    }

    .modal-dialog {
      max-width: 800px;
    }

    .modal-body {
      padding: 20px;
    }

    .detail-row {
      margin-bottom: 10px;
    }

    .detail-label {
      font-weight: bold;
    }

    /* Pending Leave Page */
    .leave-status-container a {
  background-color: maroon;
  color: white;
  transition: all 0.2s ease-in-out;
}

.leave-status-container a:hover {
  background-color: maroon;
  color: white;
}

.leave-status-container a:active {
  border: 2px solid gold !important;
  background-color: maroon;
  color: white;
  transform: scale(0.80);  /* Makes the button 5% smaller when clicked */
}

  </style>
</head>

<body>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>

      <div class="col p-0">
        <?php include("includes/header.php"); ?>

        <div class="container-fluid page-content mt-3">

          <!-- Main Container -->
          <div class="main-container">
            <!-- Content -->
            <div class="leave-status-container">
              <a href="travel_order_cande_pending.php" class="btn leave-status">Pending</a>
              <a href="travel_order_cande_approved.php" class="btn leave-status">Approved</a>
              <a href="travel_order_cande_declined.php" class="btn leave-status active">Declined</a>
            </div>
            <div class="table-container">
              <div class="container table-scontainer">
                <h3 class="table-name">Declined Travel Order Candelaria Submissions</h3>
                <div class="search-container">
                  <input type="text" id="search" class="form-control" placeholder="Search..."
                    value="<?php echo htmlspecialchars($search_term); ?>" onkeydown="handleSearch(event)">
                </div>
                <br>
                <br>
                <table class="table table-striped">
                  <thead>
                    <tr class="table-row">
                      <th>Travel Order ID</th>
                      <th>Name</th>
                      <th>Destination</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Return Time</th>
                      <th>Purpose</th>
                      <th>Status</th>
                      <th>Reason Declined</th>
                      <th>Department Head Reporting To</th>
                      <th>Dean Reporting To</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody class="text-center" id="employeeTableBody">
                    <?php
                    // Check if there are any pending leave submissions
                    if (mysqli_num_rows($result) > 0) {
                      // Loop through each row and display it in the table
                      while ($row = mysqli_fetch_assoc($result)) {
                        // Get department head name
                                                // Get department head name
                                                $dept_head_query = "SELECT firstName, lastName FROM user_details WHERE ID = '" . $row['dept_head_id'] . "'";
                                                $dept_head_result = mysqli_query($conn, $dept_head_query);
                                                $dept_head_name = mysqli_fetch_assoc($dept_head_result);
                                                $dept_head_fullname = $dept_head_name['firstName'] . " " . $dept_head_name['lastName'];

                                                // Get dean name
                                                $dean_query = "SELECT firstName, lastName FROM user_details WHERE ID = '" . $row['dean_id'] . "'";
                                                $dean_result = mysqli_query($conn, $dean_query);
                                                $dean_name = mysqli_fetch_assoc($dean_result);
                                                $dean_fullname = $dean_name['firstName'] . " " . $dean_name['lastName'];
                        echo "<tr>";
                        echo "<td>" . $row['travel_order_id'] . "</td>";
                        echo "<td>" . $row['firstName'] . " " . $row['lastName'] . "</td>";
                        echo "<td>" . $row['destination'] . "</td>";
                        echo "<td>" . date('m/d/Y', strtotime($row['travel_start_date'])) . "</td>";
                        echo "<td>" . date('m/d/Y', strtotime($row['travel_time'])) . "</td>";
                        echo "<td>" . date('m/d/Y', strtotime($row['return_time'])) . "</td>";
                        echo "<td>" . $row['purpose'] . "</td>";
                        echo "<td class='table-danger pending'>" . $row['status'] . "</td>";
                        echo "<td>" . $row['decline_reason'] . "</td>";
                        echo "<td>" . $dept_head_fullname . "</td>";
                                                echo "<td>" . $dean_fullname . "</td>"; // Added dean name to the table
                        echo "<td>";
                        echo "<button class='view-pending-button' data-bs-toggle='modal' data-bs-target='#travelOrderCModal' data-travel-order-cande-id='" . $row['travel_order_id'] . "'><i class='fas fa-eye'></i></button>";
                        echo "</td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='8'>No pending leave submissions found.</td></tr>";
                    }
                    ?>
                  </tbody>
                </table>
                <!-- Pagination links -->
                <div class="pagination-container">
                  <nav aria-label="Page navigation example">
                    <ul class="pagination">
                      <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
                      <?php endif; ?>

                      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                      <?php endfor; ?>

                      <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                      <?php endif; ?>
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
            <!-- End Content -->
          </div>
          <!--End Main Container -->
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="travelOrderCModal" tabindex="-1" aria-labelledby="travelOrderCModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="travelOrderCModalLabel">Travel Order Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="travelOrderCContent">
          <!-- Leave details will be loaded here dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php mysqli_close($conn); ?>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
  <script>
    // Additional JavaScript if needed
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const travelOrderCModal = document.getElementById('travelOrderCModal');
      travelOrderCModal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const travelOrderCId = button.getAttribute('data-travel-order-cande-id');
  fetchTravelOrderCDetails(travelOrderCId);
});
    });

    function fetchTravelOrderCDetails(travelOrderCId) {
  fetch(`actions/get_travel_order_cande_details.php?travel_order_id=${travelOrderCId}`)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error:', data.error);
      } else {
        displayTravelOrderCDetails(data);
        const travelOrderCModal = new bootstrap.Modal(document.getElementById('travelOrderCModal'));
        travelOrderCModal.show();
      }
    })
    .catch(error => console.error('Error:', error));
}

    function displayTravelOrderCDetails(data) {
  const content = document.getElementById('travelOrderCContent');
  content.innerHTML = `
    <div class="detail-row"><span class="detail-label">Travel Order ID:</span> ${data.travel_order_id}</div>
    <div class="detail-row"><span class="detail-label">Employee Name:</span> ${data.firstName} ${data.lastName}</div>
    <div class="detail-row"><span class="detail-label">Destination:</span> ${data.destination}</div>
    <div class="detail-row"><span class="detail-label">Date:</span> ${data.travel_start_date}</div>
    <div class="detail-row"><span class="detail-label">Time:</span> ${data.travel_time}</div>
    <div class="detail-row"><span class="detail-label">Return Time:</span> ${data.return_time}</div>
    <div class="detail-row"><span class="detail-label">Purpose:</span> ${data.purpose}</div>
    <div class="detail-row"><span class="detail-label">Status:</span> ${data.status}</div>
    <div class="detail-row"><span class="detail-label">Department Head:</span> ${data.dept_head_fullname}</div>
    <div class="detail-row"><span class="detail-label">Dean:</span> ${data.dean_fullname}</div>
  `;
}
    const travelOrderCModal = new bootstrap.Modal(document.getElementById('travelOrderCModal'));

    document.getElementById('travelOrderCModal').addEventListener('hidden.bs.modal', function () {
      travelOrderCModal.hide();
      document.body.classList.remove('modal-open');
      document.querySelector('.modal-backdrop').remove();
    });


  </script>

<script>
    // Add this JavaScript code before the closing </body> tag
    function handleSearch(event) {
      if (event.key === 'Enter') {
        const searchTerm = document.getElementById('search').value;
        const currentUrl = new URL(window.location.href);

        // Update search parameter
        if (searchTerm) {
          currentUrl.searchParams.set('search', searchTerm);
        } else {
          currentUrl.searchParams.delete('search');
        }

        // Reset to first page when searching
        currentUrl.searchParams.set('page', '1');

        window.location.href = currentUrl.toString();
      }
    }

    // Optional: Add clear search functionality
    function clearSearch() {
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.delete('search');
      currentUrl.searchParams.set('page', '1');
      window.location.href = currentUrl.toString();
    }
  </script>


</body>

</html>