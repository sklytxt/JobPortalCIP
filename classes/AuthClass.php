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
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $usertype = $_POST['usertype'] ?? null;

    $jobTitle = ($usertype === 'jobseeker') ? trim($_POST['job_title'] ?? '') : null;
    $companyName = ($usertype === 'employer') ? trim($_POST['company_name'] ?? '') : null;

    if ($jobTitle === '') $jobTitle = null;
    if ($companyName === '') $companyName = null;

    if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
        return "Name can only contain letters and spaces.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email address.";
    }

    if (!preg_match("/^\d{11}$/", $contact)) {
        return "Contact number must be exactly 11 digits.";
    }

    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $passwordRaw)) {
        return "Password must be at least 8 characters and contain uppercase, lowercase, and a number.";
    }

    if ($passwordRaw !== $confirm) {
        return "Password does not match.";
    }

    $check = $conn->prepare("SELECT UserID FROM users WHERE Email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        return "Email already exists";
    }

    $check->close();

    $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

    $uploadDir = dirname(__DIR__) . "/uploads/profile_img/";
    $fileName = null;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {

        $fileName = time() . "_" . preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            basename($_FILES['profile_image']['name'])
        );

        if (!move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $uploadDir . $fileName
        )) {
            return "Failed to upload profile image.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (FullName, Email, Password, Usertype, Location, JobTitle, CompanyName, ContactNumber, ProfileImagePath) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssss",
        $name,
        $email,
        $password,
        $usertype,
        $location,
        $jobTitle,
        $companyName,
        $contact,
        $fileName
    );

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    }

    $error = "Database error: " . $stmt->error;
    $stmt->close();

    return $error;
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