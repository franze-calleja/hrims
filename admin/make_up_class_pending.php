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
              FROM make_up_forms mc
              JOIN user_details ud ON mc.employee_id = ud.ID
              WHERE mc.status = 'Pending'";

if (!empty($search_term)) {
  $total_sql .= " AND (
        mc.makeup_class_id LIKE '%$search_term%' OR 
        ud.firstName LIKE '%$search_term%' OR 
        ud.lastName LIKE '%$search_term%' OR 
        mc.subject LIKE '%$search_term%' OR 
        mc.regular_class_date LIKE '%$search_term%' OR 
        mc.makeup_class_date LIKE '%$search_term%'
    )";
}

$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];

// Calculate total pages needed
$total_pages = ceil($total_records / $results_per_page);

// Main query with search and pagination
$sql = "SELECT mc.makeup_class_id, ud.firstName, ud.lastName, mc.subject, 
        mc.regular_class_date, mc.regular_class_time, mc.regular_class_room, 
        mc.makeup_class_date, mc.makeup_class_time, mc.makeup_class_room, 
        mc.status, mc.dept_head_id, mc.dean_id 
        FROM make_up_forms mc
        JOIN user_details ud ON mc.employee_id = ud.ID
        WHERE mc.status = 'Pending'";

if (!empty($search_term)) {
  $sql .= " AND (
        mc.makeup_class_id LIKE '%$search_term%' OR 
        ud.firstName LIKE '%$search_term%' OR 
        ud.lastName LIKE '%$search_term%' OR 
        mc.subject LIKE '%$search_term%' OR 
        mc.regular_class_date LIKE '%$search_term%' OR 
        mc.makeup_class_date LIKE '%$search_term%'
    )";
}

$sql .= " ORDER BY mc.makeup_class_id DESC LIMIT $start_from, $results_per_page";

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
              <a href="make_up_class_pending.php" class="btn leave-status active">Pending</a>
              <a href="make_up_class_approved.php" class="btn leave-status">Approved</a>
              <a href="make_up_class_declined.php" class="btn leave-status">Declined</a>
            </div>
            <div class="table-container">
              <div class="container table-scontainer">
                <h3 class="table-name">Pending Make-up Class Submissions</h3>
                <div class="search-container">
                  <input type="text" id="search" class="form-control" placeholder="Search..."
                    value="<?php echo htmlspecialchars($search_term); ?>" onkeydown="handleSearch(event)">
                </div>
                <br>
                <br>
                <table class="table table-striped">
                  <thead>
                    <tr class="table-row">
                      <th>Form ID</th>
                      <th>Name</th>
                      <th>Subject</th>
                      <th>Regular Date</th>
                      <th>Regular Time</th>
                      <th>Regular Room</th>
                      <th>Make-up Date</th>
                      <th>Make-up Time</th>
                      <th>Make-up Room</th>
                      <th>Status</th>
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
                        echo "<td>" . $row['makeup_class_id'] . "</td>";
                        echo "<td>" . $row['firstName'] . " " . $row['lastName'] . "</td>";
                        echo "<td>" . $row['subject'] . "</td>";
                        echo "<td>" . date('m/d/Y', strtotime($row['regular_class_date'])) . "</td>";
                        echo "<td>" . date('h:i A', strtotime($row['regular_class_time'])) . "</td>";
                        echo "<td>" . $row['regular_class_room'] . "</td>";
                        echo "<td>" . date('m/d/Y', strtotime($row['makeup_class_date'])) . "</td>";
                        echo "<td>" . date('h:i A', strtotime($row['makeup_class_time'])) . "</td>";
                        echo "<td>" . $row['makeup_class_room'] . "</td>";
                        echo "<td class='table-warning pending'>" . $row['status'] . "</td>";
                        echo "<td>" . $dept_head_fullname . "</td>";
                                                echo "<td>" . $dean_fullname . "</td>"; // Added dean name to the table
                        echo "<td>";
                        echo "<button class='accept-pending-button' data-make-up-class-id='" . $row['makeup_class_id'] . "'><i class='fas fa-check'></i></button>";
                        echo "<button class='decline-pending-button' data-make-up-class-id='" . $row['makeup_class_id'] . "'><i class='fas fa-xmark'></i></button>";
                        echo "<button class='view-pending-button' data-bs-toggle='modal' data-bs-target='#makeUpClassModal' data-make-up-class-id='" . $row['makeup_class_id'] . "'><i class='fas fa-eye'></i></button>";
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
  <div class="modal fade" id="makeUpClassModal" tabindex="-1" aria-labelledby="makeUpClassModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="makeUpClassModalLabel">Make-up Class Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="makeUpClassContent">
          <!-- Leave details will be loaded here dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

    <!-- Add this modal for decline reason -->
<div class="modal fade" id="declineReasonModal" tabindex="-1" aria-labelledby="declineReasonModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="declineReasonModalLabel">Decline Make-Up Class Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="declineReasonForm">
          <input type="hidden" id="declinemakeUpClass" name="makeup_class_id">
          <div class="mb-3">
            <label for="declineReason" class="form-label">Please provide a reason for declining:</label>
            <textarea class="form-control" id="declineReason" name="decline_reason" rows="3" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDecline">Decline Make-up Class</button>
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
      const makeUpClassModal = document.getElementById('makeUpClassModal');
      makeUpClassModal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const makeUpClassId = button.getAttribute('data-make-up-class-id');
  fetchMakeUpClassDetails(makeUpClassId);
});
    });

    function fetchMakeUpClassDetails(makeUpClassId) {
  fetch(`actions/get_make_up_class_details.php?makeup_class_id=${makeUpClassId}`)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error:', data.error);
      } else {
        displayMakeUpClassDetails(data);
        const makeUpClassModal = new bootstrap.Modal(document.getElementById('makeUpClassModal'));
        makeUpClassModal.show();
      }
    })
    .catch(error => console.error('Error:', error));
}

    function displayMakeUpClassDetails(data) {
  const content = document.getElementById('makeUpClassContent');
  content.innerHTML = `
    <div class="detail-row"><span class="detail-label">Make-up Class ID:</span> ${data.makeup_class_id}</div>
    <div class="detail-row"><span class="detail-label">Employee Name:</span> ${data.firstName} ${data.lastName}</div>
    <div class="detail-row"><span class="detail-label">Subject:</span> ${data.subject}</div>
    <div class="detail-row"><span class="detail-label">Regular Class Date:</span> ${data.regular_class_date}</div>
    <div class="detail-row"><span class="detail-label">Regular Class Time:</span> ${data.regular_class_time}</div>
    <div class="detail-row"><span class="detail-label">Regular Class Room:</span> ${data.regular_class_room}</div>
    <div class="detail-row"><span class="detail-label">Regular Class Date:</span> ${data.makeup_class_date}</div>
    <div class="detail-row"><span class="detail-label">Regular Class Time:</span> ${data.makeup_class_time}</div>
    <div class="detail-row"><span class="detail-label">Regular Class Room:</span> ${data.makeup_class_room}</div>
    <div class="detail-row"><span class="detail-label">Status:</span> ${data.status}</div>
    <div class="detail-row"><span class="detail-label">Reason:</span> ${data.reason}</div>
    <div class="detail-row"><span class="detail-label">Department Head:</span> ${data.dept_head_fullname}</div>
    <div class="detail-row"><span class="detail-label">Dean:</span> ${data.dean_fullname}</div>
  `;
}
    const makeUpClassModal = new bootstrap.Modal(document.getElementById('makeUpClassModal'));

    document.getElementById('makeUpClassModal').addEventListener('hidden.bs.modal', function () {
      makeUpClassModal.hide();
      document.body.classList.remove('modal-open');
      document.querySelector('.modal-backdrop').remove();
    });


  </script>

  <script>
document.addEventListener('DOMContentLoaded', function () {
  const declineReasonModal = new bootstrap.Modal(document.getElementById('declineReasonModal'));
  
  // Handle approve button click
  document.querySelectorAll('.accept-pending-button').forEach(function (button) {
    button.addEventListener('click', function () {
      const makeUpClass = this.getAttribute('data-make-up-class-id');
      processMakeUpClassAction(makeUpClass, 'approve');
    });
  });

  // Handle decline button click
  document.querySelectorAll('.decline-pending-button').forEach(function (button) {
    button.addEventListener('click', function () {
      const makeUpClass = this.getAttribute('data-make-up-class-id');
      // Set the make up class ID in the decline modal
      document.getElementById('declinemakeUpClass').value = makeUpClass;
      declineReasonModal.show();
    });
  });

  // Handle decline confirmation
  document.getElementById('confirmDecline').addEventListener('click', function() {
    const makeUpClass = document.getElementById('declinemakeUpClass').value;
    const declineReason = document.getElementById('declineReason').value.trim();
    
    if (!declineReason) {
      alert('Please provide a reason for declining the make-up class request.');
      return;
    }
    
    processMakeUpClassAction(makeUpClass, 'decline', declineReason);
    declineReasonModal.hide();
  });
});

function processMakeUpClassAction(makeUpClass, action, declineReason = '') {
  const formData = new URLSearchParams();
  formData.append('makeup_class_id', makeUpClass);
  formData.append('action', action);
  if (declineReason) {
    formData.append('decline_reason', declineReason);
  }

  fetch('actions/process_make_up_class.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert(data.success);
      location.reload();
    } else {
      alert(data.error);
    }
  })
  .catch(error => console.error('Error:', error));
}
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