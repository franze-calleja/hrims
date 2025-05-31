<?php
session_start();
include("../includes/database.php");

// Display success message if set
if (isset($_SESSION['success_message'])) {
  echo "<script>alert('" . htmlspecialchars($_SESSION['success_message'], ENT_QUOTES) . "');</script>";
  unset($_SESSION['success_message']);
}

// Display error message if set
if (isset($_SESSION['error_message'])) {
  echo "<script>alert('" . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES) . "');</script>";
  unset($_SESSION['error_message']);
}

$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$selected_department = isset($_GET['department']) ? $_GET['department'] : '';
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$start_from = ($current_page - 1) * $records_per_page;

$query = "SELECT ID, firstName, lastName, department, jobTitle FROM user_details WHERE 1=1";
if ($selected_department) {
  $query .= " AND department = '" . mysqli_real_escape_string($conn, $selected_department) . "'";
}
if ($search_term) {
  $query .= " AND (firstName LIKE '%$search_term%' OR lastName LIKE '%$search_term%' OR department LIKE '%$search_term%' OR jobTitle LIKE '%$search_term%')";
}
$query .= " LIMIT $start_from, $records_per_page";
$result = mysqli_query($conn, $query);

$total_records_query = "SELECT COUNT(*) FROM user_details WHERE 1=1";
if ($selected_department) {
  $total_records_query .= " AND department = '" . mysqli_real_escape_string($conn, $selected_department) . "'";
}
if ($search_term) {
  $total_records_query .= " AND (firstName LIKE '%$search_term%' OR lastName LIKE '%$search_term%' OR department LIKE '%$search_term%' OR jobTitle LIKE '%$search_term%')";
}
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records = mysqli_fetch_array($total_records_result)[0];
$total_pages = ceil($total_records / $records_per_page);

// Fetch the employee data if an ID is provided for editing
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;
$employee_data = [];

if ($edit_id > 0) {
  $edit_query = "SELECT * FROM user_details WHERE ID = $edit_id";
  $edit_result = mysqli_query($conn, $edit_query);
  $employee_data = mysqli_fetch_assoc($edit_result);
}

// Display success message
if (isset($_SESSION['message'])) {
  echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>";
  unset($_SESSION['message']);
}
// Display error message
if (isset($_SESSION['error'])) {
  echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
  unset($_SESSION['error']);
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
          <!-- Main Container Area -->
          <div class="main-container">
            <!-- Content -->
            <div class="table-responsive">
              <div class="container table-scontainer">
                <h3 class="table-name">Upload Certificate</h3>

                <div class="search-container">
                  <input type="text" id="search" class="form-control" placeholder="Search..."
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
                            <button class='view-button' onclick=\"viewEmployee('" . $row['ID'] . "')\">
                              <i class='fas fa-eye'></i>
                            </button>
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
  <script>
  function viewEmployee(employeeID) {
    // Redirect to view_employee.php with the employee ID as a query parameter
    window.location.href = `certificate_manage.php?id=${employeeID}`;
  }
</script>


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

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz4fnFO9gybBogGzAqKiLqlzW56cFg6NqT94GZwG7pEE6H1/j+hk6A6BvN"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-Y4oXHOgTA5gYotPoCV5Gr7C2lrrL+VnYs4mwSZcpkHmn+/vnZZaWk51W28S1df6o"
    crossorigin="anonymous"></script>
</body>

</html>