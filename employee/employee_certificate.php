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

// Get user details from session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch admin details from the database
if ($role === 'employee') {
    // Prepare and execute the query
    $sql = "SELECT * FROM user_details WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // Assuming 'adminID' is the username
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the details
    if ($result->num_rows > 0) {
        $employeeDetails = $result->fetch_assoc();
    } else {
        $employeeDetails = null; // No details found
    }
    $stmt->close();
} else {
    $employeeDetails = null;
}

// Fetch certificates for the current employee
$sql = "SELECT * FROM employee_certificates WHERE employee_id = ? ORDER BY uploaded_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$certificates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Certificates</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .certificate-card {
            height: 100%;
            transition: transform 0.2s;
        }

        .certificate-card:hover {
            transform: translateY(-5px);
        }

        .certificate-preview {
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .pdf-preview {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .modal-header {
            background: #f8f9fa;
        }

        .certificate-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .upload-dropzone {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
        }

        .upload-dropzone:hover {
            border-color: #0d6efd;
            background: #e9ecef;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include("includes/sidebar.php"); ?>
            <div class="col p-0">
                <?php include("includes/header.php"); ?>
                <div class="container-fluid py-4">
                    <!-- Profile Dashboard Section -->
                    <div class="card mb-4 bg-light">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="../uploads/<?php echo htmlspecialchars($employeeDetails['profileImage']); ?>"
                                        alt="Profile Image" class="rounded-circle"
                                        style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="col">
                                    <h5 class="mb-1">Good Day,</h5>
                                    <h4 class="mb-2">
                                        <?php echo htmlspecialchars($employeeDetails['firstName'] . ' ' . $employeeDetails['lastName']); ?>
                                    </h4>
                                    <p class="text-muted mb-0"><?php echo date('F j, Y'); ?></p>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#uploadModal" style="background-color: #800000 !important;">
                                        <i class="fas fa-plus me-2"></i>Add Certificate
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Certificates Grid -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                        <?php foreach ($certificates as $cert): ?>
                            <div class="col">
                                <div class="card h-100 certificate-card shadow-sm">
                                    <?php
                                    $fileExtension = pathinfo($cert['file_path'], PATHINFO_EXTENSION);
                                    if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])):
                                        ?>
                                        <img src="../uploads/certificates/<?php echo htmlspecialchars($cert['file_path']); ?>"
                                            class="card-img-top certificate-preview" alt="Certificate Preview">
                                    <?php else: ?>
                                        <div class="pdf-preview">
                                            <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="certificate-badge">
                                        <span class="badge"
                                            style="background-color: #800000 !important;"><?php echo htmlspecialchars($cert['certificate_type']); ?></span>
                                    </div>

                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($cert['certificate_name']); ?>
                                        </h5>
                                        <p class="card-text text-muted">
                                            <small>
                                                <i class="fas fa-calendar-alt me-2"></i>
                                                <?php echo date('M d, Y', strtotime($cert['uploaded_date'])); ?>
                                            </small>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0">
                                        <a href="../uploads/certificates/<?php echo htmlspecialchars($cert['file_path']); ?>"
                                            class="btn btn-outline-primary w-100"
                                            style="color: #800000 !important; border-color: #800000 !important;"
                                            onmouseover="this.style.backgroundColor='#800000'; this.style.color='white !important';"
                                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='#800000 !important';"
                                            target="_blank">
                                            <i class="fas fa-eye me-2"></i>View Certificate
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Upload Modal -->
                    <div class="modal fade" id="uploadModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Upload New Certificate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="upload_certificate.php" method="POST" enctype="multipart/form-data"
                                        id="certificateForm">
                                        <div class="mb-3">
                                            <label for="certificate_name" class="form-label">Certificate Name</label>
                                            <input type="text" class="form-control" id="certificate_name"
                                                name="certificate_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="certificate_type" class="form-label">Certificate Type</label>
                                            <select class="form-select" id="certificate_type" name="certificate_type"
                                                required>
                                                <option value="">Select Type</option>
                                                <option value="Professional">Professional</option>
                                                <option value="Academic">Academic</option>
                                                <option value="Training">Training</option>
                                                <option value="Achievement">Achievement</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Certificate File</label>
                                            <div class="upload-dropzone" id="dropzone">
                                                <input type="file" class="d-none" id="certificate_file"
                                                    name="certificate_file" accept=".pdf,.jpg,.jpeg,.png" required>
                                                <div class="text-center">
                                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"
                                                        style="color: #800000 !important;"></i>
                                                    <p class="mb-2">Drag and drop your file here or click to browse</p>
                                                    <p class="text-muted small">Supported formats: PDF, JPG, PNG (Max.
                                                        5MB)</p>
                                                </div>
                                            </div>
                                            <div id="file-name" class="small text-muted mt-2"></div>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-secondary me-2"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary"
                                                style="background-color: #800000 !important;">Upload
                                                Certificate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload interaction
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('certificate_file');
        const fileNameDisplay = document.getElementById('file-name');

        dropzone.addEventListener('click', () => fileInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-primary');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('border-primary');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-primary');

            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileName(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                updateFileName(e.target.files[0]);
            }
        });

        function updateFileName(file) {
            fileNameDisplay.textContent = `Selected file: ${file.name}`;
        }
    </script>
</body>

</html>