<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/ApplicationClass.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle "Withdraw" (Leave Job) request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_id'])) {
    $appId = (int)$_POST['withdraw_id'];
    ApplicationClass::withdrawApplication($appId, $_SESSION['user_id']);
    header("Location: jobs.php"); 
    exit();
}

$user = UserClass::getUserById($_SESSION['user_id']);
$allApplications = ApplicationClass::getApplicationsByUser($_SESSION['user_id']);

// Filter applications into categories
$pendingApps = array_filter($allApplications, function($app) {
    return isset($app['Status']) && strtolower($app['Status']) === 'pending';
});

$acceptedApps = array_filter($allApplications, function($app) {
    return isset($app['Status']) && strtolower($app['Status']) === 'accepted';
});

$rejectedApps = array_filter($allApplications, function($app) {
    return isset($app['Status']) && strtolower($app['Status']) === 'rejected';
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkJourney | My Jobs</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@700&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="styles.css" rel="stylesheet" />
</head>

<body>

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
          <li class="nav-item"><a class="nav-link d-flex flex-column align-items-center text-center active text-white" href="jobs.php"><i class="fa fa-briefcase fs-5 mb-1"></i><span class="nav-label">Jobs</span></a></li>
          <?php if (isset($user['Usertype']) && strtolower($user['Usertype']) === 'employer'): ?>
            <li class="nav-item"><a class="nav-link d-flex flex-column align-items-center text-center" href="employer.php"><i class="fa fa-building fs-5 mb-1"></i><span class="nav-label">Employer</span></a></li>
          <?php endif; ?>
        </ul>
        <div class="navbar-nav ms-auto align-items-center border-start-lg ps-lg-4 py-2 py-lg-0">
          <div class="dropdown">
            <a class="d-flex flex-column align-items-center text-center text-decoration-none dropdown-toggle nav-link-profile" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar-sm mb-1"><?= strtoupper(substr($user['FullName'] ?? 'U', 0, 1)) ?></div>
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

  <div class="container py-5">
    <h2 class="mb-4 fw-bold text-center">My Applications</h2>
    
    <div class="row">
        <div class="col-md-4 mb-5">
            <h4 class="mb-3 text-warning"><i class="fa fa-clock-o"></i> Pending</h4>
            <div class="table-responsive shadow-sm bg-white rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>Job Title</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pendingApps)): foreach ($pendingApps as $app): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $img = (!empty($app['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $app['ProfileImagePath'])) 
                                           ? "../uploads/profile_img/" . htmlspecialchars($app['ProfileImagePath']) 
                                           : null; 
                                    ?>
                                    <?php if ($img): ?>
                                        <img src="<?= $img ?>" style="width:30px; height:30px; border-radius:50%; object-fit:cover; margin-right:8px;">
                                    <?php else: ?>
                                        <div class="d-inline-block bg-secondary text-white text-center" style="width:30px; height:30px; border-radius:50%; line-height:30px; margin-right:8px; font-size:12px;"><?= strtoupper(substr($app['CompanyName'] ?? '?', 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($app['CompanyName'] ?? 'N/A') ?>
                                </td>
                                <td><?= htmlspecialchars($app['JobTitle']) ?></td>
                                <td class="text-center">
                                    <form method="POST" onsubmit="return confirm('Withdraw application?');">
                                        <input type="hidden" name="withdraw_id" value="<?= $app['ApplicationID'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center text-muted">No pending apps.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4 mb-5">
            <h4 class="mb-3 text-success"><i class="fa fa-check-circle"></i> Accepted</h4>
            <div class="table-responsive shadow-sm bg-white rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>Job Title</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($acceptedApps)): foreach ($acceptedApps as $app): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $img = (!empty($app['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $app['ProfileImagePath'])) ? "../uploads/profile_img/" . htmlspecialchars($app['ProfileImagePath']) : null; 
                                    ?>
                                    <?php if ($img): ?>
                                        <img src="<?= $img ?>" style="width:30px; height:30px; border-radius:50%; object-fit:cover; margin-right:8px;">
                                    <?php else: ?>
                                        <div class="d-inline-block bg-secondary text-white text-center" style="width:30px; height:30px; border-radius:50%; line-height:30px; margin-right:8px; font-size:12px;"><?= strtoupper(substr($app['CompanyName'] ?? '?', 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($app['CompanyName'] ?? 'N/A') ?>
                                </td>
                                <td><?= htmlspecialchars($app['JobTitle']) ?></td>
                                <td class="text-center"><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center text-muted">No accepted apps.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4 mb-5">
            <h4 class="mb-3 text-danger"><i class="fa fa-times-circle"></i> Rejected</h4>
            <div class="table-responsive shadow-sm bg-white rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>Job Title</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rejectedApps)): foreach ($rejectedApps as $app): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $img = (!empty($app['ProfileImagePath']) && file_exists("../uploads/profile_img/" . $app['ProfileImagePath'])) ? "../uploads/profile_img/" . htmlspecialchars($app['ProfileImagePath']) : null; 
                                    ?>
                                    <?php if ($img): ?>
                                        <img src="<?= $img ?>" style="width:30px; height:30px; border-radius:50%; object-fit:cover; margin-right:8px;">
                                    <?php else: ?>
                                        <div class="d-inline-block bg-secondary text-white text-center" style="width:30px; height:30px; border-radius:50%; line-height:30px; margin-right:8px; font-size:12px;"><?= strtoupper(substr($app['CompanyName'] ?? '?', 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($app['CompanyName'] ?? 'N/A') ?>
                                </td>
                                <td><?= htmlspecialchars($app['JobTitle']) ?></td>
                                <td class="text-center"><span class="badge bg-danger">Rejected</span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center text-muted">No rejected apps.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>

  <footer class="footer-custom mt-5 border-top bg-green text-white">
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