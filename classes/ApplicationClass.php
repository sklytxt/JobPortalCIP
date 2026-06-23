<?php

class ApplicationClass
{
    private static function getConnection(): mysqli
    {
        $conn = new mysqli("localhost", "root", "", "jobdb");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    public static function apply(int $jobId, int $applicantId, array $resumeFile, array $coverLetterFile, string $portfolio = ''): true|string 
    {
        $conn = self::getConnection();

        $roleStmt = $conn->prepare("SELECT Usertype FROM users WHERE UserID = ? LIMIT 1");
        $roleStmt->bind_param("i", $applicantId);
        $roleStmt->execute();
        $user = $roleStmt->get_result()->fetch_assoc();
        $roleStmt->close();

        if ($user && strtolower($user['Usertype']) === 'employer') {
            $conn->close();
            return "Employers are not authorized to apply for job listings.";
        }

        if (self::hasApplied($jobId, $applicantId)) {
            $conn->close();
            return "You have already applied to this job.";
        }

        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        // --- Resume validation ---
        if (empty($resumeFile['name']) || $resumeFile['error'] !== UPLOAD_ERR_OK) {
            $conn->close();
            return "A resume file is required.";
        }
        if (!in_array($resumeFile['type'], $allowedTypes)) {
            $conn->close();
            return "Resume must be a PDF or Word document (.pdf, .doc, .docx).";
        }
        if ($resumeFile['size'] > 5 * 1024 * 1024) {
            $conn->close();
            return "Resume file must be under 5MB.";
        }

        // --- Cover letter validation ---
        if (empty($coverLetterFile['name']) || $coverLetterFile['error'] !== UPLOAD_ERR_OK) {
            $conn->close();
            return "A cover letter file is required.";
        }
        if (!in_array($coverLetterFile['type'], $allowedTypes)) {
            $conn->close();
            return "Cover letter must be a PDF or Word document (.pdf, .doc, .docx).";
        }
        if ($coverLetterFile['size'] > 5 * 1024 * 1024) {
            $conn->close();
            return "Cover letter file must be under 5MB.";
        }

        $jobStmt = $conn->prepare("SELECT Status, EmployerID FROM jobs WHERE JobID = ? LIMIT 1");
        $jobStmt->bind_param("i", $jobId);
        $jobStmt->execute();
        $job = $jobStmt->get_result()->fetch_assoc();
        $jobStmt->close();

        if (!$job) {
            $conn->close();
            return "Job not found.";
        }
        if ($job['Status'] !== 'Open') {
            $conn->close();
            return "This job is no longer accepting applications.";
        }

        $resumeFileName = time() . '_' . $applicantId . '_' . basename($resumeFile['name']);
        if (!move_uploaded_file($resumeFile['tmp_name'], '../uploads/resumes/' . $resumeFileName)) {
            $conn->close();
            return "Failed to upload resume. Please try again.";
        }

        $coverLetterFileName = time() . '_' . $applicantId . '_' . basename($coverLetterFile['name']);
        if (!move_uploaded_file($coverLetterFile['tmp_name'], '../uploads/coverletter/' . $coverLetterFileName)) {
            // Roll back the resume that was already saved, since the application as a whole failed
            @unlink('../uploads/resumes/' . $resumeFileName);
            $conn->close();
            return "Failed to upload cover letter. Please try again.";
        }

        $portfolioVal = !empty($portfolio) ? $portfolio : null;
        $stmt = $conn->prepare("
            INSERT INTO applications (JobID, ApplicantID, EmployerID, ResumePath, PortfolioPath, Status, coverletter)
            VALUES (?, ?, ?, ?, ?, 'Pending', ?)
        ");
        $stmt->bind_param("iiisss", $jobId, $applicantId, $job['EmployerID'], $resumeFileName, $portfolioVal, $coverLetterFileName);
        $success = $stmt->execute();
        
        $stmt->close();
        $conn->close();

        return $success ? true : "Database error. Please try again.";
    }

    public static function getApplicationsByUser(int $applicantId): array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT a.*, j.JobTitle, j.JobType, j.WorkSetup, j.Location, u.FullName AS EmployerName, u.CompanyName
            FROM applications a
            JOIN jobs j ON a.JobID = j.JobID
            JOIN users u ON j.EmployerID = u.UserID
            WHERE a.ApplicantID = ?
            ORDER BY a.AppliedDate DESC
        ");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        
        $apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        return $apps;
    }

    public static function hasApplied(int $jobId, int $applicantId): bool
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT ApplicationID FROM applications WHERE JobID = ? AND ApplicantID = ? AND Status != 'Rejected' LIMIT 1");
        $stmt->bind_param("ii", $jobId, $applicantId);
        $stmt->execute();
        
        $exists = $stmt->get_result()->fetch_assoc() !== null;
        
        $stmt->close();
        $conn->close();
        return $exists;
    }

    public static function withdrawApplication(int $appId, int $applicantId): bool
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("DELETE FROM applications WHERE ApplicationID = ? AND ApplicantID = ?");
        $stmt->bind_param("ii", $appId, $applicantId);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }

    public static function getLatestApplicationStatus(int $jobId, int $applicantId): ?string 
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT Status FROM applications WHERE JobID = ? AND ApplicantID = ? ORDER BY ApplicationID DESC LIMIT 1");
        $stmt->bind_param("ii", $jobId, $applicantId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $status = $result ? $result['Status'] : null;
        $stmt->close();
        $conn->close();
        return $status;
    }

    public static function getActiveApplicationCount(int $jobId): int 
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE JobID = ? AND Status != 'Rejected'");
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        $count = (int)($result[0] ?? 0);
        $stmt->close();
        $conn->close();
        return $count;
    }

    public static function getAcceptedJobsPaginated(int $userId, int $limit, int $offset) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT a.*, j.JobTitle, j.Salary, j.Location, u.CompanyName, u.ProfileImagePath FROM applications a JOIN jobs j ON a.JobID = j.JobID JOIN users u ON j.EmployerID = u.UserID WHERE a.ApplicantID = ? AND a.Status = 'Accepted' ORDER BY a.AppliedDate DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $conn->close();
        return $result;
    }

    public static function countAcceptedJobs(int $userId): int {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE ApplicantID = ? AND Status = 'Accepted'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_row()[0];
        $conn->close();
        return (int)$count;
    }



}