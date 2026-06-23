<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/AuthClass.php';
require_once '../classes/employerClass.php';

if (isset($_GET['logout'])) {
    AuthClass::logout();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['update_job'])) {
    // 1. Get the Job ID and the Employer ID
    $jobId = $_POST['update_job_id'];
    $employerId = $_SESSION['user_id']; // Ensure session is started

    // 2. Prepare the data array to match what your updateJob method expects
    $data = [
        'title'           => $_POST['title'],
        'description'     => $_POST['description'],
        'salary'          => $_POST['salary'],
        'jobType'         => $_POST['jobType'],
        'workSetup'       => $_POST['workSetup'],
        'experienceLevel' => $_POST['experienceLevel'],
        'location'        => $_POST['location']
    ];

    // 3. Call the class method
    if (EmployerClass::updateJob($jobId, $employerId, $data)) {
        // Success: Refresh the page to show updated data
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } else {
        $error = "Failed to update job.";
    }
}

$user = UserClass::getUserById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jobtitle'])) {

    // Trim everything first so whitespace-only values are treated as blank
    $jobtitle        = trim($_POST['jobtitle'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $salary           = trim($_POST['salary'] ?? '');
    $jobtype          = trim($_POST['jobtype'] ?? '');
    $worksetup        = trim($_POST['worksetup'] ?? '');
    $experiencelevel  = trim($_POST['experiencelevel'] ?? '');
    $location         = trim($_POST['location'] ?? '');
    $maxapplicants    = trim($_POST['maxapplicants'] ?? '');

    $errors = [];

    if ($jobtitle === '') {
        $errors[] = 'Job Title is required.';
    }
    if ($description === '') {
        $errors[] = 'Job Description is required.';
    }
    if ($salary === '') {
        $errors[] = 'Salary is required.';
    } elseif (!is_numeric($salary) || (float)$salary <= 0) {
        $errors[] = 'Salary must be a valid number greater than 0.';
    }
    if ($jobtype === '') {
        $errors[] = 'Job Type is required.';
    }
    if ($worksetup === '') {
        $errors[] = 'Work Setup is required.';
    }
    if ($experiencelevel === '') {
        $errors[] = 'Experience Level is required.';
    }
    if ($location === '') {
        $errors[] = 'Location is required.';
    }
    if ($maxapplicants === '') {
        $errors[] = 'Max Number of Hires is required.';
    } elseif (!ctype_digit($maxapplicants) || (int)$maxapplicants < 1) {
        $errors[] = 'Max Number of Hires must be a whole number of at least 1.';
    }

    if (!empty($errors)) {
        $errorMsg = addslashes(implode('\n', $errors));
        echo "<script>alert('" . $errorMsg . "'); window.location='employer.php?tab=postJob';</script>";
    } else {
        $status = EmployerClass::postJob(
            $_SESSION['user_id'],
            $jobtitle,
            $description,
            $salary,
            $jobtype,
            $worksetup,
            $experiencelevel,
            $location,
            $maxapplicants
        );

        if ($status === true) {
            echo "<script>alert('Job Posted Successfully!'); window.location='employer.php';</script>";
        } else {
            echo "<script>alert('$status');</script>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appid']) && isset($_POST['status'])) {
    $appid  = trim($_POST['appid'] ?? '');
    $jobid  = trim($_POST['jobid'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($appid === '' || $status === '') {
        echo "<script>alert('Invalid request. Please try again.'); window.location='employer.php?tab=applicants';</script>";
    } else {
        if ($status == 'Accepted') {
            if ($jobid === '') {
                echo "<script>alert('Invalid job reference.'); window.location='employer.php?tab=applicants';</script>";
            } else {
                EmployerClass::acceptApplicant($appid, $jobid);
                echo "<script>alert('Status Updated!'); window.location='employer.php';</script>";
            }
        } else {
            EmployerClass::updateApplicationStatus($appid, 'Rejected');
            echo "<script>alert('Status Updated!'); window.location='employer.php';</script>";
        }
    }
}

if (isset($_POST['delete_job_id'])) {
    $deleteJobId = trim($_POST['delete_job_id'] ?? '');

    if ($deleteJobId === '') {
        echo "<script>alert('Invalid job selected for deletion.'); window.location='employer.php';</script>";
    } elseif (EmployerClass::deleteJob($deleteJobId, $_SESSION['user_id'])) {
        echo "<script>alert('Job deleted successfully.'); window.location='employer.php';</script>";
    } else {
        echo "<script>alert('Failed to delete job.');</script>";
    }
}

// Active Tab Tracking
$activeTab = $_GET['tab'] ?? 'manageJobs';

// Filtering Inputs
$jobSearch      = $_GET['job_search'] ?? '';
$expFilter      = $_GET['exp_filter'] ?? '';
$typeFilter     = $_GET['type_filter'] ?? '';
$setupFilter    = $_GET['setup_filter'] ?? '';
$salaryFilter   = $_GET['salary_filter'] ?? '';
$locationFilter = $_GET['location_filter'] ?? '';
$appSearch      = $_GET['app_search'] ?? '';
$statusFilter   = $_GET['status_filter'] ?? '';

// PAGINATION Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$employerId = $_SESSION['user_id'];

$totalJobs = EmployerClass::getTotalJobsCount($employerId, $jobSearch);
$totalPages = ceil($totalJobs / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkJourney | Employer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@700&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="styles.css" rel="stylesheet" />
</head>

<body class="home-page-body">

    <nav class="navbar navbar-expand-lg bg-green border-bottom sticky-top py-0">
        <div class="container">
            <div class="d-flex align-items-center gap-2 my-2 my-lg-0 flex-grow-1 flex-lg-grow-0">
                <a class="navbar-brand jobful-title text-white m-0 pe-2 text-decoration-none" href="home.php">WorkJourney</a>
            </div>
            <button class="navbar-toggler border-0 navbar-dark ms-auto my-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto align-items-center column-nav gap-0 gap-lg-4 py-2 py-lg-0">
                    <li class="nav-item">
                        <a class="nav-link d-flex flex-column align-items-center text-center" href="home.php">
                            <i class="fa fa-home fs-5 mb-1"></i><span class="nav-label">Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex flex-column align-items-center text-center" href="job-list.php">
                            <i class="fa fa-list-ul fs-5 mb-1"></i><span class="nav-label">Job List</span>
                        </a>
                    </li>
                    <?php if (isset($user['Usertype']) && strtolower($user['Usertype']) !== 'employer'): ?>
    <li class="nav-item">
        <a class="nav-link d-flex flex-column align-items-center text-center" href="jobs.php">
            <i class="fa fa-briefcase fs-5 mb-1"></i>
            <span class="nav-label">Jobs</span>
        </a>
    </li>
<?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex flex-column align-items-center text-center active" href="employer.php">
                            <i class="fa fa-building fs-5 mb-1"></i><span class="nav-label">Employer</span>
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav ms-auto align-items-center border-start-lg ps-lg-4 py-2 py-lg-0">
                    <div class="dropdown">
                        <a class="d-flex flex-column align-items-center text-center text-decoration-none dropdown-toggle nav-link-profile" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar-sm mb-1 d-flex align-items-center justify-content-center overflow-hidden" style="width: 32px; height: 32px; border-radius: 50%; background: #eee;">
                                <?php if (!empty($user['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $user['ProfileImagePath'])): ?>
                                    <img src="../uploads/profile_img/<?= htmlspecialchars($user['ProfileImagePath']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <span class="text-secondary fw-bold"><?= substr($user['FullName'], 0, 1) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="nav-label text-white-50">Me</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                            <li><a class="dropdown-item py-2 small-note" href="profile.php"><i class="fa fa-user-o me-2"></i> My Profile</a></li>
                            <li><a class="dropdown-item py-2 small-note" href="dashboard.php"><i class="fa fa-th-large me-2"></i> Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 small-note text-danger" href="employer.php?logout=1"><i class="fa fa-sign-out me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <div class="container py-4" style="max-width: 1000px;">

            <div class="card p-4 mb-4 border-0 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center overflow-hidden" style="width: 60px; height: 60px; border-radius: 50%; background: #eee;">
                        <?php if (!empty($user['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $user['ProfileImagePath'])): ?>
                            <img src="../uploads/profile_img/<?= htmlspecialchars($user['ProfileImagePath']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span class="text-secondary fw-bold fs-4"><?= substr($user['FullName'], 0, 1) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="h5 mb-0 fw-bold"><?= htmlspecialchars($user['FullName']) ?></h2>
                        <p class="text-secondary mb-0">Employer Dashboard</p>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs border-0 mb-3" id="employerTabs">
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'manageJobs' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#manageJobs">My Jobs</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'postJob' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#postJob">Post New Job</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'applicants' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#applicants">Applicants</button>
                </li>
            </ul>

            <div class="tab-content card p-4 border-0 shadow-sm">

                <div class="tab-pane fade <?= $activeTab === 'applicants' ? 'show active' : '' ?>" id="applicants">
                    <h5 class="fw-bold mb-3">Incoming Applicants</h5>

                    <form method="GET" action="employer.php" class="row g-2 mb-3 align-items-end">
                        <input type="hidden" name="tab" value="applicants">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold mb-1">Search Applicant / Job Title</label>
                            <input type="text" name="app_search" class="form-control form-control-sm" placeholder="e.g. Juan Dela Cruz or Developer" value="<?= htmlspecialchars($appSearch) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Status</label>
                            <select name="status_filter" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Accepted" <?= $statusFilter === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-success w-100"><i class="fa fa-search"></i> Search</button>
                        </div>
                        <?php if ($appSearch !== '' || $statusFilter !== ''): ?>
                        <div class="col-md-2">
                            <a href="employer.php?tab=applicants" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
                        </div>
                        <?php endif; ?>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th>Applied For</th>
                                    <th>Date Applied</th>
                                    <th>Resume</th>
                                    <th>Cover Letter</th>
                                    <th>Portfolio</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $applicants = EmployerClass::getApplicantsByEmployer($_SESSION['user_id'], $appSearch, $statusFilter);
                                if ($applicants && $applicants->num_rows > 0):
                                    while ($app = $applicants->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['FullName']) ?></td>
                                    <td><?= htmlspecialchars($app['JobTitle']) ?></td>
                                    <td><?= date('M d, Y', strtotime($app['AppliedDate'])) ?></td>
                                    <td><a href="../uploads/resumes/<?= htmlspecialchars($app['ResumePath']) ?>" target="_blank" class="btn btn-sm btn-outline-success">Download</a></td>
                                    <td>
                                        <?php if (!empty($app['coverletter'])): ?>
                                            <a href="../uploads/coverletter/<?= htmlspecialchars($app['coverletter']) ?>" target="_blank" class="btn btn-sm btn-outline-info">Download</a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['PortfolioPath'])): ?>
                                            <a href="<?= htmlspecialchars($app['PortfolioPath']) ?>" target="_blank"><?= htmlspecialchars($app['PortfolioPath']) ?></a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($app['Status'] == 'Pending'): ?>
                                            <form method="POST" action="employer.php" class="d-inline">
                                                <input type="hidden" name="jobid" value="<?= $app['JobID'] ?>">
                                                <input type="hidden" name="appid" value="<?= $app['ApplicationID'] ?>">
                                                <button name="status" value="Accepted" class="btn btn-sm btn-success me-1">Accept</button>
                                                <button name="status" value="Rejected" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                            
                                        <?php else: ?>
                                            <span class="badge <?= $app['Status'] == 'Accepted' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $app['Status'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <tr><td colspan="7" class="text-center text-muted py-3">No applicants found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade <?= $activeTab === 'manageJobs' ? 'show active' : '' ?>" id="manageJobs">
                    <h5 class="fw-bold mb-3">Your Posted Jobs</h5>

                    <form method="GET" action="employer.php" class="row g-2 mb-3 align-items-end">
                        <input type="hidden" name="tab" value="manageJobs">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Search Title</label>
                            <input type="text" name="job_search" class="form-control form-control-sm" placeholder="e.g. Developer" value="<?= htmlspecialchars($jobSearch) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Experience</label>
                            <select name="exp_filter" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="Entry-Level" <?= $expFilter === 'Entry-Level' ? 'selected' : '' ?>>Entry-Level</option>
                                <option value="Mid-Level" <?= $expFilter === 'Mid-Level' ? 'selected' : '' ?>>Mid-Level</option>
                                <option value="Senior-Level" <?= $expFilter === 'Senior-Level' ? 'selected' : '' ?>>Senior-Level</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Type</label>
                            <select name="type_filter" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="Full-Time" <?= $typeFilter === 'Full-Time' ? 'selected' : '' ?>>Full-Time</option>
                                <option value="Part-Time" <?= $typeFilter === 'Part-Time' ? 'selected' : '' ?>>Part-Time</option>
                                <option value="Contract" <?= $typeFilter === 'Contract' ? 'selected' : '' ?>>Contract</option>
                                <option value="Internship" <?= $typeFilter === 'Internship' ? 'selected' : '' ?>>Internship</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Setup</label>
                            <select name="setup_filter" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="On-Site" <?= $setupFilter === 'On-Site' ? 'selected' : '' ?>>On-Site</option>
                                <option value="Remote" <?= $setupFilter === 'Remote' ? 'selected' : '' ?>>Remote</option>
                                <option value="Hybrid" <?= $setupFilter === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Location</label>
                            <input type="text" name="location_filter" class="form-control form-control-sm" placeholder="e.g. Makati" value="<?= htmlspecialchars($locationFilter) ?>">
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-search"></i> Filter</button>
                            <?php if ($jobSearch !== '' || $expFilter !== '' || $typeFilter !== '' || $setupFilter !== '' || $locationFilter !== ''): ?>
                                <a href="employer.php?tab=manageJobs" class="btn btn-sm btn-outline-secondary">Clear filters</a>
                            <?php endif; ?>
                        </div>
                    </form>

                  <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Experience</th>
                <th>Type/Setup</th>
                <th>Salary</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $jobs = EmployerClass::getJobsByEmployer($_SESSION['user_id'], $jobSearch, $expFilter, $typeFilter, $setupFilter, $salaryFilter, $locationFilter, $limit, $offset);
            if ($jobs && $jobs->num_rows > 0):
                while ($job = $jobs->fetch_assoc()):
                    $isFilled = ($job['Status'] === 'Filled');
                    $statusClass = $isFilled ? 'text-danger' : 'text-success';
            ?>
            <tr>
                <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($job['JobTitle']) ?></div>
                    <small class="text-muted">
                        Status: <span class="<?= $statusClass ?> fw-bold"><?= htmlspecialchars($job['Status']) ?></span>
                    </small>
                </td>
                <td>
                    <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($job['ExperienceLevel']) ?></span>
                </td>
                <td>
                    <div><?= htmlspecialchars($job['JobType']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($job['WorkSetup']) ?></small>
                </td>
                <td class="fw-bold">₱ <?= number_format((float)$job['Salary'], 2) ?></td>
                <td><?= htmlspecialchars($job['Location']) ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="job-details.php?id=<?= $job['JobID'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-eye"></i>
                        </a>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this job?');" class="d-inline">
                            <input type="hidden" name="delete_job_id" value="<?= $job['JobID'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>

                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editModal<?= $job['JobID'] ?>">
                            <i class="fa fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <div class="modal fade" id="editModal<?= $job['JobID'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST"> 
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Edit Job</h5></div>
                            <div class="modal-body">
    <input type="hidden" name="update_job_id" value="<?= $job['JobID'] ?>">
    
    <div class="mb-2">
        <label>Title</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['JobTitle']) ?>" required>
    </div>
    
    <div class="mb-2">
        <label>Description</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($job['Description']) ?></textarea>
    </div>
    
    <div class="mb-2">
        <label>Salary</label>
        <input type="number" name="salary" class="form-control" value="<?= htmlspecialchars($job['Salary']) ?>">
    </div>

    <div class="mb-2">
        <label>Experience</label>
        <select name="experienceLevel" class="form-select">
            <option value="Entry-Level" <?= $job['ExperienceLevel'] === 'Entry-Level' ? 'selected' : '' ?>>Entry-Level</option>
            <option value="Mid-Level" <?= $job['ExperienceLevel'] === 'Mid-Level' ? 'selected' : '' ?>>Mid-Level</option>
            <option value="Senior-Level" <?= $job['ExperienceLevel'] === 'Senior-Level' ? 'selected' : '' ?>>Senior-Level</option>
        </select>
    </div>

    <div class="mb-2">
        <label>Type</label>
        <select name="jobType" class="form-select">
            <option value="Full-Time" <?= $job['JobType'] === 'Full-Time' ? 'selected' : '' ?>>Full-Time</option>
            <option value="Part-Time" <?= $job['JobType'] === 'Part-Time' ? 'selected' : '' ?>>Part-Time</option>
            <option value="Contract" <?= $job['JobType'] === 'Contract' ? 'selected' : '' ?>>Contract</option>
            <option value="Internship" <?= $job['JobType'] === 'Internship' ? 'selected' : '' ?>>Internship</option>
        </select>
    </div>

    <div class="mb-2">
        <label>Setup</label>
        <select name="workSetup" class="form-select">
            <option value="On-Site" <?= $job['WorkSetup'] === 'On-Site' ? 'selected' : '' ?>>On-Site</option>
            <option value="Remote" <?= $job['WorkSetup'] === 'Remote' ? 'selected' : '' ?>>Remote</option>
            <option value="Hybrid" <?= $job['WorkSetup'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
        </select>
    </div>

    <div class="mb-2">
        <label>Location</label>
        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['Location']) ?>">
    </div>
</div>
                            <div class="modal-footer"><button type="submit" name="update_job" class="btn btn-primary">Save Changes</button></div>
                        </div>
                    </form>
                </div>
            </div>
            <?php 
                endwhile;
            else: 
            ?>
            <tr><td colspan="6" class="text-center text-muted py-3">No posted jobs found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
            </div>

                <div class="tab-pane fade <?= $activeTab === 'postJob' ? 'show active' : '' ?>" id="postJob">
                    <h5 class="fw-bold mb-4">Create a New Job</h5>
                    <form action="employer.php" method="POST" class="needs-validation" novalidate onsubmit="return validatePostJobForm(this);">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Title</label>
                            <input type="text" name="jobtitle" class="form-control" placeholder="e.g. Senior Frontend Developer" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Job Type</label>
                                <select name="jobtype" class="form-select" required>
                                    <option value="" disabled selected>Select job type</option>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Work Setup</label>
                                <select name="worksetup" class="form-select" required>
                                    <option value="" disabled selected>Select work setup</option>
                                    <option value="On-Site">On-Site</option>
                                    <option value="Remote">Remote</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Experience</label>
                                <select name="experiencelevel" class="form-select" required>
                                    <option value="" disabled selected>Select experience level</option>
                                    <option value="Entry-Level">Entry-Level</option>
                                    <option value="Mid-Level">Mid-Level</option>
                                    <option value="Senior-Level">Senior-Level</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Salary (PHP)</label>
                                <input type="number" required name="salary" class="form-control" placeholder="Enter amount" min="1" step="0.01">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Max Number of Hires</label>
                                <input type="number" required name="maxapplicants" class="form-control" value="1" min="1" step="1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Location</label>
                                <input type="text" required name="location" class="form-control" placeholder="e.g. Makati City">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Job Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Briefly describe the role..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2">Publish Opportunity</button>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <footer class="footer-custom mt-5 border-top bg-green text-white">
        <div class="container py-4">
            <div class="row align-items-center justify-content-between g-3">
                <div class="col-md-4 text-center text-md-start">
                    <span class="jobful-title text-white fs-5 fw-bold text-decoration-none">WorkJourney</span>
                    <p class="small-note text-white-50 mb-0 mt-1">&copy; 2026 WorkJourney. All rights reserved.</p>
                </div>
                <div class="col-md-5">
                    <ul class="list-unstyled d-flex flex-wrap justify-content-center justify-content-md-end gap-3 mb-0 small-note">
                        <li><a href="#" class="text-white-50 text-decoration-none footer-link">About</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none footer-link">Accessibility</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none footer-link">User Agreement</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none footer-link">Privacy Policy</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none footer-link">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side guard: catches blank / whitespace-only values before the request
        // even leaves the browser. The PHP-side checks above are the real safety net,
        // since this can be bypassed by anyone submitting the form directly.
        function validatePostJobForm(form) {
            const fields = ['jobtitle', 'description', 'salary', 'jobtype', 'worksetup', 'experiencelevel', 'location', 'maxapplicants'];
            for (const fieldName of fields) {
                const field = form.elements[fieldName];
                if (!field || field.value.trim() === '') {
                    alert('Please fill in all fields. Blank values are not allowed.');
                    if (field) field.focus();
                    return false;
                }
            }
            if (parseFloat(form.elements['salary'].value) <= 0) {
                alert('Salary must be greater than 0.');
                form.elements['salary'].focus();
                return false;
            }
            if (parseInt(form.elements['maxapplicants'].value, 10) < 1) {
                alert('Max Number of Hires must be at least 1.');
                form.elements['maxapplicants'].focus();
                return false;
            }
            return true;
        }
    </script>

    
</body>
</html>
