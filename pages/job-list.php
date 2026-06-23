<?php
session_start();
require_once '../classes/UserClass.php';
require_once '../classes/AuthClass.php';
require_once '../classes/JobClass.php';
require_once '../classes/ApplicationClass.php';

if (isset($_GET['logout'])) {
    AuthClass::logout();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user = UserClass::getUserById($_SESSION['user_id']);
$alertMessage = '';
$alertClass = '';
$current_user_id = $_SESSION['user_id'] ?? 0;
$role = $user['Usertype'] ?? 'jobseeker';
$initials = !empty($user['FullName']) ? strtoupper(substr(trim($user['FullName']), 0, 2)) : 'AR';


// --- Handle Modal Application Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $jobId = (int)$_POST['job_id'];
    $portfolio = trim($_POST['portfolio_path'] ?? '');
    $resumeFile = $_FILES['resume'] ?? null;
    $coverLetterFile = $_FILES['coverletter'] ?? null;

    if (empty($resumeFile['name'])) {
        $alertMessage = "Please upload a valid resume file.";
        $alertClass = "alert-danger";
    } elseif (empty($coverLetterFile['name'])) {
        $alertMessage = "Please upload a valid cover letter file.";
        $alertClass = "alert-danger";
    } else {
        $result = ApplicationClass::apply($jobId, $_SESSION['user_id'], $resumeFile, $coverLetterFile, $portfolio);
        if ($result === true) {
            $alertMessage = "Application submitted successfully!";
            $alertClass = "alert-success";
        } else {
            $alertMessage = $result;
            $alertClass = "alert-danger";
        }
    }
}

// --- Gather Inputs matching your exact option values ---
$jobSearch      = $_GET['job_search'] ?? '';
$expFilter      = $_GET['exp_filter'] ?? '';
$typeFilter     = $_GET['type_filter'] ?? '';
$setupFilter    = $_GET['setup_filter'] ?? '';

// Build the array payload for JobClass::getJobs()
$filters = [
    'search' => $jobSearch,
    'type'   => $typeFilter,
    'setup'  => $setupFilter,
    'exp'    => $expFilter
];

// Fetch all matching job listings
$allJobs = JobClass::getJobs($filters);

// --- Pagination Logic ---
$limit = 10; 
$totalItems = count($allJobs);
$totalPages = ceil($totalItems / $limit);

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
} elseif ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $limit;
$jobsList = array_slice($allJobs, $offset, $limit);

$queryParams = $_GET;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WorkJourney | Job List</title>
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
              <i class="fa fa-home fs-5 mb-1"></i>
              <span class="nav-label">Home</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex flex-column align-items-center text-center active" href="job-list.php">
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

    <main class="page-shell">
        <div class="container py-4" style="max-width: 1000px;">

            <?php if (!empty($alertMessage)): ?>
                <div class="alert <?= $alertClass ?> alert-dismissible fade show shadow-sm border-0" role="alert">
                    <?= htmlspecialchars($alertMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card p-4 mb-4 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="h4 mb-1 fw-bold">Explore Opportunities</h2>
                        <p class="text-secondary mb-0">Find jobs matching your skill sets and criteria.</p>
                    </div>
                    <i class="fa fa-briefcase fs-2 text-success opacity-50"></i>
                </div>
            </div>

            <div class="card p-4 mb-4 border-0 shadow-sm">
                <h5 class="fw-bold mb-3">Filter Search Parameters</h5>
                <form method="GET" action="job-list.php" class="row g-2 align-items-end">
                    <div class="col-md-4">
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
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-success w-100"><i class="fa fa-search"></i> Find</button>
                        <?php if ($jobSearch !== '' || $expFilter !== '' || $typeFilter !== '' || $setupFilter !== ''): ?>
                            <a href="job-list.php" class="btn btn-sm btn-outline-secondary"><i class="fa fa-refresh"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="d-flex flex-column gap-3 mb-4">
                <?php 
                if (!empty($jobsList)): 
                    foreach ($jobsList as $job): 
                        $isFilled = ($job['Status'] === 'Filled');
                        $badgeStatusClass = $isFilled ? 'bg-danger text-white' : 'bg-light text-dark border';
                        
                        // Check if current user already applied
                        $hasApplied = ApplicationClass::hasApplied($job['JobID'], $_SESSION['user_id']);
                        $isEmployer = (isset($user['Usertype']) && strtolower($user['Usertype']) === 'employer');
                ?>
                    <div class="card shadow-sm border-0 p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($job['JobTitle']) ?></h5>
                                <div class="mb-2">
                                    <span class="text-success small fw-bold me-2"><?= htmlspecialchars($job['CompanyName'] ?? 'WorkJourney Partner') ?></span>
                                    <span class="text-muted small"><?= htmlspecialchars($job['Location']) ?></span>
                                    <span class="mx-1 text-muted">•</span>
                                    <span class="badge bg-success-subtle text-success small"><?= htmlspecialchars($job['JobType']) ?></span>
                                    <span class="badge bg-primary-subtle text-primary small"><?= htmlspecialchars($job['WorkSetup']) ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary small"><?= htmlspecialchars($job['ExperienceLevel']) ?></span>
                                </div>
                                <div class="fw-bold text-dark mb-2 small">Salary Offering: ₱<?= htmlspecialchars(number_format((float)$job['Salary'])) ?></div>
                            </div>
                            <div class="text-end ms-3 flex-shrink-0">
                                <span class="badge <?= $badgeStatusClass ?> mb-3 d-block py-1 px-2"><?= htmlspecialchars($job['Status']) ?></span>
                                
                                <?php if ($isFilled): ?>
                                    <button class="btn btn-sm btn-secondary px-3 fw-bold shadow-sm" disabled>Filled</button>
                                <?php elseif ($isEmployer): ?>
                                    <button class="btn btn-sm btn-secondary px-3 fw-bold shadow-sm" disabled title="Employers cannot apply for jobs">Apply</button>
                                <?php elseif ($hasApplied): ?>
                                    <button class="btn btn-sm btn-outline-success px-3 fw-bold shadow-sm" disabled>Applied</button>
                                <?php else: ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-success px-3 fw-bold shadow-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#applyJobModal" 
                                            data-jobid="<?= $job['JobID'] ?>" 
                                            data-jobtitle="<?= htmlspecialchars($job['JobTitle']) ?>"
                                            data-company="<?= htmlspecialchars($job['CompanyName'] ?? 'WorkJourney Partner') ?>">
                                        Apply
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach; 
                else: 
                ?>
                    <div class="card p-5 border-0 shadow-sm text-center">
                        <i class="fa fa-folder-open-o fs-2 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No matching open job postings found.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="d-flex justify-content-center">
                    <ul class="pagination pagination-sm shadow-sm">
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                            <?php 
                                $queryParams['page'] = $currentPage - 1;
                                $prevLink = "job-list.php?" . http_build_query($queryParams);
                            ?>
                            <a class="page-link" href="<?= ($currentPage <= 1) ? '#' : $prevLink ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php 
                                $queryParams['page'] = $i;
                                $pageLink = "job-list.php?" . http_build_query($queryParams);
                            ?>
                            <li class="page-item <?= ($currentPage === $i) ? 'active' : '' ?>">
                                <a class="page-link <?= ($currentPage === $i) ? 'bg-success border-success text-white' : 'text-success' ?>" href="<?= $pageLink ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                            <?php 
                                $queryParams['page'] = $currentPage + 1;
                                $nextLink = "job-list.php?" . http_build_query($queryParams);
                            ?>
                            <a class="page-link" href="<?= ($currentPage >= $totalPages) ? '#' : $nextLink ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </main>

    <div class="modal fade" id="applyJobModal" isset-id="-1" tabindex="-1" aria-labelledby="applyJobModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="job-list.php" method="POST" enctype="multipart/form-data" onsubmit="return validateApplyForm(this);">
                    <div class="modal-header bg-green text-white py-3">
                        <h5 class="modal-title fw-bold" id="applyJobModalLabel">Job Application Form</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Applying for:</p>
                            <h4 class="fw-bold text-success mb-0" id="modalJobTitle">Job Position</h4>
                            <span class="text-secondary small" id="modalCompanyName">Company Name</span>
                        </div>
                        
                        <hr class="text-muted my-3">
                        
                        <input type="hidden" name="job_id" id="modalJobId" value="">

                        <div class="mb-3">
                            <label class="form-label small fw-bold mb-1">Upload Resume (.pdf, .doc, .docx) <span class="text-danger">*</span></label>
                            <input type="file" name="resume" class="form-control form-control-sm" accept=".pdf,.doc,.docx" required>
                            <div class="form-text extra-small-text">Maximum upload file capacity: 5MB</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold mb-1">Upload Cover Letter (.pdf, .doc, .docx) <span class="text-danger">*</span></label>
                            <input type="file" name="coverletter" class="form-control form-control-sm" accept=".pdf,.doc,.docx" required>
                            <div class="form-text extra-small-text">Maximum upload file capacity: 5MB</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold mb-1">Portfolio Link (Optional)</label>
                            <input type="url" name="portfolio_path" class="form-control form-control-sm" placeholder="https://yourportfolio.com">
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary border-0 fw-bold px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_application" class="btn btn-sm btn-success fw-bold px-4">Submit Application</button>
                    </div>
                </form>
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
        const applyJobModal = document.getElementById('applyJobModal');
        if (applyJobModal) {
            applyJobModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                
                const jobId = button.getAttribute('data-jobid');
                const jobTitle = button.getAttribute('data-jobtitle');
                const companyName = button.getAttribute('data-company');

                const modalJobIdInput = applyJobModal.querySelector('#modalJobId');
                const modalJobTitleSpan = applyJobModal.querySelector('#modalJobTitle');
                const modalCompanyNameSpan = applyJobModal.querySelector('#modalCompanyName');

                modalJobIdInput.value = jobId;
                modalJobTitleSpan.textContent = jobTitle;
                modalCompanyNameSpan.textContent = companyName;
            });

            // Reset the whole form (resume, cover letter, portfolio) each time the modal closes
            applyJobModal.addEventListener('hidden.bs.modal', () => {
                const form = applyJobModal.querySelector('form');
                if (form) form.reset();
            });
        }

        // Client-side guard: blocks submission if either required file is missing.
        // The PHP-side checks in ApplicationClass::apply() are the real safety net.
        function validateApplyForm(form) {
            const resumeInput = form.elements['resume'];
            if (!resumeInput || resumeInput.files.length === 0) {
                alert('Please upload a resume file before submitting.');
                if (resumeInput) resumeInput.focus();
                return false;
            }
            const coverLetterInput = form.elements['coverletter'];
            if (!coverLetterInput || coverLetterInput.files.length === 0) {
                alert('Please upload a cover letter file before submitting.');
                if (coverLetterInput) coverLetterInput.focus();
                return false;
            }
            return true;
        }
    </script>
</body>
</html>