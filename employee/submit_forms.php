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



// Fetch department heads from the database
$deptHeads = [];
$query = "SELECT dept_head_table.ID, user_details.firstName, user_details.lastName 
          FROM dept_head_table
          INNER JOIN user_details ON dept_head_table.ID = user_details.ID";
$result = mysqli_query($conn, $query);
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $deptHeads[] = $row;
  }
}

$deans = [];
$query = "SELECT dean_table.ID, user_details.firstName, user_details.lastName 
          FROM dean_table
          INNER JOIN user_details ON dean_table.ID = user_details.ID";
$result = mysqli_query($conn, $query);
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $deans[] = $row;
  }
}

$employee_id = $_SESSION['username']; // Assuming username stores the employee ID


// Query to fetch 5 most recent forms across all form types
$query = "
    (SELECT 
        'Leave Form' as form_type,
        created_at,
        status,
        CONCAT('Leave from ', start_date, ' to ', end_date) as description
    FROM leave_forms 
    WHERE employee_id = ?)
    UNION ALL
    (SELECT 
        'Log Form' as form_type,
        created_at,
        status,
        CONCAT(log_in_out, ' on ', log_date, ' at ', log_time) as description
    FROM log_form 
    WHERE employee_id = ?)
    UNION ALL
    (SELECT 
        'Make-up Class' as form_type,
        created_at,
        status,
        CONCAT('Make-up class for ', subject, ' on ', makeup_class_date) as description
    FROM make_up_forms 
    WHERE employee_id = ?)
    UNION ALL
    (SELECT 
        'Travel Order (Candelaria)' as form_type,
        created_at,
        status,
        CONCAT('Travel to ', destination, ' on ', travel_start_date) as description
    FROM travel_order_candelaria_forms 
    WHERE employee_id = ?)
    UNION ALL
    (SELECT 
        'Travel Order' as form_type,
        created_at,
        status,
        CONCAT('Travel to ', destination, ' from ', start_date, ' to ', return_date) as description
    FROM travel_order_forms 
    WHERE employee_id = ?)
    ORDER BY created_at DESC
    LIMIT 5";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
$stmt->bind_param("sssss", $employee_id, $employee_id, $employee_id, $employee_id, $employee_id);
$stmt->execute();
$result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Leave</title>
  <link rel="stylesheet" href="assets/css/forms.css">

</head>

<style>
  .status-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    margin-left: 8px;
  }

  .pending {
    background-color: #ffd700;
    color: #000;
  }

  .approved {
    background-color: #90EE90;
    color: #000;
  }

  .declined {
    background-color: #ffcccb;
    color: #000;
  }

  .form-history-list li {
    margin-bottom: 15px;
    padding: 10px;
    border-bottom: 1px solid #eee;
  }

  /* Modal styles */
  .modal-dialog-scrollable {
    max-height: 90vh;
    /* Maximum height of 90% of viewport height */
  }

  .modal-dialog-scrollable .modal-content {
    max-height: 85vh;
    /* Slightly less than dialog to account for header/footer */
  }

  .modal-dialog-scrollable .modal-body {
    overflow-y: auto;
    /* Enable vertical scrolling */
    padding: 1rem;
  }

  /* Make sure form content is properly spaced */
  .submission-container {
    padding: 0.5rem;
  }

  .input-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .input-group>div {
    width: 100%;
  }
</style>

<body>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("includes/sidebar.php"); ?>


      <div class="col p-0">
        <?php include("includes/header.php"); ?>


        <div class="container-fluid page-content mt-3">

          <!-- Main Container -->
          <div class="col-md-9 col-lg-10 main-container">
            <div class="container my-4">
              <!-- Content -->
              <div class="form-container">
                <div class="form-button">
                  <button class="btn btn-danger btn-large" onclick="showModal('leaveModal')">Submit Leave</button>
                </div>
                <div class="form-button">
                  <button class="btn btn-danger btn-large" onclick="showModal('travelOrderCModal')">Submit Travel
                    Order(Candelaria)</button>
                </div>
                <div class="form-button">
                  <button class="btn btn-danger btn-large" onclick="showModal('travelOrderModal')">Submit Travel
                    Order</button>
                </div>
                <div class="form-button">
                  <button class="btn btn-danger btn-large" onclick="showModal('makeUpClassModal')">Submit Make-up
                    Class</button>
                </div>
                <div class="form-button">
                  <button class="btn btn-danger btn-large" onclick="showModal('logModal')">Submit Log</button>
                </div>
              </div>

              <div class="form-history-container">
                <div class="form-submitted-history">
                  <p>Forms Submitted History</p>
                  <ul class="form-history-list">
                    <?php
                    if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                        $date = date('F d, Y', strtotime($row['created_at']));
                        $status_class = strtolower($row['status']);
                        echo "<li>
                        <strong>{$date}:</strong> 
                        {$row['form_type']} - {$row['description']}
                        <span class='status-badge {$status_class}'>{$row['status']}</span>
                    </li>";
                      }
                    } else {
                      echo "<li>No forms have been submitted yet.</li>";
                    }
                    ?>
                  </ul>
                </div>
              </div>



            </div>
          </div>
          <!-- End Main Container -->

        </div>
      </div>
    </div>
  </div>

  <!-- Leave Modal -->
  <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="leaveModalLabel">Submit Leave</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="submission-container">
            <form id="leaveForm" action="" method="POST">
              <h2>Leave Application</h2>
              <div class="input-group">
                <div>
                  <label for="empID">Employee ID</label>
                  <input type="text" id="empID" name="empID" placeholder="Employee ID"
                    value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                </div>
                <div>
                  <label for="leaveType">Leave Type</label>
                  <select id="leaveType" name="leaveType" required>
                    <option value="" disabled selected>Select Leave Type</option>
                    <option value="Vacation">Vacation</option>
                    <option value="Sick">Sick</option>
                    <option value="Birthday">Birthday</option>
                    <option value="Solo parent">Solo parent</option>
                    <option value="Maternity/Paternity">Maternity/Paternity</option>
                    <option value="Leave without pay">Leave without pay</option>
                    <option value="Long Leave">Long Leave (Academic project, medical reason, travel)</option>
                    <option value="Others">Others, Please specify</option>
                  </select>
                </div>
                <div id="specifyLeaveType" style="display: none;">
                  <label for="otherLeaveType">Please specify</label>
                  <input type="text" id="otherLeaveType" name="otherLeaveType" placeholder="Specify Leave Type">
                </div>
                <div>
                  <label for="deptHeadReport">DeptHead Reporting to:</label>
                  <select id="deptHeadReport" name="deptHeadReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deptHeads as $deptHead) {
                      echo "<option value='{$deptHead['ID']}'>Dean {$deptHead['firstName']} {$deptHead['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="deanReport">Dean Reporting to:</label>
                  <select id="deanReport" name="deanReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deans as $dean) {
                      echo "<option value='{$dean['ID']}'>Dean {$dean['firstName']} {$dean['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="startDate">Start Date</label>
                  <input type="date" id="startDate" name="startDate" required>
                </div>
                <div>
                  <label for="endDate">End Date</label>
                  <input type="date" id="endDate" name="endDate" required>
                </div>
                <div>
                  <label for="days">Days</label>
                  <input type="number" id="days" name="days" placeholder="Days of Leave" required>
                </div>
                <div>
                  <label for="place">Place</label>
                  <input type="text" id="place" name="place" placeholder="Place to spend this leave" required>
                </div>
                <div>
                  <label for="reason">Reason</label>
                  <textarea id="reason" name="reason" placeholder="Reason for Leave" required></textarea>
                </div>
              </div>
              <div id="error" class="error"></div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" form="leaveForm" class="btn">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Travel Order Modal -->
  <div class="modal fade" id="travelOrderCModal" tabindex="-1" aria-labelledby="travelOrderCModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="travelOrderCModalLabel">Submit Travel Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="submission-container">
            <form id="travelOrderCForm" action="" method="POST">
              <h2>Travel Order Application</h2>
              <div class="input-group">
                <div>
                  <label for="empID">Employee ID</label>
                  <input type="text" id="empID" name="empID" placeholder="Employee ID"
                    value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                </div>
                <div>
                  <label for="deptHeadReport">DeptHead Reporting to:</label>
                  <select id="deptHeadReport" name="deptHeadReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deptHeads as $deptHead) {
                      echo "<option value='{$deptHead['ID']}'>Dean {$deptHead['firstName']} {$deptHead['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="deanReport">Dean Reporting to:</label>
                  <select id="deanReport" name="deanReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deans as $dean) {
                      echo "<option value='{$dean['ID']}'>Dean {$dean['firstName']} {$dean['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="destination">Destination</label>
                  <input type="text" id="destination" name="destination" placeholder="Destination" required>
                </div>
                <div>
                  <label for="purpose">Purpose</label>
                  <input type="text" id="purpose" name="purpose" placeholder="Purpose" required>
                </div>
                <div>
                  <label for="travelStartDate">Date</label>
                  <input type="date" id="travelStartDate" name="travelStartDate" placeholder="Start Date" required>
                </div>
                <div>
                  <label for="time">Time</label>
                  <input type="time" id="time" name="travelTime" placeholder="Time" required>
                </div>
                <div>
                  <label for="time">Return Time</label>
                  <input type="time" id="time" name="returnTime" placeholder="Time" required>
                </div>
              </div>
              <div id="error" class="error"></div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" form="travelOrderCForm" class="btn">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Travel Order Modal -->
  <div class="modal fade" id="travelOrderModal" tabindex="-1" aria-labelledby="travelOrderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="travelOrderModalLabel">Submit Travel Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="submission-container">
            <form id="travelOrderForm" action="" method="POST">
              <h2>Travel Order Application</h2>
              <div class="input-group">
                <div>
                  <label for="empID">Employee ID</label>
                  <input type="text" id="empID" name="empID" placeholder="Employee ID"
                    value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                </div>
                <div>
                  <label for="deptHeadReport">DeptHead Reporting to:</label>
                  <select id="deptHeadReport" name="deptHeadReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deptHeads as $deptHead) {
                      echo "<option value='{$deptHead['ID']}'>Dean {$deptHead['firstName']} {$deptHead['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="deanReport">Dean Reporting to:</label>
                  <select id="deanReport" name="deanReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deans as $dean) {
                      echo "<option value='{$dean['ID']}'>Dean {$dean['firstName']} {$dean['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="travelDestination">Destination</label>
                  <input type="text" id="travelDestination" name="travelDestination" placeholder="Destination" required>
                </div>
                <div>
                  <label for="travelStartDate">Start Date</label>
                  <input type="date" id="travelStartDate" name="travelStartDate" placeholder="Start Date" required>
                </div>
                <div>
                  <label for="travelReturnDate">Return Date</label>
                  <input type="date" id="travelReturnDate" name="travelReturnDate" placeholder="Return Date" required>
                </div>
                <div>
                  <label for="travelPurpose">Purpose</label>
                  <input type="text" id="travelPurpose" name="travelPurpose" placeholder="Purpose" required>
                </div>
                <div>
                  <label for="travelCashAdvance">Cash Advance</label>
                  <input type="text" id="travelCashAdvance" name="travelCashAdvance" placeholder="Cash Advance"
                    required>
                </div>
                <div>
                  <label for="travelReportDate">Report Date</label>
                  <input type="date" id="travelReportDate" name="travelReportDate" placeholder="Report Date" required>
                </div>
              </div>
              <div id="error" class="error"></div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn" form="travelOrderForm">Submit</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Make-Up Class Modal -->
  <div class="modal fade" id="makeUpClassModal" tabindex="-1" aria-labelledby="makeUpClassModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="makeUpClassModalLabel">Submit Make-Up Class</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="submission-container">
            <form id="makeUpClassForm" action="" method="POST">
              <h2>Make-Up Class Application</h2>
              <div class="input-group">
                <div>
                  <label for="empID">Employee ID</label>
                  <input type="text" id="empID" name="empID" placeholder="Employee ID"
                    value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                </div>
                <div>
                  <label for="deptHeadReport">DeptHead Reporting to:</label>
                  <select id="deptHeadReport" name="deptHeadReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deptHeads as $deptHead) {
                      echo "<option value='{$deptHead['ID']}'>DeptHead {$deptHead['firstName']} {$deptHead['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="deanReport">Dean Reporting to:</label>
                  <select id="deanReport" name="deanReport" required>
                    <option value="" disabled selected>Select Dean</option>
                    <?php
                    foreach ($deans as $dean) {
                      echo "<option value='{$dean['ID']}'>Dean {$dean['firstName']} {$dean['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="subject">Subject</label>
                  <input type="text" id="subject" name="subject" placeholder="Subject" required>
                </div>
                <div>
                  <label for="regClassDate">Regular Class Date</label>
                  <input type="date" id="regClassDate" name="regClassDate" required>
                </div>
                <div>
                  <label for="regClassTime">Regular Class Time</label>
                  <input type="time" id="regClassTime" name="regClassTime" required>
                </div>
                <div>
                  <label for="regClassRoom">Regular Class Room</label>
                  <input type="text" id="regClassRoom" name="regClassRoom" placeholder="Regular Class Room" required>
                </div>
                <div>
                  <label for="makeupClassDate">Make-up Class Date</label>
                  <input type="date" id="makeupClassDate" name="makeupClassDate" required>
                </div>
                <div>
                  <label for="makeupClassTime">Make-up Class Time</label>
                  <input type="time" id="makeupClassTime" name="makeupClassTime" required>
                </div>
                <div>
                  <label for="makeupClassRoom">Make-up Class Room</label>
                  <input type="text" id="makeupClassRoom" name="makeupClassRoom" placeholder="Make-up Class Room"
                    required>
                </div>
                <div>
                  <label for="reason">Reason</label>
                  <textarea id="reason" name="reason" placeholder="Reason for Make-Up Class" required></textarea>
                </div>
              </div>
              <div id="error" class="error"></div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn" form="makeUpClassForm">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Log Modal -->
  <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logModalLabel">Submit Log</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="submission-container">
            <form id="logForm" action="" method="POST">
              <h2>Log Entry</h2>
              <div class="input-group">
                <div>
                  <label for="empID">Employee ID</label>
                  <input type="text" id="empID" name="empID" placeholder="Employee ID"
                    value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                </div>
                <div>
                  <label for="deptHeadReport">Dean Reporting to:</label>
                  <select id="deptHeadReport" name="deptHeadReport" required>
                    <option value="" disabled selected>Select DeptHead</option>
                    <?php
                    foreach ($deptHeads as $deptHead) {
                      echo "<option value='{$deptHead['ID']}'>DeptHead {$deptHead['firstName']} {$deptHead['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="deanReport">Dean Reporting to:</label>
                  <select id="deanReport" name="deanReport" required>
                    <option value="" disabled selected>Select Dean</option>
                    <?php
                    foreach ($deans as $dean) {
                      echo "<option value='{$dean['ID']}'>Dean {$dean['firstName']} {$dean['lastName']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div>
                  <label for="logInOut">Log In or Out</label>
                  <select name="logInOut" id="logInOut">
                    <option value="" disabled selected>Select if IN or OUT</option>
                    <option value="Log-In">Log In</option>
                    <option value="Log-Out">Log Out</option>
                  </select>
                </div>
                <div>
                  <label for="logDate">Log Date</label>
                  <input type="date" id="logDate" name="logDate" placeholder="Log Date" required>
                </div>
                <div>
                  <label for="logTime">Time</label>
                  <input type="time" id="logTime" name="logTime" placeholder="Time" required>
                </div>
                <div>
                  <label for="reason">Reason</label>
                  <textarea id="reason" name="reason" placeholder="Reason" required></textarea>
                </div>
              </div>
              <div id="error" class="error"></div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn" form="logForm">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Include Bootstrap JS and Popper.js -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

  <script>
    function showModal(modalId) {
      var modal = new bootstrap.Modal(document.getElementById(modalId));
      modal.show();
    }
    document.getElementById('leaveType').addEventListener('change', function () {
      var specifyLeaveType = document.getElementById('specifyLeaveType');
      if (this.value === 'Others') {
        specifyLeaveType.style.display = 'block';
      } else {
        specifyLeaveType.style.display = 'none';
        document.getElementById('otherLeaveType').value = ''; // Clear the input if hidden
      }
    });

    document.getElementById('leaveForm').addEventListener('submit', function (e) {
      e.preventDefault();

      var form = this;
      var formData = new FormData(form);

      fetch('actions/submit_leave.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          alert('Error submitting leave form. Please try again.');
          console.error('Error:', error);
        });
    });
  </script>

  <script>
    function showModal(modalId) {
      var modal = new bootstrap.Modal(document.getElementById(modalId));
      modal.show();
    }
    document.getElementById('makeUpClassForm').addEventListener('submit', function (e) {
      e.preventDefault();

      var form = this;
      var formData = new FormData(form);

      fetch('actions/submit_make_up_class.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          alert('Error submitting make-up class form. Please try again.');
          console.error('Error:', error);
        });
    });
  </script>

  <script>
    function showModal(modalId) {
      var modal = new bootstrap.Modal(document.getElementById(modalId));
      modal.show();
    }
    document.getElementById('logForm').addEventListener('submit', function (e) {
      e.preventDefault();

      var form = this;
      var formData = new FormData(form);

      fetch('actions/submit_log.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          alert('Error submitting log form. Please try again.');
          console.error('Error:', error);
        });
    });
  </script>

  <script>
    function showModal(modalId) {
      var modal = new bootstrap.Modal(document.getElementById(modalId));
      modal.show();
    }
    document.getElementById('travelOrderCForm').addEventListener('submit', function (e) {
      e.preventDefault();

      var form = this;
      var formData = new FormData(form);

      fetch('actions/submit_travel_order_candelaria.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          alert('Error submitting travel order form. Please try again.');
          console.error('Error:', error);
        });
    });
  </script>


  <script>
    function showModal(modalId) {
      var modal = new bootstrap.Modal(document.getElementById(modalId));
      modal.show();
    }
    document.getElementById('travelOrderForm').addEventListener('submit', function (e) {
      e.preventDefault();

      var form = this;
      var formData = new FormData(form);

      fetch('actions/submit_travel_order.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          alert('Error submitting travel order form. Please try again.');
          console.error('Error:', error);
        });
    });
  </script>



</body>

</html>