<?php 
session_start();

// Restrict access if not logged in or not a member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Member') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get member stats (with error handling for missing tables)
$total_bookings = 0;
$membership_status = 'Active';

try {
    $stats_query = "SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?";
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    $total_bookings = $bookings_result->fetch_assoc()['total_bookings'];
} catch (Exception $e) {
    $total_bookings = 0;
}

try {
    $membership_query = "SELECT status FROM memberships WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($membership_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $membership_result = $stmt->get_result();
    $membership_status = $membership_result->num_rows > 0 ? $membership_result->fetch_assoc()['status'] : 'Active';
} catch (Exception $e) {
    $membership_status = 'Active';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - FitZone Pro</title>
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
            --sidebar-bg: #0f0f0f;
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
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .logo span {
            color: var(--primary-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .user-details h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .user-details p {
            font-size: 0.8rem;
            color: var(--text-light);
            margin: 0;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 71, 87, 0.1);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            margin-top: 2rem;
            padding: 0 2rem;
        }

        .logout-btn .nav-link {
            background: rgba(255, 71, 87, 0.1);
            border-radius: var(--border-radius);
            margin: 0 1rem;
            color: var(--primary-color);
        }

        .logout-btn .nav-link:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: var(--dark-bg);
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .quick-actions {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .quick-actions h3 {
            font-family: 'Oswald', sans-serif;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            text-align: center;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        /* Mobile Responsive */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: var(--border-radius);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .mobile-toggle {
                display: block;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">FitZone<span>Pro</span></div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($name); ?></h4>
                        <p>Member</p>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="search_gyms.php" class="nav-link">
                        <i class="fas fa-search"></i>
                        Search Gyms
                    </a>
                </div>
                <div class="nav-item">
                    <a href="member_view_categories.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        Categories
                    </a>
                </div>
                <div class="nav-item">
                    <a href="my_bookings.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i>
                        My Bookings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="my_membership_status.php" class="nav-link">
                        <i class="fas fa-id-card"></i>
                        Membership Status
                    </a>
                </div>
                <div class="nav-item">
                    <a href="member_view_diet.php" class="nav-link">
                        <i class="fas fa-apple-alt"></i>
                        Diet Plans
                    </a>
                </div>
                <div class="nav-item">
                    <a href="member_profile.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        My Profile
                    </a>
                </div>
                
                <div class="logout-btn">
                    <a href="index.html" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo htmlspecialchars($name); ?>!</h1>
                <p>Ready to continue your fitness journey?</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_bookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="stat-value"><?php echo $membership_status; ?></div>
                    <div class="stat-label">Membership Status</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div class="stat-value">Active</div>
                    <div class="stat-label">Account Status</div>
                </div>
            </div>

            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="search_gyms.php" class="action-btn">
                        <i class="fas fa-search"></i>
                        Find Gyms
                    </a>
                    <a href="my_bookings.php" class="action-btn">
                        <i class="fas fa-calendar-plus"></i>
                        Book Workout
                    </a>
                    <a href="member_view_diet.php" class="action-btn">
                        <i class="fas fa-apple-alt"></i>
                        View Diet Plans
                    </a>
                    <a href="member_profile.php" class="action-btn">
                        <i class="fas fa-user-edit"></i>
                        Update Profile
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>