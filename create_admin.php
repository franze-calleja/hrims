<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("includes/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize form inputs
    $adminID = filter_input(INPUT_POST, "adminID", FILTER_SANITIZE_SPECIAL_CHARS);
    $createPassword = filter_input(INPUT_POST, "createPassword", FILTER_SANITIZE_SPECIAL_CHARS);

    // Hash the password for security
    $passwordHash = password_hash($createPassword, PASSWORD_DEFAULT);

    // Start a transaction to ensure data consistency
    mysqli_begin_transaction($conn);

    try {
        // Insert into admin_credentials table
        $sqlCredentials = "INSERT INTO admin_credentials (ID, password) 
                           VALUES ('$adminID', '$passwordHash')";
        if (!mysqli_query($conn, $sqlCredentials)) {
            throw new Exception(mysqli_error($conn));
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Redirect to login page or another page after successful registration
        header("Location: login.php");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HRIMS Admin Registration</title>
  <link rel="stylesheet" href="assets/css/create_admin.css">
  <script src="assets/js/create.js"></script>

</head>

<body>
  <div class="register-container">
    <div class="register-header">
      <img src="assets/img/logo.png" alt="Health Service Office Logo" class="login-logo">
      <h2>Human Resource Information Management System</h2>
    </div>
    <form id="registerForm" action="create_admin.php" method="POST">
      <h2>Admin Registration</h2>
      <div class="input-group">
        <div>
          <label for="username">Admin ID</label>
          <input type="text" id="adminID" name="adminID" placeholder="Admin ID">
        </div>
      </div>

      <div class="input-group">
        <div>
          <label for="createPassword">Create Password</label>
          <input type="password" id="createPassword" name="createPassword" placeholder="Create Password" required>
        </div>
      </div>
    
      <div class="form-footer">
          <button type="submit" class="btn">Signup</button>
      </div>
      <div id="error" class="error"></div>
    </form>
  </div>

  <script>
    
  </script>

</body>

</html>