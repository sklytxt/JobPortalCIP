<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/JobClass.php';
require_once '../classes/ApplicationClass.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only jobseekers can apply
$user = UserClass::getUserById($_SESSION['user_id']);
if ($user['Usertype'] !== 'jobseeker') {
    header("Location: job-list.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: job-list.php");
    exit();
}

$jobId = (int) $_GET['id'];
$job   = JobClass::getJobById($jobId);

if (!$job) {
    echo "<script>alert('Job not found.'); window.location='job-list.php';</script>";
    exit();
}

$alreadyApplied = ApplicationClass::hasApplied($jobId, $_SESSION['user_id']);
$errorMsg   = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyApplied) {
    $portfolio = trim($_POST['portfolio'] ?? '');
    $result    = ApplicationClass::apply(
        $jobId,
        $_SESSION['user_id'],
        $_FILES['resume'],
        $portfolio
    );

    if ($result === true) {
        $successMsg = "Application submitted successfully!";
        $alreadyApplied = true; // disable the form now
    } else {
        $errorMsg = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Apply | WorkJourney</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@700&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="styles.css" rel="stylesheet" />
</head>

<body class="home-page-body">

  <nav class="navbar navbar-expand-lg bg-green border-bottom sticky-top py-0">
    <div class="container">
      <a class="navbar-brand jobful-title text-white m-0 pe-2 text-decoration-none" href="home.php">WorkJourney</a>
      <div class="navbar-nav ms-auto align-items-center py-2">
        <a href="job-list.php" class="btn btn-sm btn-outline-light">
          <i class="fa fa-arrow-left me-1"></i> Back to Listings
        </a>
      </div>
    </div>
  </nav>

  <main class="page-shell">
    <div class="container py-4" style="max-width: 760px;">

      <!-- Job Summary Card -->
      <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h2 class="h5 fw-bold mb-1"><?= htmlspecialchars($job['JobTitle']) ?></h2>
            <p class="text-muted small mb-2">
              <?= htmlspecialchars($job['CompanyName'] ?: $job['EmployerName']) ?>
              &nbsp;·&nbsp; <?= htmlspecialchars($job['Location']) ?>
            </p>
            <div class="d-flex flex-wrap gap-2">
              <span class="badge bg-success-subtle text-success-emphasis"><?= htmlspecialchars($job['JobType']) ?></span>
              <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= htmlspecialchars($job['WorkSetup']) ?></span>
              <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= htmlspecialchars($job['ExperienceLevel']) ?></span>
            </div>
          </div>
          <div class="text-end">
            <span class="fw-bold text-dark fs-6">₱<?= number_format((float)$job['Salary'], 0) ?></span>
          </div>
        </div>
      </div>

      <!-- Application Form Card -->
      <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-bold mb-4">Submit Your Application</h5>

        <?php if (!empty($successMsg)): ?>
          <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="fa fa-check-circle"></i>
            <div><?= htmlspecialchars($successMsg) ?></div>
          </div>
          <a href="job-list.php" class="btn btn-success mt-2">Browse More Jobs</a>
        <?php elseif ($alreadyApplied): ?>
          <div class="alert alert-info d-flex align-items-center gap-2">
            <i class="fa fa-info-circle"></i>
            <div>You have already submitted an application for this job.</div>
          </div>
          <a href="job-list.php" class="btn btn-outline-secondary mt-2">Back to Listings</a>
        <?php else: ?>

          <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
              <i class="fa fa-exclamation-triangle"></i>
              <div><?= htmlspecialchars($errorMsg) ?></div>
            </div>
          <?php endif; ?>

          <form action="apply.php?id=<?= $jobId ?>" method="POST" enctype="multipart/form-data">

            <div class="mb-4">
              <label class="form-label fw-bold">Resume <span class="text-danger">*</span></label>
              <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
              <div class="form-text">PDF or Word document, max 5MB.</div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold">Portfolio / LinkedIn URL <span class="text-muted fw-normal">(optional)</span></label>
              <input type="url" name="portfolio" class="form-control"
                     placeholder="https://github.com/yourprofile or https://linkedin.com/in/you"
                     value="<?= htmlspecialchars($_POST['portfolio'] ?? '') ?>">
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
              <a href="job-list.php" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-success px-5 fw-bold"
                      onclick="return confirm('Submit your application for this job?');">
                <i class="fa fa-paper-plane me-2"></i> Apply Now
              </button>
            </div>

          </form>
        <?php endif; ?>
      </div>

    </div>
  </main>

  <footer class="footer-custom mt-5 border-top bg-green text-white">
    <div class="container py-4">
      <div class="row align-items-center justify-content-between g-3">
        <div class="col-md-4 text-center text-md-start">
          <span class="jobful-title text-white fs-5 fw-bold">WorkJourney</span>
          <p class="small-note text-white-50 mb-0 mt-1">&copy; 2026 WorkJourney. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>