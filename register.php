<?php    
session_start();

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

function isValidPhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

function isValidPassword($password) {
    return strlen($password) >= 6;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $passwordRaw = $_POST['password'];
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $place    = trim($_POST['place']);
    $gender   = $_POST['gender'];
    $dob      = $_POST['dob'];
    $role     = 'Member';

    // Validation
    if (empty($name) || empty($email) || empty($passwordRaw) || empty($phone) || empty($address) || empty($place) || empty($gender) || empty($dob)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!str_ends_with($email, '@gmail.com')) {
        $error = "Email must be a Gmail address (@gmail.com).";
    } elseif (!isValidPhone($phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } elseif (!isValidPassword($passwordRaw)) {
        $error = "Password must be at least 6 characters.";
    } else {
        $today = new DateTime();
        $birthDate = new DateTime($dob);
        $age = $today->diff($birthDate)->y;

        if ($age < 15) {
            $error = "You must be at least 15 years old to register.";
        } else {
            $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

            $checkStmt = $conn->prepare("SELECT user_id FROM gym_users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                $stmt = $conn->prepare("INSERT INTO gym_users (name, email, password, phone, address, place, gender, dob, role) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $name, $email, $password, $phone, $address, $place, $gender, $dob, $role);

                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
                $stmt->close();
            }
            $checkStmt->close();
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
    <title>Join FitZone Pro - Member Registration</title>
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

        .registration-container {
            max-width: 500px;
            width: 100%;
            margin: 120px auto 40px;
            padding: 0 20px;
        }

        .registration-card {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 2rem;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #2ed573;
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

        @media (max-width: 768px) {
            .registration-card {
                padding: 2rem;
                margin: 100px 15px 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
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

    <div class="registration-container">
        <div class="registration-card">
            <div class="card-header">
                <div class="logo">FitZone<span>Pro</span></div>
                <h1>Join Our Community</h1>
                <p>Start your fitness journey today</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="example@gmail.com">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Min 6 characters">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" required placeholder="10-digit number">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" required placeholder="Enter your full address" rows="3"></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="place">City/Town</label>
                        <input type="text" id="place" name="place" class="form-control" required placeholder="Enter your city">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control" required>
                </div>

                <button type="submit" name="register" class="btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Register Now
                </button>

                <div class="form-links">
                    Already a member? <a href="login.php">Login here</a>
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

        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = this.email.value;
            const phone = this.phone.value;
            
            if (!email.endsWith('@gmail.com')) {
                alert('Email must be a Gmail address (@gmail.com).');
                e.preventDefault();
                return;
            }
            
            if (!/^\d{10}$/.test(phone)) {
                alert('Phone number must be exactly 10 digits.');
                e.preventDefault();
                return;
            }
        });

        // Smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.registration-card');
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