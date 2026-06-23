<?php

class UserClass 
{
    public $fullName, $email, $password, $location, $jobTitle, $contactNumber, $imagePath;
    
    private static $databaseName = "jobdb";
    private static $conn = null;

    public function __construct($fullName = "", $email = "", $password = "", $location = "", $jobTitle = "", $contactNumber = "", $imagePath = "") 
    {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->password = $password;
        $this->location = $location;
        $this->jobTitle = $jobTitle;
        $this->contactNumber = $contactNumber;
        $this->imagePath = $imagePath;
    }

    private static function getDb() 
    {
        if (self::$conn === null) {
            self::$conn = new mysqli("localhost", "root", "", self::$databaseName);
            if (self::$conn->connect_error) {
                die("Connection failed: " . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }

    public static function getUserById($id) 
    {
        $conn = self::getDb();
        $stmt = $conn->prepare("SELECT UserID, FullName, Email, JobTitle, Location, ContactNumber, ProfileImagePath, Usertype, CompanyName FROM users WHERE UserID = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user) {
            $user['DisplaySubtext'] = ($user['Usertype'] === 'employer') 
                ? ($user['CompanyName'] ?? $user['JobTitle']) 
                : ($user['JobTitle'] ?? '');
        }
        
        $stmt->close();
        return $user;
    }

    public static function updateProfile($userId, $data, $file) 
    {
        $conn = self::getDb();

        $fullName    = trim($data['fullname'] ?? '');
        $email       = $data['email'] ?? '';
        $jobTitle    = $data['jobtitle'] ?? null;
        $companyName = $data['companyname'] ?? null;
        $contact     = trim($data['contact'] ?? '');
        $location    = $data['location'] ?? '';

        $namePattern = "/^[a-zA-Z\s\.\-]+$/";
        $contactPattern = "/^(09|\+639)\d{9}$/";

        if (empty($fullName) || empty($contact)) {
            return "Full Name and Contact fields cannot be empty.";
        }
        if (!preg_match($namePattern, $fullName)) {
            return "Full Name can only contain letters, dots, hyphens, and spaces.";
        }
        if (!preg_match($contactPattern, $contact)) {
            return "Invalid Contact format. Please use a valid PH mobile number (e.g., 09123456789).";
        }

        $fileName = null;
        if (!empty($file['profile_image']['name'])) {
            $fileName = time() . "_" . basename($file['profile_image']['name']);
            // The directory structure requires moving files into the profile_img subfolder
            move_uploaded_file($file['profile_image']['tmp_name'], "../uploads/profile_img/" . $fileName);
        }

        if ($fileName) {
            $stmt = $conn->prepare("UPDATE users SET FullName=?, Email=?, JobTitle=?, CompanyName=?, ContactNumber=?, Location=?, ProfileImagePath=? WHERE UserID=?");
            $stmt->bind_param("sssssssi", $fullName, $email, $jobTitle, $companyName, $contact, $location, $fileName, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET FullName=?, Email=?, JobTitle=?, CompanyName=?, ContactNumber=?, Location=? WHERE UserID=?");
            $stmt->bind_param("ssssssi", $fullName, $email, $jobTitle, $companyName, $contact, $location, $userId);
        }
        
        $success = $stmt->execute();
        $stmt->close();

        return $success ? true : "Database processing error. Please try again.";
    }
}