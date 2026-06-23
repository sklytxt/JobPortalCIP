<?php
require_once '../classes/AuthClass.php';
$displayResult = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthClass::register();
    if ($result === true) {
        header("Location: login.php");
        exit();
    }
    $displayResult = $result;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Job System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="robots" content="noindex, follow">
    <style>
        .dynamic-fields { display: none; }
        .password-field { position: relative; }
        .password-toggle { 
            position: absolute; 
            right: 15px; 
            top: 38px; 
            cursor: pointer !important; 
            color: #6c757d; 
            z-index: 9999 !important; 
            pointer-events: auto !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 centered-bg-wrapper register-visual-pane d-flex align-items-center justify-content-center">
        <div class="gradient-overlay position-absolute top-0 start-0 w-100 h-100"></div>
        <div class="register-card-centered p-4 p-sm-5 m-3" style="max-width: 600px; width: 100%;">
            <h1 class="jobful-title">WorkJourney</h1>
            <h2 class="fw-bold text-dark mb-4" style="font-size: 1.2rem;">Create your account</h2>
            
            <?php if (!empty($displayResult)): ?>
                <div class="alert alert-danger mt-3 mb-4"><?= htmlspecialchars($displayResult) ?></div>
            <?php endif; ?>

            <form id="regForm" action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label text-muted-custom mb-0 fw-medium">Account Type</label>
                    <select class="form-control-minimal" name="usertype" id="usertype" required onchange="toggleFields()">
                        <option value="">Select Account Type</option>
                        <option value="jobseeker" <?= (isset($_POST['usertype']) && $_POST['usertype'] === 'jobseeker') ? 'selected' : '' ?>>Job Seeker</option>
                        <option value="employer" <?= (isset($_POST['usertype']) && $_POST['usertype'] === 'employer') ? 'selected' : '' ?>>Employer</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="mb-4">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Full Name</label>
                            <input class="form-control-minimal" type="text" name="name" id="name" placeholder="Enter name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Email Address</label>
                            <input class="form-control-minimal" type="email" name="email" placeholder="email@example.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                        </div>
                        <div class="mb-4 password-field">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Password</label>
                            <input class="form-control-minimal no-js-password-1" type="password" name="password" id="password" placeholder="*************" value="<?= isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '' ?>" required>
                            <span class="password-toggle" onclick="togglePasswordVisibility('password', this)"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-4">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Location</label>
                            <input class="form-control-minimal" type="text" name="location" placeholder="City, Country" value="<?= isset($_POST['location']) ? htmlspecialchars($_POST['location']) : '' ?>" required>
                        </div>
                        <div id="seeker-fields" class="dynamic-fields">
                            <div class="mb-4">
                                <label class="form-label text-muted-custom mb-0 fw-medium">Job Title</label>
                                <input class="form-control-minimal" type="text" name="job_title" placeholder="e.g. Frontend Developer" value="<?= isset($_POST['job_title']) ? htmlspecialchars($_POST['job_title']) : '' ?>">
                            </div>
                        </div>
                        <div id="employer-fields" class="dynamic-fields">
                            <div class="mb-4">
                                <label class="form-label text-muted-custom mb-0 fw-medium">Company Name</label>
                                <input class="form-control-minimal" type="text" name="company_name" placeholder="Company Inc." value="<?= isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : '' ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Contact Number</label>
                            <input class="form-control-minimal" type="tel" name="contact_number" id="contact_number" placeholder="Enter your contact number" value="<?= isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : '' ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="mb-4 password-field">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Confirm Password</label>
                            <input class="form-control-minimal no-js-password-2" type="password" name="confirm_password" id="confirm_password" placeholder="*************" value="<?= isset($_POST['confirm_password']) ? htmlspecialchars($_POST['confirm_password']) : '' ?>" required>
                            <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password', this)"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-4">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Profile Image</label>
                            <input class="form-control-minimal pt-2" type="file" name="profile_image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-4">
                    <button type="submit" class="btn btn-gradient fw-medium">Sign Up</button>
                    <a href="login.php" class="link-dark-custom fw-medium text-muted-custom text-dark">Already registered? <strong>Sign in</strong></a>
                </div>
            </form>
        </div>
    </div>
    <script>
        function toggleFields() {
            const type = document.getElementById('usertype').value;
            document.getElementById('seeker-fields').style.display = (type === 'jobseeker') ? 'block' : 'none';
            document.getElementById('employer-fields').style.display = (type === 'employer') ? 'block' : 'none';
        }

        window.addEventListener('DOMContentLoaded', toggleFields);

        function togglePasswordVisibility(fieldId, toggleEl) {
            const field = document.getElementById(fieldId);
            const icon = toggleEl.querySelector('i');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>