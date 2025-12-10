<?php
ob_start();
session_start();

$conn = new mysqli("localhost", "root", "", "gym_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, name, password, role, status FROM gym_users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    if ($user['role'] === 'Admin' && isset($user['status']) && $user['status'] === 'Pending') {
                        $error = "Admin account pending approval.";
                    } elseif ($user['role'] === 'Admin' && isset($user['status']) && $user['status'] === 'Rejected') {
                        $error = "Admin account rejected.";
                    } else {
                        $_SESSION['user_id']  = $user['user_id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['role']     = $user['role'];

                        if ($user['role'] === 'Admin') {
                            header("Location: admin_dashboard.php");
                        } else {
                            header("Location: member_dashboard.php");
                        }
                        exit;
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitZone Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff4757;
            --secondary-color: #1a1a1a;
            --accent-color: #ff6b7a;
            --text-dark: #ffffff;
            --text-light: #b0b0b0;
            --dark-bg: #111111;
            --card-bg: #1a1a1a;
            --gradient-primary: linear-gradient(135deg, #ff4757 0%, #ff6b7a 100%);
            --shadow-light: 0 5px 15px rgba(255, 255, 255, 0.05);
            --shadow-medium: 0 10px 30px rgba(255, 255, 255, 0.08);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #000000;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .video-background video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            padding: 1rem 0;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo h2 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--text-dark);
            margin: 0;
        }

        .nav-logo span {
            color: var(--primary-color);
        }

        .back-btn {
            background: var(--gradient-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 20px;
        }

        .login-card {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .card-header .logo {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .card-header .logo span {
            color: var(--primary-color);
        }

        .card-header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .card-header p {
            color: var(--text-light);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            background: var(--dark-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-dark);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            padding: 1.2rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-danger {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: var(--primary-color);
        }

        .form-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .form-links a:hover {
            color: var(--accent-color);
        }

        .forgot-password {
            text-align: center;
            margin-bottom: 1rem;
        }

        .forgot-password a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .forgot-password a:hover {
            color: var(--primary-color);
        }

        .admin-link {
            display: block;
            margin-top: 0.5rem;
            color: var(--accent-color) !important;
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
                margin: 0 15px;
            }

            .card-header .logo {
                font-size: 1.5rem;
            }

            .card-header h1 {
                font-size: 1.5rem;
            }

            .nav-container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Video Background -->
    <div class="video-background">
        <video autoplay muted loop>
            <source src="assets/videos/trainer-bg.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>FitZone<span>Pro</span></h2>
            </div>
            <a href="modern.html" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <div class="logo">FitZone<span>Pro</span></div>
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="forgot-password">
                    <a href="user_forgot_password.php">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>

                <div class="form-links">
                    Don't have an account? <a href="register.php">Register here</a>
                    <a href="admin_register.php" class="admin-link">Admin Registration</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.login-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>