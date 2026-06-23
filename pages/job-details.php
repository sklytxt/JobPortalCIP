<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/AuthClass.php';
require_once '../classes/EmployerClass.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: employer.php");
    exit();
}

$user = UserClass::getUserById($_SESSION['user_id']); // Fetching user to verify role
$jobId = $_GET['id'];
$job = EmployerClass::getJobDetails($jobId);

if (!$job) {
    echo "<script>alert('Job not found.'); window.location='employer.php';</script>";
    exit();
}

// BACKEND SECURITY: Only allow termination if user is an employer AND owns this specific job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terminate_single'])) {
    if ($user['Usertype'] === 'employer' && (int)$job['EmployerID'] === (int)$_SESSION['user_id']) {
        $appId = $_POST['app_id_to_terminate'];
        if (EmployerClass::terminateContract($jobId, $appId)) {
            header("Location: job-details.php?id=" . $jobId);
            exit();
        }
    } else {
        die("Unauthorized access.");
    }
}

$hiredApplicants = EmployerClass::getHiredApplicants($jobId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkJourney | Job Details</title>
  <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@700&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="styles.css" rel="stylesheet" />
</head>

<body class="home-page-body">

  <nav class="navbar navbar-expand-lg bg-green border-bottom sticky-top py-0">
    <div class="container">
      <a class="navbar-brand jobful-title text-white m-0 pe-2 text-decoration-none" href="home.php">WorkJourney</a>
    </div>
  </nav>

  <main class="page-shell">
    <div class="container py-4" style="max-width: 1000px;">
      <div class="card p-4 mb-4 border-0 shadow-sm">
        <h2 class="h5 mb-0 fw-bold">Job Details</h2>
        <p class="text-secondary mb-0">Reviewing: <?= htmlspecialchars($job['JobTitle']) ?></p>
      </div>

   <div class="card p-4 border-0 shadow-sm">
    <a href="employer.php" class="btn btn-sm btn-outline-secondary mb-4" style="width: fit-content;">
        <i class="fa fa-arrow-left"></i> Back to Dashboard
    </a>
    
    <h3 class="fw-bold text-dark"><?= htmlspecialchars($job['JobTitle']) ?></h3>
    <p class="h4 text-primary fw-bold mt-2 mb-4">₱<?= number_format($job['Salary'], 2) ?></p>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light h-100">
                <small class="text-uppercase text-muted fw-bold d-block mb-1">Job Type</small>
                <span class="fw-bold text-dark"><?= htmlspecialchars($job['JobType']) ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light h-100">
                <small class="text-uppercase text-muted fw-bold d-block mb-1">Work Setup</small>
                <span class="fw-bold text-dark"><?= htmlspecialchars($job['WorkSetup']) ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light h-100">
                <small class="text-uppercase text-muted fw-bold d-block mb-1">Experience</small>
                <span class="fw-bold text-dark"><?= htmlspecialchars($job['ExperienceLevel']) ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light h-100">
                <small class="text-uppercase text-muted fw-bold d-block mb-1">Location</small>
                <span class="fw-bold text-dark"><?= htmlspecialchars($job['Location']) ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light h-100">
                <small class="text-uppercase text-muted fw-bold d-block mb-1">Date Posted</small>
                <span class="fw-bold text-dark"><?= date('M d, Y', strtotime($job['PostedDate'])) ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light h-100">
                <small class="text-uppercase text-muted fw-bold d-block mb-1">Status</small>
                <span class="badge <?= $job['Status'] === 'Filled' ? 'bg-danger' : 'bg-success' ?> fs-6">
                    <?= htmlspecialchars($job['Status']) ?>
                </span>
            </div>
        </div>
    </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-8">
                <h5 class="fw-bold mb-3">Job Description</h5>
                <p class="text-secondary"><?= nl2br(htmlspecialchars($job['Description'])) ?></p>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded">
                    <h6 class="fw-bold text-success mb-3">
                        <i class="fa fa-users"></i> Hired (<?= $hiredApplicants->num_rows ?> / <?= htmlspecialchars($job['MaxApplicants']) ?>)
                    </h6>

                    <?php if ($hiredApplicants->num_rows > 0): ?>
                        <?php while ($hired = $hiredApplicants->fetch_assoc()): ?>
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%; background: #ddd;">
                                        <?php if (!empty($hired['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $hired['ProfileImagePath'])): ?>
                                            <img src="../uploads/profile_img/<?= htmlspecialchars($hired['ProfileImagePath']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <span class="fw-bold text-secondary"><?= substr($hired['FullName'], 0, 1) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold small"><?= htmlspecialchars($hired['FullName']) ?></p>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;"><?= htmlspecialchars($hired['Email']) ?></small>
                                    </div>
                                </div>
                                
                                <?php if ($user['Usertype'] === 'employer' && (int)$job['EmployerID'] === (int)$_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Terminate this contract?');">
                                    <input type="hidden" name="app_id_to_terminate" value="<?= $hired['ApplicationID'] ?>">
                                    <button type="submit" name="terminate_single" class="btn btn-outline-danger btn-sm p-1">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted small">No positions filled yet.</p>
                    <?php endif; ?>

                    <hr>
                    <h6 class="fw-bold mb-3">Managed By</h6>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%; background: #eee;">
                            <?php if (!empty($job['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $job['ProfileImagePath'])): ?>
                                <img src="../uploads/profile_img/<?= htmlspecialchars($job['ProfileImagePath']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <span class="fw-bold text-secondary"><?= substr($job['EmployerName'], 0, 1) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="mb-0 fw-bold small"><?= htmlspecialchars($job['EmployerName']) ?></p>
                            <small class="text-muted d-block" style="font-size: 0.75rem;"><?= htmlspecialchars($job['Email']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div> 
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>