<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/AuthClass.php';
require_once '../classes/ApplicationClass.php';

// Authentication Check
if (isset($_GET['logout'])) {
    AuthClass::logout();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Data Fetching
$user = UserClass::getUserById($_SESSION['user_id']);
$role = $user['Usertype'] ?? 'jobseeker';
// Calculate Initials for Avatar
$initials = !empty($user['FullName']) ? strtoupper(substr(trim($user['FullName']), 0, 2)) : 'AR';

// Pagination Logic
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$totalAccepted = ApplicationClass::countAcceptedJobs($_SESSION['user_id']);
$totalPages = ceil($totalAccepted / $limit);
$myAcceptedJobs = ApplicationClass::getAcceptedJobsPaginated($_SESSION['user_id'], $limit, $offset);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkJourney | My Current Jobs</title>
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
          <li class="nav-item"><a class="nav-link d-flex flex-column align-items-center text-center text-white-50" href="home.php"><i class="fa fa-home fs-5 mb-1"></i><span class="nav-label">Home</span></a></li>
          <li class="nav-item"><a class="nav-link d-flex flex-column align-items-center text-center text-white-50" href="job-list.php"><i class="fa fa-list-ul fs-5 mb-1"></i><span class="nav-label">Job List</span></a></li>
          <li class="nav-item"><a class="nav-link d-flex flex-column align-items-center text-center text-white" href="jobs.php"><i class="fa fa-briefcase fs-5 mb-1"></i><span class="nav-label">Application</span></a></li>
          <?php if (isset($user['Usertype']) && strtolower($user['Usertype']) === 'employer'): ?>
            <li class="nav-item"><a class="nav-link d-flex flex-column align-items-center text-center" href="employer.php"><i class="fa fa-building fs-5 mb-1"></i><span class="nav-label">Employer</span></a></li>
          <?php endif; ?>
          <?php if (isset($user['Usertype']) && strtolower($user['Usertype']) !== 'employer'): ?>
    <li class="nav-item">
        <a class="nav-link d-flex flex-column align-items-center text-center active" href="my-jobs.php">
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
        <span class="text-secondary fw-bold"><?= strtoupper(substr($user['FullName'] ?? 'U', 0, 1)) ?></span>
    <?php endif; ?>
</div>
              <span class="nav-label text-white-50">Me</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
              <li><a class="dropdown-item py-2 small-note" href="profile.php"><i class="fa fa-user-o me-2"></i> My Profile</a></li>
              <li><a class="dropdown-item py-2 small-note" href="dashboard.php"><i class="fa fa-th-large me-2"></i> Dashboard</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item py-2 small-note text-danger" href="logout.php"><i class="fa fa-sign-out me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <main class="page-shell flex-grow-1 py-5">
    <div class="container" style="max-width: 900px;">
        <h2 class="fw-bold mb-4">My Current Jobs</h2>
        <?php if ($totalAccepted === 0): ?>
            <div class="card border-0 shadow-sm p-5 text-center">
                <i class="fa fa-briefcase fa-3x text-muted mb-3"></i>
                <h5 class="fw-bold">No accepted jobs yet.</h5>
                <p class="text-muted">Once you are hired, your jobs will appear here.</p>
                <a href="job-list.php" class="btn btn-success mt-2">Find Jobs</a>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php while ($job = $myAcceptedJobs->fetch_assoc()): 
                    $imagePath = "../uploads/profile_img/" . $job['ProfileImagePath'];
                    $hasImg = !empty($job['ProfileImagePath']) && file_exists($imagePath);
                ?>
                <div class="card border-0 shadow-sm p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-4 bg-light border rounded d-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
                            <?php if ($hasImg): ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" class="rounded" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <i class="fa fa-building fa-2x text-secondary"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold mb-1"><?= htmlspecialchars($job['JobTitle']) ?></h4>
                            <p class="text-muted mb-1"><i class="fa fa-building-o me-1"></i> <?= htmlspecialchars($job['CompanyName'] ?? 'Company') ?> • <?= htmlspecialchars($job['Location']) ?></p>
                            <span class="badge bg-success-subtle text-success">Accepted</span>
                        </div>
                        <a href="job-details.php?id=<?= $job['JobID'] ?>" class="btn btn-outline-primary btn-sm px-4">View</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4"><ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                </ul></nav>
            <?php endif; ?>
        <?php endif; ?>
        
    </div>
  </main>

  <footer class="footer-custom mt-auto border-top bg-green text-white">
    <div class="container py-4">
      <div class="row align-items-center justify-content-between g-3">
        <div class="col-md-4 text-center text-md-start">
          <span class="jobful-title text-white fs-5 fw-bold text-decoration-none">WorkJourney</span>
          <p class="small-note text-white-50 mb-0 mt-1">&copy; 2026 WorkJourney. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>