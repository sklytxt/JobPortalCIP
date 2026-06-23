<?php
class JobClass {
    public $jobID, $employerID, $jobTitle, $description, $jobType, $workSetup, $experienceLevel, $location, $postedDate, $salary, $status, $maxApplicants;
    public function __construct($jobID = "", $employerID = "", $jobTitle = "", $description = "", $jobType = "", $workSetup = "", $experienceLevel = "", $location = "", $postedDate = "", $salary = "", $status = "Open", $maxApplicants = 1) {
        $this->jobID = $jobID;
        $this->employerID = $employerID;
        $this->jobTitle = $jobTitle;
        $this->description = $description;
        $this->jobType = $jobType;
        $this->workSetup = $workSetup;
        $this->experienceLevel = $experienceLevel;
        $this->location = $location;
        $this->postedDate = $postedDate;
        $this->salary = $salary;
        $this->status = $status;
        $this->maxApplicants = $maxApplicants;
    }
    private static function connect() {
        $conn = new mysqli("localhost", "root", "", "jobdb");
        if ($conn->connect_error) {
            die("Database connection failed");
        }
        return $conn;
    }
    public static function getJobs($filters = []) {
        $conn = self::connect();
        $sql = "SELECT j.JobID, j.JobTitle, j.JobType, j.WorkSetup, j.ExperienceLevel, j.Location, j.PostedDate, j.Salary, j.Status, j.MaxApplicants, u.FullName, u.CompanyName, (SELECT COUNT(*) FROM applications a WHERE a.JobID = j.JobID) AS ApplicantCount, (SELECT COUNT(*) FROM applications a WHERE a.JobID = j.JobID AND a.Status = 'Accepted') AS AcceptedCount FROM jobs j LEFT JOIN users u ON j.EmployerID = u.UserID WHERE 1=1";
        $params = [];
        $types = "";
        if (!empty($filters['search'])) {
            $sql .= " AND (j.JobTitle LIKE ? OR j.Description LIKE ?)";
            $keyword = "%" . $filters['search'] . "%";
            $params[] = $keyword;
            $params[] = $keyword;
            $types .= "ss";
        }
        if (!empty($filters['type'])) {
            $sql .= " AND j.JobType = ?";
            $params[] = self::mapJobType($filters['type']);
            $types .= "s";
        }
        if (!empty($filters['setup'])) {
            $sql .= " AND j.WorkSetup = ?";
            $params[] = self::mapWorkSetup($filters['setup']);
            $types .= "s";
        }
        if (!empty($filters['exp'])) {
            $sql .= " AND j.ExperienceLevel = ?";
            $params[] = self::mapExperience($filters['exp']);
            $types .= "s";
        }
        $sql .= " ORDER BY j.PostedDate DESC";
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['AcceptedCount'] >= $row['MaxApplicants']) {
                $row['Status'] = 'Filled';
            }
            $jobs[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $jobs;
    }
    public static function getJobById($jobId) {
        $conn = self::connect();
        $stmt = $conn->prepare("SELECT j.*, u.FullName, u.CompanyName, u.ProfileImagePath, (SELECT COUNT(*) FROM applications a WHERE a.JobID = j.JobID) AS ApplicantCount, (SELECT COUNT(*) FROM applications a WHERE a.JobID = j.JobID AND a.Status = 'Accepted') AS AcceptedCount FROM jobs j LEFT JOIN users u ON j.EmployerID = u.UserID WHERE j.JobID = ? LIMIT 1");
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $job = $stmt->get_result()->fetch_assoc();
        if ($job && $job['AcceptedCount'] >= $job['MaxApplicants']) {
            $job['Status'] = 'Filled';
        }
        $stmt->close();
        $conn->close();
        return $job;
    }
    public static function submitApplication($jobId, $applicantId, $files, $portfolioLink = "") {
        $job = self::getJobById($jobId);
        if (!$job) {
            return ["success" => false, "message" => "Job not found."];
        }
        $employerId = $job['EmployerID'];
        if ($job['Status'] !== 'Open') {
            return ["success" => false, "message" => "This job is no longer accepting applications."];
        }
        if ($job['ApplicantCount'] >= $job['MaxApplicants']) {
            return ["success" => false, "message" => "This job has reached its maximum number of applicants."];
        }
        if (self::hasAlreadyApplied($jobId, $applicantId)) {
            return ["success" => false, "message" => "You have already applied to this job."];
        }
        if (empty($files['resume']['name'])) {
            return ["success" => false, "message" => "A resume file is required."];
        }
        $uploadDir = "../uploads/resumes/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $resumeName = time() . "_" . basename($files['resume']['name']);
        $resumePath = $uploadDir . $resumeName;
        if (!move_uploaded_file($files['resume']['tmp_name'], $resumePath)) {
            return ["success" => false, "message" => "Failed to upload resume. Please try again."];
        }
        $portfolioPath = null;
        if (!empty($files['portfolio']['name'])) {
            $portfolioName = time() . "_" . basename($files['portfolio']['name']);
            $portfolioPath = $uploadDir . $portfolioName;
            move_uploaded_file($files['portfolio']['tmp_name'], $portfolioPath);
        } elseif (!empty($portfolioLink)) {
            $portfolioPath = $portfolioLink;
        }
        $conn = self::connect();
        $stmt = $conn->prepare("INSERT INTO applications (JobID, ApplicantID, ResumePath, PortfolioPath, EmployerID, Status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("iissi", $jobId, $applicantId, $resumePath, $portfolioPath, $employerId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        if ($success) {
            return ["success" => true, "message" => "Application submitted successfully."];
        }
        return ["success" => false, "message" => "Something went wrong submitting your application."];
    }
    public static function hasAlreadyApplied($jobId, $applicantId) {
        $conn = self::connect();
        $stmt = $conn->prepare("SELECT ApplicationID FROM applications WHERE JobID = ? AND ApplicantID = ? LIMIT 1");
        $stmt->bind_param("ii", $jobId, $applicantId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();
        $conn->close();
        return $exists;
    }
    public static function mapJobType($slug) {
        $map = [
            'full-time' => 'Full-Time',
            'part-time' => 'Part-Time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'freelance' => 'Freelance',
        ];
        return $map[$slug] ?? $slug;
    }
    public static function mapWorkSetup($slug) {
        $map = [
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'onsite' => 'On-Site',
        ];
        return $map[$slug] ?? $slug;
    }
    public static function mapExperience($slug) {
        $map = [
            'entry' => 'Entry Level',
            'mid' => 'Mid-Senior',
            'director' => 'Executive',
        ];
        return $map[$slug] ?? $slug;
    }
    public static function getAllOpenJobs(): array {
        $conn = self::connect();
        $stmt = $conn->prepare("SELECT j.*, u.FullName, u.CompanyName FROM jobs j LEFT JOIN users u ON j.EmployerID = u.UserID WHERE j.Status = 'Open' ORDER BY j.PostedDate DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $jobs;
    }
}