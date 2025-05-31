<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("includes/database.php");

$alertMessage = '';  // Variable to hold any alert messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize form inputs
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
    $role = filter_input(INPUT_POST, "role", FILTER_SANITIZE_SPECIAL_CHARS);

    // Determine the table and column based on the role
    $table = '';
    $idColumn = '';
    switch ($role) {
        case 'admin':
            $table = 'admin_credentials';
            $idColumn = 'ID';
            break;
        case 'deptHead':
        case 'dean':
            $table = 'deptHead_credentials';
            $idColumn = 'ID';
            break;
        case 'employee':
            $table = 'employee_credentials';
            $idColumn = 'ID';
            break;
        default:
            $alertMessage = 'Invalid role selected.';
            break;
    }

    // First, verify if the credentials exist
    if ($alertMessage === '') {
        $sql = "SELECT password FROM $table WHERE $idColumn = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $passwordHash);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $alertMessage = "Error in query: " . mysqli_error($conn);
        }

        // Check if a hash was retrieved and verify password
        if ($passwordHash && password_verify($password, $passwordHash)) {
            // For department head and dean, verify their position in user_details
            if ($role === 'deptHead' || $role === 'dean') {
                $positionCheck = "SELECT department FROM user_details WHERE ID = ?";
                $stmt = mysqli_prepare($conn, $positionCheck);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $username);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $actualPosition);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    // Verify if the selected role matches their actual position
                    $validRole = false;
                    if ($role === 'deptHead' && $actualPosition === 'Department Head') {
                        $validRole = true;
                    } elseif ($role === 'dean' && $actualPosition === 'Dean of Studies') {
                        $validRole = true;
                    }

                    if (!$validRole) {
                        $alertMessage = 'Error: You do not have the required position for this role.';
                    }
                } else {
                    $alertMessage = "Error in query: " . mysqli_error($conn);
                }
            }

            // If all checks pass, store session variables
            if (empty($alertMessage)) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                // Redirect based on role
                switch ($role) {
                    case 'admin':
                        header("Location: admin/admin_dashboard.php");
                        break;
                    case 'deptHead':
                    case 'dean':
                        header("Location: dept_head/deptHead_dashboard.php");
                        break;
                    case 'employee':
                        header("Location: employee/employee_dashboard.php");
                        break;
                    default:
                        header("Location: login.php");
                        break;
                }
                exit;
            }
        } else {
            $alertMessage = 'Error: Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRIMS Login</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: url('assets/img/background.png') no-repeat center center fixed;
        background-size: cover;
        position: relative;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(212, 189, 189, 0.479);
        z-index: 1;
    }

    .login-container {
        background-color: rgb(255, 255, 255);
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 750px;
        color: maroon;
        position: relative;
        z-index: 2;
    }

    .header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .login-logo {
        height: 150px;
        width: 170px;
        margin-right: 40px;
        margin-left: 20px;
    }

    .header h2 {
        font-size: 25px;
        font-weight: 900;
    }

    .role-group {
        margin-bottom: 1rem;
        text-align: center;
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }

    .role-group button {
        margin-right: 10px;
        padding: 0.60rem 1.5rem;
        background-color: #ddd;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        margin-top: -20px;
    }

    .role-group button.active {
        border: none;
        background-image: linear-gradient(180deg, rgb(219, 146, 11) 26%, rgb(248, 212, 51) 100%);
        color: white;
    }

    .input-group {
        margin-bottom: 1rem;
        text-align: center;
        display: flex;
        justify-content: center;
    }

    .input-group input {
        width: 500px;
        /* Fixed width */
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        background-color: #e6e6e6b6;
    }

    .input-group a {
        display: block;
        text-align: center;
        margin-top: 10px;
    }

    .btn {
        width: 80px;
        height: 40px;
        background-color: maroon;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        text-align: center;
        margin-right: 93px;
    }

    .btn:hover {
        opacity: 0.8;
    }

    .alert {
        width: 500px;
        /* Fixed width for alert */
        text-align: center;
        display: flex;
        justify-content: center;
        margin: 0 auto;
        /* Center horizontally */
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .error {
        color: maroon;
        margin-top: 10px;
    }
</style>

<body>
    <div class="login-container">
        <div class="header">
            <img src="assets/img/logo.png" alt="EUC HRIMS Logo" class="login-logo">
            <div>
                <h2>Human Resource</h2>
                <h2>Information Management System</h2>
                <h2>MSEUFCI</h2>
            </div>
        </div>



        <form id="loginForm" action="" method="POST">
            <div class="role-group">
                <button type="button" class="role-btn active" data-role="admin">Admin</button>
                <button type="button" class="role-btn" data-role="deptHead">Dept Head</button>
                <button type="button" class="role-btn" data-role="dean">Dean of Studies</button>
                <button type="button" class="role-btn" data-role="employee">Employee</button>
            </div>
            <input type="hidden" id="role" name="role" value="admin">
            <!-- Display Bootstrap alert message if any -->
            <?php if ($alertMessage): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $alertMessage ?>
                </div>
            <?php endif; ?>

            <!-- Input group centered -->
            <div class="input-group">
                <input type="text" id="username" name="username" placeholder="ID" required>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-group">
                <a href="register.php">Not registered yet? Click here</a>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-danger">Login</button>
            </div>
        </form>
    </div>

    <script src="assets/js/login.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>