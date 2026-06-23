<?php
class AdminDashboard {
    private $db;

    // Automatically connect when the object is instantiated
    public function __construct() {
        $this->db = new mysqli("localhost", "root", "", "jobdb");
        
        if ($this->db->connect_error) {
            die("Database linkage offline: " . $this->db->connect_error);
        }
    }

    // --- READ OPERATION ---
    public function getTableData($tableName) {
        $cleanTable = $this->db->real_escape_string($tableName);
        return $this->db->query("SELECT * FROM `$cleanTable` ORDER BY 1 DESC");
    }

    // --- SECURE DYNAMIC UPDATE MANAGER ---
    public function updateRecord($table, $id, $postData) {
        if ($table === 'users') {
            if (!empty($postData['Password'])) {
                $passHash = password_hash($postData['Password'], PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET FullName=?, Email=?, Password=?, Usertype=?, Location=?, JobTitle=?, CompanyName=?, ContactNumber=? WHERE UserID=?");
                $stmt->bind_param("ssssssssi", $postData['FullName'], $postData['Email'], $passHash, $postData['Usertype'], $postData['Location'], $postData['JobTitle'], $postData['CompanyName'], $postData['ContactNumber'], $id);
            } else {
                $stmt = $this->db->prepare("UPDATE users SET FullName=?, Email=?, Usertype=?, Location=?, JobTitle=?, CompanyName=?, ContactNumber=? WHERE UserID=?");
                $stmt->bind_param("sssssssi", $postData['FullName'], $postData['Email'], $postData['Usertype'], $postData['Location'], $postData['JobTitle'], $postData['CompanyName'], $postData['ContactNumber'], $id);
            }
            return $stmt->execute();
        } 
        
        if ($table === 'jobs') {
            $stmt = $this->db->prepare("UPDATE jobs SET EmployerID=?, JobTitle=?, Description=?, JobType=?, WorkSetup=?, ExperienceLevel=?, Location=?, Salary=?, Status=? WHERE JobID=?");
            $stmt->bind_param("issssssssi", $postData['EmployerID'], $postData['JobTitle'], $postData['Description'], $postData['JobType'], $postData['WorkSetup'], $postData['ExperienceLevel'], $postData['Location'], $postData['Salary'], $postData['Status'], $id);
            return $stmt->execute();
        }

        if ($table === 'applications') {
            $stmt = $this->db->prepare("UPDATE applications SET JobID=?, ApplicantID=?, ResumePath=?, PortfolioPath=?, Status=?, EmployerID=? WHERE ApplicationID=?");
            $stmt->bind_param("iisssii", $postData['JobID'], $postData['ApplicantID'], $postData['ResumePath'], $postData['PortfolioPath'], $postData['Status'], $postData['EmployerID'], $id);
            return $stmt->execute();
        }

        return false;
    }

    // --- SECURE DYNAMIC DELETE MANAGER ---
    public function deleteRecord($table, $id) {
        $cleanTable = $this->db->real_escape_string($table);
        
        // Key mapping rules based on your schema indexes
        $primaryKeyMap = [
            'users' => 'UserID',
            'jobs' => 'JobID',
            'applications' => 'ApplicationID'
        ];

        if (!array_key_exists($cleanTable, $primaryKeyMap)) return false;
        $pk = $primaryKeyMap[$cleanTable];

        $stmt = $this->db->prepare("DELETE FROM `$cleanTable` WHERE `$pk` = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>