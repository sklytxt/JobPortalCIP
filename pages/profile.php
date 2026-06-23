<?php
session_start();
require_once '../classes/UserClass.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user = UserClass::getUserById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    UserClass::updateProfile(
        $_SESSION['user_id'],
        $_POST,
        $_FILES
    );

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Profile | WorkJourney</title>

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
      
      <!-- Left Side: Brand Logo Only -->
      <div class="d-flex align-items-center gap-2 my-2 my-lg-0 flex-grow-1 flex-lg-grow-0">
        <a class="navbar-brand jobful-title text-white m-0 pe-2 text-decoration-none" href="home.php">WorkJourney</a>
      </div>
      
      <!-- Mobile Toggler -->
      <button class="navbar-toggler border-0 navbar-dark ms-auto my-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <!-- Center Links & Right Control Menu -->
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
              <span class="nav-label">My Jobs</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex flex-column align-items-center text-center" href="jobs.php">
              <i class="fa fa-briefcase fs-5 mb-1"></i>
              <span class="nav-label">Jobs</span>
            </a>
          </li>

          <li class="nav-item">
              <a class="nav-link d-flex flex-column align-items-center text-center" href="employer.php">
                  <i class="fa fa-building fs-5 mb-1"></i>
                  <span class="nav-label">Employer</span>
              </a>
          </li>
        </ul>
        
        <!-- Right Side User Menu Profile Segment -->
        <div class="navbar-nav ms-auto align-items-center border-start-lg ps-lg-4 py-2 py-lg-0">
          <div class="dropdown">
  <a class="d-flex flex-column align-items-center text-center text-decoration-none dropdown-toggle nav-link-profile" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    
    <div class="avatar-sm mb-1 d-flex align-items-center justify-content-center overflow-hidden" style="width: 32px; height: 32px; border-radius: 50%; background: #eee;">
      <?php if (!empty($user['ProfileImagePath']) && file_exists("../uploads/".$user['ProfileImagePath'])): ?>
        <img src="../uploads/<?= htmlspecialchars($user['ProfileImagePath']) ?>" style="width:100%; height:100%; object-fit:cover;">
      <?php else: ?>
        <span class="text-secondary fw-bold">AR</span>
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

    <div class="row g-4 justify-content-center">

      <div class="col-lg-8">

        <div class="card border-0 shadow-sm p-4">

          <h3 class="fw-bold mb-4">Edit Profile</h3>

          <!-- FORM -->
          <form method="POST" enctype="multipart/form-data">

            <div class="text-center mb-4">
              <?php if (!empty($user['ProfileImagePath'])): ?>
                <img src="../uploads/<?= htmlspecialchars($user['ProfileImagePath']) ?>"
                     class="rounded-circle border"
                     style="width:120px;height:120px;object-fit:cover;">
              <?php else: ?>
                <div class="rounded-circle bg-light border mx-auto"
                     style="width:120px;height:120px;"></div>
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <label>Full Name</label>
              <input type="text" name="fullname" class="form-control"
                     value="<?= htmlspecialchars($user['FullName']) ?>">
            </div>

            <div class="mb-3">
              <label>Email</label>
              <input readonly type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($user['Email']) ?>">
            </div>

            <?php if ($user['Usertype'] === 'employer'): ?>
    <div class="mb-3">
      <label>Company Name</label>
      <input type="text" name="companyname" class="form-control" value="<?= htmlspecialchars($user['CompanyName'] ?? '') ?>">
    </div>
<?php else: ?>
    <div class="mb-3">
      <label>Job Title</label>
      <input type="text" name="jobtitle" class="form-control" value="<?= htmlspecialchars($user['JobTitle'] ?? '') ?>">
    </div>
<?php endif; ?>

            <div class="mb-3">
              <label>Contact</label>
              <input type="text" name="contact" class="form-control"
                     value="<?= htmlspecialchars($user['ContactNumber']) ?>">
            </div>

            <div class="mb-3">
              <label>Location</label>
              <input type="text" name="location" class="form-control"
                     value="<?= htmlspecialchars($user['Location']) ?>">
            </div>

            <div class="mb-3">
              <label>Profile Image</label>
              <input type="file" name="profile_image" class="form-control">
            </div>

            <div class="d-flex justify-content-between mt-4">
  <a href="home.php" class="btn btn-outline-secondary">Cancel</a>
  
  <button type="submit" class="btn btn-success" 
          onclick="return confirm('Are you sure you want to save these changes?');">
    Save Changes
  </button>
</div>

          </form>

        </div>

      </div>

    </div>

  </section>
</main>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>