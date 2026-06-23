<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/AuthClass.php';

if (isset($_GET['logout'])) {
    AuthClass::logout();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user = UserClass::getUserById($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;
$totalApplications = AuthClass::countUserApplications($current_user_id);

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
  <link href="styles.css" rel="stylesheet"/>
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
          <li class="nav-item">
            <a class="nav-link d-flex flex-column align-items-center text-center" href="jobs.php">
              <i class="fa fa-briefcase fs-5 mb-1"></i>
              <span class="nav-label">Jobs</span>
            </a>
          </li>

            <?php if ($user['Usertype'] === 'employer'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="employer.php">
                        <i class="fa fa-building fs-5 mb-1"></i><span class="nav-label">Employer</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="navbar-nav ms-auto align-items-center border-start-lg ps-lg-4 py-2 py-lg-0">
          <div class="dropdown">
  <a class="d-flex flex-column align-items-center text-center text-decoration-none dropdown-toggle nav-link-profile" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    
    <div class="avatar-sm mb-1 d-flex align-items-center justify-content-center overflow-hidden" style="width: 32px; height: 32px; border-radius: 50%; background: #eee;">
      <?php if (!empty($user['ProfileImagePath']) && file_exists("../uploads/".$user['ProfileImagePath'])): ?>
        <img src="../uploads/<?= htmlspecialchars($user['ProfileImagePath']) ?>" style="width:100%; height:100%; object-fit:cover;">
      <?php else: ?>
        <span class="text-secondary fw-bold">Me</span>
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

<main class="page-shell">
  <section class="container py-4">

    <div class="row g-4">

      <aside class="col-lg-3">

        <div class="card border-0 shadow-sm p-4 mb-4">
  
  <div class="text-center">

    <?php if (!empty($user['ProfileImagePath']) && file_exists("../uploads/".$user['ProfileImagePath'])): ?>
      <img src="../uploads/<?= htmlspecialchars($user['ProfileImagePath']) ?>"
           alt="profile"
           style="width:100px;height:100px;object-fit:cover;border-radius:50%;display:block;margin:0 auto 12px auto;border:1px solid #ddd;">
    <?php else: ?>
      <div style="width:100px;height:100px;border-radius:50%;
                  background:#f1f1f1;border:1px solid #ddd;
                  display:flex;align-items:center;justify-content:center;
                  margin:0 auto 12px auto;font-weight:600;color:#888;">
        User
      </div>
    <?php endif; ?>

    <h5 class="mb-1"><?= htmlspecialchars($user['FullName']) ?></h5>

      <p class="small-note text-secondary mb-3 text-truncate">
    <?= htmlspecialchars($user['DisplaySubtext']) ?>
  </p>
  </div>

  <div class="d-grid mt-4">
    <a href="profile.php" class="btn btn-outline-custom">
      Edit Profile
    </a>
  </div>

</div>

        <div class="card border-0 shadow-sm p-4">
          <h6 class="fw-bold mb-3">Quick Links</h6>

          <div class="d-grid gap-2">

            <a href="job-list.php" class="btn btn-outline-custom btn-sm">
              My Applications
            </a>
          </div>
        </div>

      </aside>

      <!-- Center Column -->
      <section class="col-lg-6">

        <div class="card border-0 shadow-sm p-4 mb-4">
          <h3 class="fw-bold mb-2">
            Welcome Back, <?= htmlspecialchars($user['FullName']) ?>
          </h3>

          <p class="text-secondary mb-0">
            Stay on top of your applications and discover new career opportunities.
          </p>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-12">
              <div class="card border-0 shadow-sm p-3 text-center">
                  <h3 class="fw-bold text-success mb-1"><?php echo $totalApplications; ?></h3>
                  <p class="small-note mb-0">Applications</p>
              </div>
          </div>


        </div>


      </section>

      <!-- Right Column -->
      <aside class="col-lg-3">

        <div class="card border-0 shadow-sm p-4 mb-4">
          <h6 class="fw-bold mb-3">Recent Activity</h6>

          <div class="small-note">

            <div class="mb-3">
              <strong>Application Submitted</strong>
              <p class="text-secondary mb-0">
                Frontend Developer
              </p>
            </div>

            <div class="mb-3">
              <strong>Interview Scheduled</strong>
              <p class="text-secondary mb-0">
                UI/UX Designer
              </p>
            </div>

            <div>
              <strong>Resume Updated</strong>
              <p class="text-secondary mb-0">
                Today
              </p>
            </div>

          </div>
        </div>  

      </aside>

    </div>

  </section>
</main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Solid Green Minimalist Footer -->
  <footer class="footer-custom mt-5 border-top bg-green text-white">
    <div class="container py-4">
      <div class="row align-items-center justify-content-between g-3">
        <!-- Brand Segment -->
        <div class="col-md-4 text-center text-md-start">
          <span class="jobful-title text-white fs-5 fw-bold text-decoration-none">WorkJourney</span>
          <p class="small-note text-white-50 mb-0 mt-1">&copy; 2026 WorkJourney. All rights reserved.</p>
        </div>
        
        <!-- Quick Informational Links -->
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
</body>
</html>