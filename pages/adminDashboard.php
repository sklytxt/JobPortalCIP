<?php
session_start();
require_once '..\classes\adminClass.php';
require_once '..\classes\AuthClass.php'; 

$adminSystem = new AdminDashboard();
$message = "";

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    AuthClass::logout(); 
}

if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['target_table'] ?? '';
    $id    = intval($_POST['target_id'] ?? 0);

    if (isset($_POST['action_delete']) && $id > 0) {
        if ($adminSystem->deleteRecord($table, $id)) {
            $message = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'><i class='fa fa-check me-2'></i>Record safely removed from system context.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $message = "<div class='alert alert-danger alert-dismissible fade show shadow-sm' role='alert'><i class='fa fa-exclamation-triangle me-2'></i>Execution error handling database constraints.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    } 
    elseif (isset($_POST['action_edit']) && $id > 0) {
        if ($adminSystem->updateRecord($table, $id, $_POST)) {
            $message = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'><i class='fa fa-check me-2'></i>Changes successfully written to the database!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $message = "<div class='alert alert-danger alert-dismissible fade show shadow-sm' role='alert'><i class='fa fa-exclamation-triangle me-2'></i>Failed processing update parameters.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    }
}

$apps_query  = $adminSystem->getTableData('applications');
$jobs_query  = $adminSystem->getTableData('jobs');
$users_query = $adminSystem->getTableData('users');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkJourney | Master Admin Control Center</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    body { font-family: 'Roboto', sans-serif; }
    .bg-green { background-color: #198754 !important; }
    .nav-tabs .nav-link { color: #6c757d; font-weight: 500; border: none; padding: 0.75rem 1.2rem; }
    .nav-tabs .nav-link.active { color: #198754 !important; border-bottom: 3px solid #198754 !important; background: transparent; }
    .table-scroll { max-height: 520px; overflow-y: auto; }
  </style>
</head>
<body class="bg-light home-page-body">

  <nav class="navbar navbar-expand-lg bg-green shadow-sm mb-4">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
      <a class="navbar-brand text-white fw-bold m-0" href="#">WorkJourney Master System</a>
      
      <a href="admindashboard.php?action=logout" class="btn btn-sm btn-light text-danger fw-medium px-3 rounded-2 shadow-sm" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fa fa-sign-out me-1"></i> Log Out
      </a>
    </div>
  </nav>

  <div class="container-fluid px-4">
    
    <?php echo $message; ?>

    <div class="row mb-3">
      <div class="col-lg-8">
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-0 text-muted"><i class="fa fa-search"></i></span>
          <input type="text" id="dashboardSearch" class="form-control border-0 py-2 ps-1" placeholder="Search directories in real-time (names, descriptions, status, locations...)" onkeyup="filterActiveTabTable()">
        </div>
      </div>
    </div>

    <div class="row g-4">
      
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
          
          <ul class="nav nav-tabs bg-light px-3" id="adminTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" id="app-tab" data-bs-toggle="tab" data-bs-target="#tab-app" type="button" role="tab">Applications</button></li>
            <li class="nav-item"><button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#tab-job" type="button" role="tab">Jobs</button></li>
            <li class="nav-item"><button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#tab-user" type="button" role="tab">Users</button></li>
          </ul>

          <div class="tab-content p-3" id="adminTabContent">
            
            <div class="tab-pane fade show active" id="tab-app" role="tabpanel">
              <div class="table-scroll">
                <table class="table table-hover align-middle small text-nowrap filterable-table">
                  <thead class="table-light"><tr><th>ApplicationID</th><th>JobID</th><th>ApplicantID</th><th>Resume</th><th>Date Applied</th><th>Status</th><th>Action</th></tr></thead>
                  <tbody>
                    <?php if ($apps_query && $apps_query->num_rows > 0): while($app = $apps_query->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo $app['ApplicationID']; ?></td>
                      <td>Job #<?php echo $app['JobID']; ?></td>
                      <td>User #<?php echo $app['ApplicantID']; ?></td>
                      <td><span class="text-muted"><?php echo htmlspecialchars($app['ResumePath']); ?></span></td>
                      <td><?php echo $app['AppliedDate']; ?></td>
                      <td><span class="badge <?php echo $app['Status'] === 'Accepted' ? 'bg-success' : ($app['Status'] === 'Rejected' ? 'bg-danger' : 'bg-warning'); ?>"><?php echo $app['Status']; ?></span></td>
                      <td><button class="btn btn-sm btn-outline-secondary py-0" onclick='populateConsole("applications", <?php echo $app['ApplicationID']; ?>, <?php echo json_encode($app); ?>)'>Edit</button></td>
                    </tr>
                    <?php endwhile; else: ?>
                      <tr class="no-data-row"><td colspan="7" class="text-muted text-center py-3">No structural applications recorded.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-job" role="tabpanel">
              <div class="table-scroll">
                <table class="table table-hover align-middle small text-nowrap filterable-table">
                  <thead class="table-light"><tr><th>JobID</th><th>Title</th><th>Type</th><th>Setup</th><th>Location</th><th>Salary</th><th>Status</th><th>Action</th></tr></thead>
                  <tbody>
                    <?php if ($jobs_query && $jobs_query->num_rows > 0): while($job = $jobs_query->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo $job['JobID']; ?></td>
                      <td class="fw-bold text-dark"><?php echo htmlspecialchars($job['JobTitle']); ?></td>
                      <td><?php echo htmlspecialchars($job['JobType']); ?></td>
                      <td><?php echo htmlspecialchars($job['WorkSetup']); ?></td>
                      <td><?php echo htmlspecialchars($job['Location'] ?? 'N/A'); ?></td>
                      <td>$<?php echo htmlspecialchars($job['Salary'] ?? '0'); ?></td>
                      <td><span class="badge bg-info"><?php echo htmlspecialchars($job['Status']); ?></span></td>
                      <td><button class="btn btn-sm btn-outline-secondary py-0" onclick='populateConsole("jobs", <?php echo $job['JobID']; ?>, <?php echo json_encode($job); ?>)'>Edit</button></td>
                    </tr>
                    <?php endwhile; else: ?>
                      <tr class="no-data-row"><td colspan="8" class="text-muted text-center py-3">No structural jobs posted yet.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-user" role="tabpanel">
              <div class="table-scroll">
                <table class="table table-hover align-middle small text-nowrap filterable-table">
                  <thead class="table-light"><tr><th>UserID</th><th>Full Name</th><th>Email</th><th>Location</th><th>Role</th><th>Company</th><th>Contact</th><th>Action</th></tr></thead>
                  <tbody>
                    <?php if ($users_query && $users_query->num_rows > 0): while($user = $users_query->fetch_assoc()): ?>
                    <tr>
                      <td><strong><?php echo $user['UserID']; ?></strong></td>
                      <td class="fw-bold"><?php echo htmlspecialchars($user['FullName']); ?></td>
                      <td><?php echo htmlspecialchars($user['Email']); ?></td>
                      <td><?php echo htmlspecialchars($user['Location'] ?? 'N/A'); ?></td>
                      <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['Usertype']); ?></span></td>
                      <td><?php echo htmlspecialchars($user['CompanyName'] ?? 'N/A'); ?></td>
                      <td><?php echo htmlspecialchars($user['ContactNumber'] ?? 'N/A'); ?></td>
                      <td><button class="btn btn-sm btn-outline-secondary py-0" onclick='populateConsole("users", <?php echo $user['UserID']; ?>, <?php echo json_encode($user); ?>)'>Edit</button></td>
                    </tr>
                    <?php endwhile; else: ?>
                      <tr class="no-data-row"><td colspan="8" class="text-muted text-center py-3">No user records available.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
          <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fa fa-sliders me-2"></i>Modification Console</h5>
          
          <form method="POST" action="">
            <input type="hidden" name="target_table" id="console_table" value="">
            <input type="hidden" name="target_id" id="console_id" value="">

            <div class="row g-2 mb-3" id="dynamic_fields_container">
                <div class="text-muted small text-center py-4">Select a record from the directory view to begin adjustments.</div>
            </div>

            <div class="d-flex flex-row gap-2 pt-2" id="console_actions" style="display: none !important;">
                <button type="submit" name="action_edit" class="btn btn-sm btn-success w-50 fw-medium"><i class="fa fa-save me-1"></i> Commit</button>
                <button type="submit" name="action_delete" class="btn btn-sm btn-danger w-50 fw-medium" onclick="return confirm('Purge this record completely?');"><i class="fa fa-trash me-1"></i> Delete</button>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Live Client-Side Table Content Search Filter
    function filterActiveTabTable() {
        const query = document.getElementById('dashboardSearch').value.toLowerCase();
        
        // Target only the table inside the currently visible/active tab pane
        const activeTabPane = document.querySelector('.tab-content .tab-pane.active');
        if (!activeTabPane) return;
        
        const table = activeTabPane.querySelector('.filterable-table');
        if (!table) return;
        
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            // Ignore custom fallback messages
            if (rows[i].classList.contains('no-data-row')) continue;

            let matchFound = false;
            const cells = rows[i].getElementsByTagName('td');
            
            // Loop through all data cells inside the current row
            for (let j = 0; j < cells.length - 1; j++) { // exclude last action button cell
                if (cells[j].innerText.toLowerCase().includes(query)) {
                    matchFound = true;
                    break;
                }
            }
            
            // Toggle visibility seamlessly
            rows[i].style.display = matchFound ? "" : "none";
        }
    }

    // Reset search bar value when switching tabs to prevent confusion
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tabButton => {
        tabButton.addEventListener('shown.bs.tab', () => {
            document.getElementById('dashboardSearch').value = '';
            filterActiveTabTable();
        });
    });

    function populateConsole(tableName, rowId, data) {
        document.getElementById('console_table').value = tableName;
        document.getElementById('console_id').value = rowId;
        
        document.getElementById('console_actions').style.setProperty('display', 'flex', 'important');

        const container = document.getElementById('dynamic_fields_container');
        let htmlOutput = '';

        if (tableName === 'users') {
            htmlOutput = `
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">User ID (Primary Key)</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="${rowId}" readonly>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Full Name</label>
                    <input type="text" class="form-control form-control-sm" name="FullName" value="${data.FullName || ''}" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Email Address</label>
                    <input type="email" class="form-control form-control-sm" name="Email" value="${data.Email || ''}" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Password (Leave blank to keep unchanged)</label>
                    <input type="password" class="form-control form-control-sm" name="Password" placeholder="••••••••">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">User Type</label>
                    <select class="form-select form-select-sm" name="Usertype">
                        <option value="jobseeker" ${data.Usertype === 'jobseeker' ? 'selected' : ''}>Job Seeker</option>
                        <option value="employer" ${data.Usertype === 'employer' ? 'selected' : ''}>Employer</option>
                        <option value="admin" ${data.Usertype === 'admin' ? 'selected' : ''}>Admin</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Location</label>
                    <input type="text" class="form-control form-control-sm" name="Location" value="${data.Location || ''}">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Job Title</label>
                    <input type="text" class="form-control form-control-sm" name="JobTitle" value="${data.JobTitle || ''}">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Company Name</label>
                    <input type="text" class="form-control form-control-sm" name="CompanyName" value="${data.CompanyName || ''}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Contact Number</label>
                    <input type="text" class="form-control form-control-sm" name="ContactNumber" value="${data.ContactNumber || ''}">
                </div>
                <div class="col-12 small mt-2">
                    <span class="text-muted fw-medium">Linked Image Asset:</span> <code class="text-success">${data.ProfileImagePath || 'None'}</code>
                </div>
            `;
        } 
        else if (tableName === 'jobs') {
            htmlOutput = `
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Job ID (Primary Key)</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="${rowId}" readonly>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Employer ID</label>
                    <input type="number" class="form-control form-control-sm" name="EmployerID" value="${data.EmployerID || ''}" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Job Title</label>
                    <input type="text" class="form-control form-control-sm" name="JobTitle" value="${data.JobTitle || ''}" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Description</label>
                    <textarea class="form-control form-control-sm" name="Description" rows="3" required>${data.Description || ''}</textarea>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Job Type</label>
                    <select class="form-select form-select-sm" name="JobType">
                        <option value="Full-Time" ${data.JobType === 'Full-Time' ? 'selected' : ''}>Full-Time</option>
                        <option value="Part-Time" ${data.JobType === 'Part-Time' ? 'selected' : ''}>Part-Time</option>
                        <option value="Contract" ${data.JobType === 'Contract' ? 'selected' : ''}>Contract</option>
                        <option value="Internship" ${data.JobType === 'Internship' ? 'selected' : ''}>Internship</option>
                        <option value="Freelance" ${data.JobType === 'Freelance' ? 'selected' : ''}>Freelance</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Work Setup</label>
                    <select class="form-select form-select-sm" name="WorkSetup">
                        <option value="On-Site" ${data.WorkSetup === 'On-Site' ? 'selected' : ''}>On-Site</option>
                        <option value="Remote" ${data.WorkSetup === 'Remote' ? 'selected' : ''}>Remote</option>
                        <option value="Hybrid" ${data.WorkSetup === 'Hybrid' ? 'selected' : ''}>Hybrid</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Experience Level</label>
                    <input type="text" class="form-control form-control-sm" name="ExperienceLevel" value="${data.ExperienceLevel || ''}">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Salary Metric ($)</label>
                    <input type="text" class="form-control form-control-sm" name="Salary" value="${data.Salary || ''}">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Location</label>
                    <input type="text" class="form-control form-control-sm" name="Location" value="${data.Location || ''}">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Status</label>
                    <input type="text" class="form-control form-control-sm" name="Status" value="${data.Status || 'Open'}">
                </div>
            `;
        } 
        else if (tableName === 'applications') {
            htmlOutput = `
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Application ID</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="${rowId}" readonly>
                </div>
                <div class="col-4">
                    <label class="form-label small fw-bold text-muted mb-0">Job ID</label>
                    <input type="number" class="form-control form-control-sm" name="JobID" value="${data.JobID || ''}" required>
                </div>
                <div class="col-4">
                    <label class="form-label small fw-bold text-muted mb-0">Applicant ID</label>
                    <input type="number" class="form-control form-control-sm" name="ApplicantID" value="${data.ApplicantID || ''}" required>
                </div>
                <div class="col-4">
                    <label class="form-label small fw-bold text-muted mb-0">Employer ID</label>
                    <input type="number" class="form-control form-control-sm" name="EmployerID" value="${data.EmployerID || ''}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Resume Path Link</label>
                    <input type="text" class="form-control form-control-sm" name="ResumePath" value="${data.ResumePath || ''}" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Portfolio Path (URL)</label>
                    <input type="text" class="form-control form-control-sm" name="PortfolioPath" value="${data.PortfolioPath || ''}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted mb-0">Application Status Decision</label>
                    <select class="form-select form-select-sm" name="Status">
                        <option value="Pending" ${data.Status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Accepted" ${data.Status === 'Accepted' ? 'selected' : ''}>Accepted</option>
                        <option value="Rejected" ${data.Status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                    </select>
                </div>
            `;
        }

        container.innerHTML = htmlOutput;
    }
  </script>
</body>
</html>