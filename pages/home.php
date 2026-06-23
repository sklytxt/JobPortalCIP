<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/AuthClass.php';
require_once '../classes/HomeClass.php'; 

if (isset($_GET['logout'])) {
    AuthClass::logout();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user = UserClass::getUserById($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;
$role = $user['Usertype'] ?? 'jobseeker';

if ($role === 'employer') {
    $stats = HomeClass::getEmployerStats($current_user_id);
    $totalApplications = $stats['pending_apps'] ?? 0; 
} else {
    $totalApplications = AuthClass::countUserApplications($current_user_id);
}

$initials = !empty($user['FullName']) ? strtoupper(substr(trim($user['FullName']), 0, 2)) : 'AR';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkJourney | Home</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@700&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="styles.css" rel="stylesheet" />
</head>

<body class="home-page-body d-flex flex-column min-vh-100">
  
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
            <a class="nav-link d-flex flex-column align-items-center text-center active" href="home.php">
              <i class="fa fa-home fs-5 mb-1"></i>
              <span class="nav-label">Home</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex flex-column align-items-center text-center" href="job-list.php">
              <i class="fa fa-list-ul fs-5 mb-1"></i>
              <span class="nav-label">Job List</span>
            </a>
          </li>
          <?php if (isset($user['Usertype']) && strtolower($user['Usertype']) !== 'employer'): ?>
    <li class="nav-item">
        <a class="nav-link d-flex flex-column align-items-center text-center" href="jobs.php">
            <i class="fa fa-briefcase fs-5 mb-1"></i>
            <span class="nav-label">Application</span>
        </a>
    </li>
<?php endif; ?>
          <?php if ($role === 'employer'): ?>
              <li class="nav-item">
                  <a class="nav-link d-flex flex-column align-items-center text-center" href="employer.php">
                      <i class="fa fa-building fs-5 mb-1"></i><span class="nav-label">Employer</span>
                  </a>
              </li>
          <?php endif; ?>
          

<?php if (isset($user['Usertype']) && strtolower($user['Usertype']) !== 'employer'): ?>
    <li class="nav-item">
        <a class="nav-link d-flex flex-column align-items-center text-center" href="my-jobs.php">
            <i class="fa fa-briefcase fs-5 mb-1"></i>
            <span class="nav-label">My Jobs</span>
        </a>
    </li>
<?php endif; ?>



        </ul>
        <div class="navbar-nav ms-auto align-items-center border-start-lg ps-lg-4 py-2 py-lg-0">
          <div class="dropdown">
            <a class="d-flex flex-column align-items-center text-center text-decoration-none dropdown-toggle nav-link-profile" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar-sm mb-1 d-flex align-items-center justify-content-center overflow-hidden" style="width: 32px; height: 32px; border-radius: 50%; background: #eee;">
                <?php if (!empty($user['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $user['ProfileImagePath'])): ?>
                  <img src="../uploads/profile_img/<?= htmlspecialchars($user['ProfileImagePath']) ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                  <span class="text-secondary fw-bold small"><?= $initials ?></span>
                <?php endif; ?>
              </div>
              <span class="nav-label text-white-50">Me</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
              <li><a class="dropdown-item py-2 small-note" href="profile.php"><i class="fa fa-user-o me-2"></i> My Profile</a></li>
              <li><a class="dropdown-item py-2 small-note" href="dashboard.php"><i class="fa fa-th-large me-2"></i> Dashboard</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item py-2 small-note text-danger" href="home.php?logout=1"><i class="fa fa-sign-out me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <main class="page-shell flex-grow-1">
    <section class="container py-4">
      <div class="row g-4">
        <aside class="col-lg-3">
          <div class="card border-0 shadow-sm p-4 text-center">
            <?php if (!empty($user['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $user['ProfileImagePath'])): ?>
              <img src="../uploads/profile_img/<?= htmlspecialchars($user['ProfileImagePath']) ?>"
                   alt="profile"
                   style="width:100px;height:100px;object-fit:cover;border-radius:50%;display:block;margin:0 auto 12px auto;border:1px solid #ddd;">
            <?php else: ?>
              <div style="width:100px;height:100px;border-radius:50%;
                          background:#f1f1f1;border:1px solid #ddd;
                          display:flex;align-items:center;justify-content:center;
                          margin:0 auto 12px auto;font-weight:600;color:#888;font-size:1.5rem;">
                <?= $initials ?>
              </div>
            <?php endif; ?>
            <h5 class="mb-1 fw-bold"><?= htmlspecialchars($user['FullName']) ?></h5>
            <p class="small-note text-secondary mb-2 text-truncate">
              <?= htmlspecialchars($user['DisplaySubtext'] ?? '') ?>
            </p>
            <div class="user-details-block mt-3 pt-3 border-top text-start">
                <div class="small-note text-muted-custom mb-1 text-truncate">
                    <i class="fa fa-id-badge me-2 text-green"></i>USER ID: <strong><?= htmlspecialchars($user['UserID'] ?? 'N/A') ?></strong>
                </div>
                <div class="small-note text-muted-custom mb-1 text-truncate">
                    <i class="fa fa-envelope me-2 text-green"></i><?= htmlspecialchars($user['Email'] ?? 'No email provided') ?>
                </div>
                <div class="small-note text-muted-custom text-truncate">
                    <i class="fa fa-phone me-2 text-green"></i><?= htmlspecialchars($user['ContactNumber'] ?? 'No contact number') ?>
                </div>
                <div class="small-note text-muted-custom text-truncate">
                    <i class="fa fa-id-card-o me-2 text-green"></i><?= htmlspecialchars($user['Usertype'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="d-grid mt-4">
              <a href="profile.php" class="btn btn-outline-custom">Edit Profile</a>
            </div>
          </div>
        </aside>

        <section class="col-lg-9">
          <div class="card border-0 shadow-sm p-4 mb-4">
            <h3 class="fw-bold mb-2">Welcome Back, <?= htmlspecialchars($user['FullName']) ?></h3>
            <p class="text-secondary mb-0 small-note">
              <?php if ($role === 'employer'): ?>
                You are an employer. Manage your active openings, search jobseekers, and fill open slots effortlessly.
              <?php else: ?>
                Stay on top of your applications and discover new career opportunities.
              <?php endif; ?>
            </p>
          </div>

          <div class="row g-3 mb-4">
            <?php if ($role === 'employer'): ?>
              <div class="col-md-6">
                  <div class="card border-0 shadow-sm p-3 text-center">
                      <h3 class="fw-bold text-success mb-1"><?= $stats['total_jobs'] ?? 0 ?></h3>
                      <p class="small-note text-muted mb-0">Active Openings</p>
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="card border-0 shadow-sm p-3 text-center">
                      <h3 class="fw-bold text-warning mb-1"><?= $stats['pending_apps'] ?? 0 ?></h3>
                      <p class="small-note text-muted mb-0">Unreviewed Applicants</p>
                  </div>
              </div>
            <?php else: ?>
              <div class="col-md-12">
                  <div class="card border-0 shadow-sm p-3 text-center">
                      <h3 class="fw-bold text-success mb-1"><?= $totalApplications; ?></h3>
                      <p class="small-note text-muted mb-0">Submitted Applications</p>
                  </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="card border-0 shadow-sm p-4">
            <?php if ($role === 'employer'): ?>
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Your Active Job Offerings</h5>
                <a href="employer.php" class="small text-decoration-none">Manage All</a>
              </div>
              <?php 
              $myJobs = HomeClass::getPublicJobs(); 
              $count = 0;
              while ($job = $myJobs->fetch_assoc()): 
                  if ($job['EmployerID'] !== $current_user_id) continue;
                  $count++;
              ?>
                <div class="border-bottom pb-3 mb-3">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h6 class="mb-1 fw-bold"><?= htmlspecialchars($job['JobTitle']) ?></h6>
                      <p class="small-note text-secondary mb-1">
                        <?= htmlspecialchars($job['Location']) ?> • Max Capacity: <?= $job['MaxApplicants'] ?>
                      </p>
                    </div>
                    <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($job['Status']) ?></span>
                  </div>
                </div>
              <?php 
              endwhile; 
              if ($count === 0): ?>
                 <p class="text-center text-muted small-note py-3">You don't have any job entries listed yet.</p>
              <?php endif; ?>

            <?php else: ?>
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Explore Available Positions</h5>
                <a href="jobs.php" class="small text-decoration-none">View All Listings</a>
              </div>
              <?php 
              $jobs = HomeClass::getPublicJobs();
              if ($jobs && $jobs->num_rows > 0):
                  while ($job = $jobs->fetch_assoc()): 
                      // Note: Added null-coalescing fallback in case ProfileImagePath isn't selected in the SQL Join
                      $imageFilename = $job['ProfileImagePath'] ?? '';
                      $empImg = !empty($imageFilename) && file_exists("../uploads/profile_img/" . $imageFilename) ? "../uploads/profile_img/" . $imageFilename : null;
              ?>
                <div class="border-bottom pb-3 mb-3 d-flex align-items-center gap-3">
                  <div class="overflow-hidden bg-light border flex-shrink-0" style="width:48px; height:48px; border-radius:8px;">
                    <?php if ($empImg): ?>
                      <img src="<?= $empImg ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                      <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted fw-bold small">CO</div>
                    <?php endif; ?>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold"><a href="job-details.php?id=<?= $job['JobID'] ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($job['JobTitle']) ?></a></h6>
                    <p class="small-note text-secondary mb-1">
                      <?= htmlspecialchars($job['CompanyName'] ?? 'Independent Employer') ?> • <?= htmlspecialchars($job['Location']) ?>
                    </p>
                    <span class="badge bg-light text-dark border-1 border"><?= htmlspecialchars($job['WorkSetup']) ?></span>
                    <span class="badge bg-light text-dark border-1 border"><?= htmlspecialchars($job['JobType']) ?></span>
                  </div>
                  <div>
                     <span class="text-success small fw-bold">₱<?= number_format((float)($job['Salary'] ?? 0)) ?></span>
                  </div>
                </div>
              <?php 
                  endwhile;
              else: ?>
                <p class="text-center text-muted small-note py-3">No active jobs found matching public directories.</p>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </section>
  </main>

  <footer class="footer-custom mt-auto border-top bg-green text-white">
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
</body>
</html>