<?php
session_start();
include('./includes/db_config.php');

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to the respective dashboard based on the role
    header('Location: ' . ($_SESSION['role'] == 'admin' ? './admin/admin_dashboard.php' : './users/user_dashboard.php'));
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Use a prepared statement to check if the user exists
    $query = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result)) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password (plain text comparison - should be hashed in production)
        if ($password === $user['password']) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            header('Location: ' . ($user['role'] == 'admin' ? './admin/admin_dashboard.php' : './user/user_dashboard.php'));
            exit();
        } else {
            $error_message = "Incorrect username or password";
        }
    } else {
        $error_message = "User not found";
    }
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to access your Business Management System dashboard">
    <title>Login - Business Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://source.unsplash.com/1920x1080/?business,technology,office') no-repeat center center fixed;
            background-size: cover;
            color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-heading {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin: 2rem 0;
            color: white;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
        }

        .login-container {
            max-width: 450px;
            width: 90%;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-out;
            margin-bottom: 2rem;
        }

        .login-container h2 {
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-align: center;
            color: var(--dark-color);
            position: relative;
            padding-bottom: 0.5rem;
        }

        .login-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25%;
            width: 50%;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 3px;
        }

        .form-group label {
            color: var(--dark-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
        }

        .btn-login {
            background-color: var(--secondary-color);
            border: none;
            border-radius: 5px;
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: right;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }

        .forgot-password a {
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .login-footer a {
            color: var(--secondary-color);
            font-weight: 500;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .password-toggle {
            cursor: pointer;
            background: transparent;
            border: none;
            color: #6c757d;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Accessibility focus styles */
        a:focus, button:focus, input:focus {
            outline: 3px solid var(--accent-color);
            outline-offset: 2px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .page-heading {
                font-size: 2rem;
                margin: 1.5rem 0;
            }
            
            .login-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<!-- Heading at the Top -->
<div class="page-heading">Business Management System Portal</div>

<!-- Login Container -->
<div class="container d-flex justify-content-center align-items-center flex-grow-1">
    <div class="login-container">
        <h2>Sign In</h2>
        <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php } ?>
            <div class="form-group">
                <label for="username">Username or Email</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username or email" required autocomplete="username" aria-describedby="usernameHelp">
                </div>
                <small id="usernameHelp" class="form-text text-muted d-none">Enter your registered username or email</small>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                    <div class="input-group-append">
                        <button class="btn password-toggle" type="button" aria-label="Show password">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="forgot-password">
                <a href="forgot-password.html">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-login btn-block">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
            
            <div class="login-footer">
                Don't have an account? <a href="register.html">Contact administrator</a>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>

<script>
    // Password visibility toggle
    $(document).ready(function() {
        $('#togglePassword').click(function() {
            const password = $('#password');
            const icon = $(this);
            const type = password.attr('type') === 'password' ? 'text' : 'password';
            password.attr('type', type);
            icon.toggleClass('fa-eye fa-eye-slash');
        });
    });
</script>

</body>
</html>
