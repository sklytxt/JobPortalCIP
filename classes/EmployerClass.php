<?php
class EmployerClass {
    private static function getConnection() {
        return new mysqli("localhost", "root", "", "jobdb");
    }

    public static function postJob($employerId, $jobTitle, $description, $salary, $jobType, $workSetup, $experienceLevel, $location, $maxApplicants) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("INSERT INTO jobs (EmployerID, JobTitle, Description, Salary, JobType, WorkSetup, ExperienceLevel, Location, Status, MaxApplicants) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Open', ?)");
        $stmt->bind_param("isssssssi", $employerId, $jobTitle, $description, $salary, $jobType, $workSetup, $experienceLevel, $location, $maxApplicants);
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    }

    // 1. UPDATED: Added $limit and $offset parameters to handle page constraints
    public static function getJobsByEmployer($employerId, $search = '', $experience = '', $jobType = '', $workSetup = '', $minSalary = '', $location = '', $limit = 10, $offset = 0) {
        $conn = self::getConnection();
        $sql = "SELECT * FROM jobs WHERE EmployerID = ?";
        $types = "i";
        $params = [$employerId];

        if ($search !== '') {
            $sql .= " AND JobTitle LIKE ?";
            $types .= "s";
            $params[] = "%{$search}%";
        }
        if ($experience !== '') {
            $sql .= " AND ExperienceLevel = ?";
            $types .= "s";
            $params[] = $experience;
        }
        if ($jobType !== '') {
            $sql .= " AND JobType = ?";
            $types .= "s";
            $params[] = $jobType;
        }
        if ($workSetup !== '') {
            $sql .= " AND WorkSetup = ?";
            $types .= "s";
            $params[] = $workSetup;
        }
        if ($minSalary !== '') {
            $sql .= " AND Salary >= ?";
            $types .= "i";
            $params[] = (int)$minSalary;
        }
        if ($location !== '') {
            $sql .= " AND Location LIKE ?";
            $types .= "s";
            $params[] = "%{$location}%";
        }

        $sql .= " ORDER BY PostedDate DESC";
        
        // Append Pagination Constraint
        $sql .= " LIMIT ? OFFSET ?";
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    // 2. NEW METHOD: Counts total matches so the view knows how many page numbers to show
    public static function getTotalJobsCount($employerId, $search = '', $experience = '', $jobType = '', $workSetup = '', $minSalary = '', $location = '') {
        $conn = self::getConnection();
        $sql = "SELECT COUNT(*) as total FROM jobs WHERE EmployerID = ?";
        $types = "i";
        $params = [$employerId];

        if ($search !== '') { $sql .= " AND JobTitle LIKE ?"; $types .= "s"; $params[] = "%{$search}%"; }
        if ($experience !== '') { $sql .= " AND ExperienceLevel = ?"; $types .= "s"; $params[] = $experience; }
        if ($jobType !== '') { $sql .= " AND JobType = ?"; $types .= "s"; $params[] = $jobType; }
        if ($workSetup !== '') { $sql .= " AND WorkSetup = ?"; $types .= "s"; $params[] = $workSetup; }
        if ($minSalary !== '') { $sql .= " AND Salary >= ?"; $types .= "i"; $params[] = (int)$minSalary; }
        if ($location !== '') { $sql .= " AND Location LIKE ?"; $types .= "s"; $params[] = "%{$location}%"; }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $row['total'] ?? 0;
    }

    // 3. UPDATED: Added $limit and $offset parameters for incoming candidates
    public static function getApplicantsByEmployer($employerId, $search = '', $statusFilter = '', $limit = 10, $offset = 0) {
        $conn = self::getConnection();
        
        $sql = "SELECT a.*, u.FullName, j.JobTitle FROM applications a 
                JOIN users u ON a.ApplicantID = u.UserID 
                JOIN jobs j ON a.JobID = j.JobID 
                WHERE a.EmployerID = ? AND a.Status != 'Rejected'";
        $types = "i";
        $params = [$employerId];

        if ($search !== '') {
            $sql .= " AND (u.FullName LIKE ? OR j.JobTitle LIKE ?)";
            $types .= "ss";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($statusFilter !== '') {
            $sql .= " AND a.Status = ?";
            $types .= "s";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY a.AppliedDate DESC";
        
        // Append Pagination Constraint
        $sql .= " LIMIT ? OFFSET ?";
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    // 4. NEW METHOD: Counts total matching applicants for dynamic page counts
    public static function getTotalApplicantsCount($employerId, $search = '', $statusFilter = '') {
        $conn = self::getConnection();
        $sql = "SELECT COUNT(*) as total FROM applications a 
                JOIN users u ON a.ApplicantID = u.UserID 
                JOIN jobs j ON a.JobID = j.JobID 
                WHERE a.EmployerID = ? AND a.Status != 'Rejected'";
        $types = "i";
        $params = [$employerId];

        if ($search !== '') {
            $sql .= " AND (u.FullName LIKE ? OR j.JobTitle LIKE ?)";
            $types .= "ss";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($statusFilter !== '') {
            $sql .= " AND a.Status = ?";
            $types .= "s";
            $params[] = $statusFilter;
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $row['total'] ?? 0;
    }

    public static function getHiredApplicants($jobId) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT a.ApplicationID, u.FullName, u.ProfileImagePath, u.Email FROM applications a JOIN users u ON a.ApplicantID = u.UserID WHERE a.JobID = ? AND a.Status = 'Accepted'");
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function updateApplicationStatus($applicationId, $status) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("UPDATE applications SET Status = ?, StatusDate = NOW() WHERE ApplicationID = ?");
        $stmt->bind_param("si", $status, $applicationId);
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function acceptApplicant($applicationId, $jobId) {
    $conn = self::getConnection();
    $conn->begin_transaction();
    try {
        $check = $conn->prepare("SELECT j.MaxApplicants, (SELECT COUNT(*) FROM applications WHERE JobID = ? AND Status = 'Accepted') as CurrentCount FROM jobs j WHERE j.JobID = ?");
        $check->bind_param("ii", $jobId, $jobId);
        $check->execute();
        $data = $check->get_result()->fetch_assoc();
        $check->close();

        if ($data['CurrentCount'] >= $data['MaxApplicants']) {
            throw new Exception("Maximum capacity reached for this job.");
        }

        // UPDATED: Added StatusDate = NOW() here
        $stmt1 = $conn->prepare("UPDATE applications SET Status = 'Accepted', StatusDate = NOW() WHERE ApplicationID = ?");
        $stmt1->bind_param("i", $applicationId);
        $stmt1->execute();
        $stmt1->close();
        
        if ($data['CurrentCount'] + 1 >= $data['MaxApplicants']) {
            $stmt2 = $conn->prepare("UPDATE jobs SET Status = 'Filled' WHERE JobID = ?");
            $stmt2->bind_param("i", $jobId);
            $stmt2->execute();
            $stmt2->close();
        }
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    } finally {
        $conn->close();
    }
}

    public static function terminateContract($jobId, $applicationId) {
        $conn = self::getConnection();
        $conn->begin_transaction();
        try {
            $stmt2 = $conn->prepare("UPDATE applications SET Status = 'Rejected' WHERE ApplicationID = ?");
            $stmt2->bind_param("i", $applicationId);
            $stmt2->execute();
            $stmt2->close();

            $check = $conn->prepare("SELECT j.MaxApplicants, (SELECT COUNT(*) FROM applications WHERE JobID = ? AND Status = 'Accepted') as CurrentCount FROM jobs j WHERE j.JobID = ?");
            $check->bind_param("ii", $jobId, $jobId);
            $check->execute();
            $data = $check->get_result()->fetch_assoc();
            $check->close();

            if ($data['CurrentCount'] < $data['MaxApplicants']) {
                $stmt1 = $conn->prepare("UPDATE jobs SET Status = 'Open' WHERE JobID = ?");
                $stmt1->bind_param("i", $jobId);
                $stmt1->execute();
                $stmt1->close();
            }
            
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        } finally {
            $conn->close();
        }
    }

    public static function getJobDetails($jobId) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT j.*, u.FullName as EmployerName, u.Email, u.ProfileImagePath FROM jobs j JOIN users u ON j.EmployerID = u.UserID WHERE j.JobID = ?");
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function deleteJob($jobId, $employerId) {
        $conn = self::getConnection();
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("DELETE FROM applications WHERE JobID = ?");
            $stmt1->bind_param("i", $jobId);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $conn->prepare("DELETE FROM jobs WHERE JobID = ? AND EmployerID = ?");
            $stmt2->bind_param("ii", $jobId, $employerId);
            $stmt2->execute();
            $stmt2->close();

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        } finally {
            $conn->close();
        }
    }

    public static function updateJob($jobId, $employerId, $data) {
    $conn = self::getConnection();
    // Security: Only update if the JobID matches the EmployerID
    $stmt = $conn->prepare("UPDATE jobs SET 
        JobTitle = ?, 
        Description = ?, 
        Salary = ?, 
        JobType = ?, 
        WorkSetup = ?, 
        ExperienceLevel = ?, 
        Location = ? 
        WHERE JobID = ? AND EmployerID = ?");
    
    $stmt->bind_param("ssissssii", 
        $data['title'], 
        $data['description'], 
        $data['salary'], 
        $data['jobType'], 
        $data['workSetup'], 
        $data['experienceLevel'], 
        $data['location'], 
        $jobId,
        $employerId
    );
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}
}
?>