<?php
session_start();
require_once '../classes/AuthClass.php';
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
        header("Location: admindashboard.php");
    } else {
        header("Location: home.php");
    }
    exit();
}
$displayResult = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $loginSuccess = AuthClass::login($username, $password, $remember);
    if ($loginSuccess) {
        if ($loginSuccess['Usertype'] === 'admin') {
            header("Location: admindashboard.php");
        } else {
            header("Location: home.php");
        }
        exit();
    } else {
        $displayResult = "Invalid login";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Job System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="robots" content="noindex, follow">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">

            <div class="col-md-7 login-visual-pane position-relative">
                <div class="gradient-overlay position-absolute top-0 start-0 w-100 h-100"></div>
            </div>

            <div class="col-12 col-md-5 login-form-pane d-flex align-items-center justify-content-center bg-white p-4 p-md-5">
                <div class="w-100" style="max-width: 420px;">
                    
                    <h1 class="jobful-title">WorkJourney</h1>
                    <h2 class="fw-bold text-dark mb-5" style="font-size: 1.2rem;">Sign In</h2>

                    <?php if (!empty($displayResult)): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-4 rounded-3 d-flex align-items-center gap-2" role="alert" style="font-size: 0.85rem;">
                            <i class="fa fa-exclamation-triangle text-danger"></i>
                            <div class="fw-medium">
                                <?php echo htmlspecialchars($displayResult); ?>
                            </div>
                            <button type="button" class="btn-close small shadow-none" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem 1rem; font-size: 0.65rem;"></button>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" class="validate-form">
                        
                        <div class="mb-4">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Username or Email</label>
                            <input class="form-control-minimal" type="text" name="username" placeholder="Username or email address..." required autocomplete="username">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted-custom mb-0 fw-medium">Password</label>
                            <div class="password-field">
                                <input type="checkbox" class="password-checkbox" id="togglePassword">
                                <label class="password-toggle" for="togglePassword">
                                    <i class="fa fa-eye"></i>
                                </label>
                                <input class="form-control-minimal no-js-password" type="password" name="password" placeholder="*************" required autocomplete="current-password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-5 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                <label class="form-check-label text-muted-custom" for="rememberMe">Keep me logged in</label>
                            </div>
                            <a href="forgetpassword.php" class="link-dark-custom small text-muted-custom">Forgot Password?</a>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <button name="Login_Btn" type="submit" class="btn btn-gradient fw-medium">Sign In</button>
                            
                            <a href="register.php" class="link-dark-custom fw-medium text-muted-custom text-dark">
                                Sign up <i class="fa fa-long-arrow-right m-l-5"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('.no-js-password');
        togglePassword.addEventListener('change', function (e) {
            const type = this.checked ? 'text' : 'password';
            password.setAttribute('type', type);
        });
    </script>
</body>
</html>