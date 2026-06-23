<?php
class HomeClass {
    private static function getConnection() {
        return new mysqli("localhost", "root", "", "jobdb");
    }

    public static function getPublicJobs($search = '') {
        $conn = self::getConnection();
        $sql = "SELECT j.*, u.CompanyName, u.ProfileImagePath, u.JobTitle AS EmployerTitle FROM jobs j 
                JOIN users u ON j.EmployerID = u.UserID 
                WHERE j.Status = 'Open'";
        
        $params = [];
        $types = "";

        if ($search !== '') {
            $sql .= " AND (j.JobTitle LIKE ? OR u.CompanyName LIKE ? OR j.Location LIKE ?)";
            $types .= "sss";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $sql .= " ORDER BY j.PostedDate DESC";
        $stmt = $conn->prepare($sql);
        
        if ($search !== '') {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function getJobseekerDashboard($applicantId) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT a.ApplicationID, a.AppliedDate, a.Status, j.JobTitle, u.CompanyName, u.ProfileImagePath
            FROM applications a
            JOIN jobs j ON a.JobID = j.JobID
            JOIN users u ON j.EmployerID = u.UserID
            WHERE a.ApplicantID = ?
            ORDER BY a.AppliedDate DESC
        ");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function getEmployerStats($employerId) {
        $conn = self::getConnection();
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total_jobs FROM jobs WHERE EmployerID = ?");
        $stmt->bind_param("i", $employerId);
        $stmt->execute();
        $totalJobs = $stmt->get_result()->fetch_assoc()['total_jobs'];
        $stmt->close();

        $stmt = $conn->prepare("SELECT COUNT(*) as pending_apps FROM applications WHERE EmployerID = ? AND Status = 'Pending'");
        $stmt->bind_param("i", $employerId);
        $stmt->execute();
        $pendingApps = $stmt->get_result()->fetch_assoc()['pending_apps'];
        $stmt->close();

        $conn->close();
        return [
            'total_jobs' => $totalJobs,
            'pending_apps' => $pendingApps
        ];
    }
}
?>