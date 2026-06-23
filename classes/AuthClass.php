<?php

class AuthClass
{
    private static $databaseName = "jobdb";
    private static $conn = null;

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

    public static function login($username, $password, $remember = false)
    {
        $conn = self::getDb();
        $stmt = $conn->prepare("SELECT UserID, Password, Usertype FROM users WHERE Email=? OR FullName=?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['Password'])) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $row['UserID'];
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_type'] = $row['Usertype']; 
                if ($remember) {
                    setcookie("remember_user", $row['UserID'], time() + (86400 * 30), "/");
                }
                return $row; 
            }
        }
        return false;
    }

    public static function register()
    {
        $conn = self::getDb();
        $name = $_POST['name'] ?? null;
        $email = $_POST['email'] ?? null;
        $passwordRaw = $_POST['password'] ?? null;
        $confirm = $_POST['confirm_password'] ?? null;
        $location = $_POST['location'] ?? null;
        $contact = $_POST['contact_number'] ?? null;
        $usertype = $_POST['usertype'] ?? null;
        $jobTitle = ($usertype === 'jobseeker') ? ($_POST['job_title'] ?? null) : null;
        $companyName = ($usertype === 'employer') ? ($_POST['company_name'] ?? null) : null;
        if ($passwordRaw !== $confirm) {
            return "Password does not match";
        }
        $check = $conn->prepare("SELECT UserID FROM users WHERE Email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            return "Email already exists";
        }
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);
        $uploadDir = "../uploads/";
        $fileName = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . "_" . basename($_FILES['profile_image']['name']);
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fileName);
        }
        $stmt = $conn->prepare("INSERT INTO users (FullName, Email, Password, Usertype, Location, JobTitle, CompanyName, ContactNumber, ProfileImagePath) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $name, $email, $password, $usertype, $location, $jobTitle, $companyName, $contact, $fileName);
        if ($stmt->execute()) {
            return true;
        }
        return "Database error: " . $stmt->error;
    }

    public static function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        session_destroy();
        header("Location: login.php");
        exit();
    }

    public static function countUserApplications($applicantId)
    {
        $conn = self::getDb();
        $stmt = $conn->prepare("SELECT COUNT(*) as total_apps FROM applications WHERE ApplicantID = ?");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['total_apps'];
        }
        return 0;
    }
}
?>