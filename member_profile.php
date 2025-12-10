<?php
session_start();

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Member') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// If form is submitted, process the update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['update_profile'])) {
        $name    = trim($_POST['name']);
        $phone   = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $place   = trim($_POST['place']);
        $gender  = $_POST['gender'];
        $dob     = $_POST['dob'];

        $stmt = $conn->prepare("UPDATE gym_users SET name=?, phone=?, address=?, place=?, gender=?, dob=? WHERE user_id=?");
        $stmt->bind_param("ssssssi", $name, $phone, $address, $place, $gender, $dob, $user_id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
        } else {
            $error = "Update failed. Try again.";
        }
    } elseif (isset($_POST['change_password'])) {
        $new_password = $_POST['new_password'];
        
        if (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE gym_users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Failed to change password.";
            }
        }
    }
}

// Fetch member details
$sql = "SELECT name, email, phone, address, place, gender, dob FROM gym_users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Member not found.";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FitZone Pro</title>
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
        }

        .navbar {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
            box-shadow: var(--shadow-medium);
        }

        .profile-header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .profile-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .profile-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            transition: var(--transition);
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .card-title h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .card-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
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

        .form-control:read-only {
            background: rgba(255, 255, 255, 0.05);
            cursor: not-allowed;
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
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-dark);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 8px;
        }

        .info-label {
            color: var(--text-light);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-weight: 600;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }

            .profile-header h1 {
                font-size: 2rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }

            .container {
                padding: 1rem 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>FitZone<span>Pro</span></h2>
            </div>
            <a href="member_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($row['name'], 0, 1)) ?>
            </div>
            <h1><?= htmlspecialchars($row['name']) ?></h1>
            <p>Member Profile</p>
        </div>

        <!-- Alerts -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Profile Information -->
            <div class="profile-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="card-title">
                        <h3>Profile Information</h3>
                        <div class="card-subtitle">Update your personal details</div>
                    </div>
                </div>

                <form method="post">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($row['name']) ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" required rows="3"><?= htmlspecialchars($row['address']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="place">City/Place</label>
                        <input type="text" id="place" name="place" value="<?= htmlspecialchars($row['place']) ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control" required>
                            <option value="Male" <?= $row['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $row['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $row['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($row['dob']) ?>" class="form-control" required>
                    </div>

                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Security Settings -->
            <div class="profile-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="card-title">
                        <h3>Security Settings</h3>
                        <div class="card-subtitle">Manage your account security</div>
                    </div>
                </div>

                <!-- Current Info Display -->
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($row['email']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?= htmlspecialchars($row['gender']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?= date('d M Y', strtotime($row['dob'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">2024</div>
                    </div>
                </div>

                <!-- Change Password Form -->
                <form method="post">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6" placeholder="Enter new password (min 6 characters)">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn-primary btn-secondary">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('new_password');
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
            const cards = document.querySelectorAll('.profile-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>