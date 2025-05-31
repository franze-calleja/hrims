<?php
session_start();
include("../includes/database.php");

$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$selected_department = isset($_GET['department']) ? $_GET['department'] : '';
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$start_from = ($current_page - 1) * $records_per_page;

// Main data query: only show records not “deleted”
$query = "
  SELECT ID, firstName, lastName, department, jobTitle
    FROM user_details
   WHERE isDelete = 0
";
if ($selected_department) {
  $query .= " AND department = '" . mysqli_real_escape_string($conn, $selected_department) . "'";
}
if ($search_term) {
  $query .= " AND (
        firstName LIKE '%{$search_term}%'
     OR lastName  LIKE '%{$search_term}%'
     OR department LIKE '%{$search_term}%'
     OR jobTitle LIKE '%{$search_term}%'
    )";
}
$query .= " LIMIT {$start_from}, {$records_per_page}";
$result = mysqli_query($conn, $query);

// Count for pagination: only non-deleted
$total_records_query = "
  SELECT COUNT(*)
    FROM user_details
   WHERE isDelete = 0
";
if ($selected_department) {
  $total_records_query .= " AND department = '" . mysqli_real_escape_string($conn, $selected_department) . "'";
}
if ($search_term) {
  $total_records_query .= " AND (
        firstName LIKE '%{$search_term}%'
     OR lastName  LIKE '%{$search_term}%'
     OR department LIKE '%{$search_term}%'
     OR jobTitle LIKE '%{$search_term}%'
    )";
}
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records = mysqli_fetch_array($total_records_result)[0];
$total_pages = ceil($total_records / $records_per_page);

// Fetch the employee data if an ID is provided for editing
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;
$employee_data = [];
if ($edit_id > 0) {
  $edit_query = "SELECT * FROM user_details WHERE ID = {$edit_id} AND isDelete = 0";
  $edit_result = mysqli_query($conn, $edit_query);
  $employee_data = mysqli_fetch_assoc($edit_result);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/admin_table.css">
  <link rel="stylesheet" href="assets/css/edit_employee.css">
</head>

<style>
  .btn-info {
    width: 48px;
  }

  /* Hide dropdown initially */
  .department-filter {
    width: 250px;
    display: none;
    /* Hidden by default */
    margin-right: 20px;
    margin-bottom: 15px;
    /* Add right margin for alignment */
  }

  .department-filter select {
    border: 2px solid maroon;
    /* Border styling */
    border-radius: 5px;
    /* Rounded corners */
    padding: 5px;
    /* Add padding inside the dropdown */
  }

  /* Show dropdown when active */
  .department-filter.active {
    display: block;
    /* Show when filter is active */
  }
</style>

<body>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>

      <div class="col p-0">
        <?php include("includes/header.php"); ?>

        <div class="container-fluid page-content mt-3">
          <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
              <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
          <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
              <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
          <?php endif; ?>
          <!-- Main Container Area -->
          <div class="main-container">

            <!-- Content -->
            <div class="table-responsive">
              <div class="container table-scontainer">
                <h3 class="table-name">Employee Records</h3>


                <div class="search-container">
                  <input type="text" id="search" class="form-control" placeholder="Search..." style="width: 272px;"
                    value="<?php echo htmlspecialchars($search_term); ?>" onkeydown="handleSearch(event)">
                  <button class="filter-button ms-2" onclick="toggleFilterDropdown()">
                    <i class="fas fa-filter"></i>
                  </button>
                </div>

                <!-- Department Filter Dropdown -->
                <div class="department-filter" id="departmentFilter">
                  <select id="departmentSelect" class="form-select">
                    <option value="">All Departments</option>
                    <option value="Admin" <?php echo ($selected_department == 'Admin') ? 'selected' : ''; ?>>Admin
                    </option>
                    <option value="Elementary" <?php echo ($selected_department == 'Elementary') ? 'selected' : ''; ?>>
                      Elementary</option>
                    <option value="Highschool" <?php echo ($selected_department == 'Highschool') ? 'selected' : ''; ?>>
                      Highschool</option>
                    <option value="College" <?php echo ($selected_department == 'College') ? 'selected' : ''; ?>>College
                    </option>
                    <option value="Non-Faculty" <?php echo ($selected_department == 'Non-Faculty') ? 'selected' : ''; ?>>
                      Non-Faculty</option>
                    <option value="Department Head" <?php echo ($selected_department == 'Department Head') ? 'selected' : ''; ?>>Department Head</option>
                    <option value="Dean of Studies" <?php echo ($selected_department == 'Dean of Studies') ? 'selected' : ''; ?>>Dean of Studies</option>
                  </select>
                </div>

                <div class="add-container">
                  <button class="add-employee-button" id="printDetails"
                    onclick="window.open('hris-form/employee-gov-ID.php', '_blank')" style="margin-right: 10px;">
                    <i class="fas fa-print"></i>Print Details
                  </button>
                  <button class="add-employee-button openModalBtn" id="addEmployeeBtn"><i class="fas fa-plus"></i> Add
                    Employee</button>
                </div>

                <table class="table table-striped">
                  <thead>
                    <tr class="table-row">
                      <th>ID</th>
                      <th>First Name</th>
                      <th>Last Name</th>
                      <th>Department</th>
                      <th>Job Title</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="employeeTableBody">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                      // Output data for each row
                      while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['ID'] . "</td>";
                        echo "<td>" . $row['firstName'] . "</td>";
                        echo "<td>" . $row['lastName'] . "</td>";
                        echo "<td>" . $row['department'] . "</td>";
                        echo "<td>" . $row['jobTitle'] . "</td>";
                        echo "
                        <div class='action-button d-flex'>
                        <td>
                            <a href='update_employee.php?id=" . $row['ID'] . "'><button class='edit-button'><i class='fas fa-pen'></i></button></a>
                            <a href='delete_employee.php?id=" . $row['ID'] . "' class='delete-link' onclick='return confirm(\"Are you sure you want to delete this employee?\");'><button class='delete-button'><i class='fas fa-trash'></i></button></a>
                            <a href='employee_view.php?id=" . $row['ID'] . "'><button class='view-button'><i class='fas fa-eye'></i></button></a>
                        </td>
                          </div>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='6'>No records found</td></tr>";
                    }

                    mysqli_close($conn);
                    ?>
                  </tbody>
                </table>
                <div class="pagination-container">
                  <nav aria-label="Page navigation">
                    <ul class="pagination">
                      <?php
                      // Previous page link
                      if ($current_page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='?page=" . ($current_page - 1) . "&department=" . urlencode($selected_department) . "'>Previous</a></li>";
                      }

                      // Links for individual pages
                      for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i == $current_page) {
                          echo "<li class='page-item active'><a class='page-link' href='#'>$i</a></li>";
                        } else {
                          echo "<li class='page-item'><a class='page-link' href='?page=$i&department=" . urlencode($selected_department) . "'>$i</a></li>";
                        }
                      }

                      // Next page link
                      if ($current_page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='?page=" . ($current_page + 1) . "&department=" . urlencode($selected_department) . "'>Next</a></li>";
                      }
                      ?>
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
            <!-- End Content -->
          </div>
          <!-- End Main Container -->
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addEmployeeForm" action="process_add_employee.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label for="employeeID" class="form-label">Employee ID</label>
              <input type="text" class="form-control" id="employeeID" name="employeeID" required>
            </div>
            <div class="mb-3">
              <label for="firstName" class="form-label">First Name</label>
              <input type="text" class="form-control" id="firstName" name="firstName" required>
            </div>
            <div class="mb-3">
              <label for="lastName" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lastName" name="lastName" required>
            </div>
            <div class="mb-3">
              <label for="department" class="form-label">Department</label>
              <select class="form-select" id="department" name="department" required>
                <option value="">Select Department</option>
                <option value="Admin">Admin</option>
                <option value="Department Head">Department Head</option>
                <option value="Dean of Studies">Dean of Studies</option>
                <option value="Elementary">Elementary</option>
                <option value="Highschool">High School</option>
                <option value="College">College</option>
                <option value="Non-Faculty">Non-Faculty</option>

              </select>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Employee</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Handle department selection change
    document.getElementById("departmentSelect").addEventListener("change", function () {
      const selectedDepartment = this.value;
      const url = new URL(window.location.href);
      url.searchParams.set("department", selectedDepartment);
      window.location.href = url.toString();
    });


  </script>

  <script>
    function toggleFilterDropdown() {
      var dropdown = document.getElementById("departmentFilter");
      dropdown.classList.toggle("active");
    }
    function handleSearch(event) {
      if (event.key === "Enter") {
        const search = document.getElementById("search").value;
        const url = new URL(window.location.href);
        url.searchParams.set("search", search);
        window.location.href = url.toString();
      }
    }
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Get the button and modal elements
      const addEmployeeBtn = document.getElementById('addEmployeeBtn');
      const addEmployeeModal = new bootstrap.Modal(document.getElementById('addEmployeeModal'));

      // Add click event listener to the button
      addEmployeeBtn.addEventListener('click', function () {
        addEmployeeModal.show();
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz4fnFO9gybBogGzAqKiLqlzW56cFg6NqT94GZwG7pEE6H1/j+hk6A6BvN"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-Y4oXHOgTA5gYotPoCV5Gr7C2lrrL+VnYs4mwSZcpkHmn+/vnZZaWk51W28S1df6o"
    crossorigin="anonymous"></script>
</body>

</html>