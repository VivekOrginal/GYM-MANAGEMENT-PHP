<?php
session_start();

// Restrict access to Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['user_name'];

// Get dashboard stats
$conn = new mysqli("localhost", "root", "", "gym_management");
$total_members = 0;
$total_trainers = 0;
$pending_approvals = 0;

try {
    $members_result = $conn->query("SELECT COUNT(*) as count FROM gym_users WHERE role = 'Member'");
    $total_members = $members_result->fetch_assoc()['count'];
    
    $trainers_result = $conn->query("SELECT COUNT(*) as count FROM gym_trainers WHERE status = 'Approved'");
    $total_trainers = $trainers_result->fetch_assoc()['count'];
    
    $pending_result = $conn->query("SELECT COUNT(*) as count FROM gym_trainers WHERE status = 'Pending'");
    $pending_approvals = $pending_result->fetch_assoc()['count'];
} catch (Exception $e) {
    // Handle database errors gracefully
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitZone Pro</title>
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

        .sidebar::-webkit-scrollbar {
            width: 0px;
            background: transparent;
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

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .admin-avatar {
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

        .admin-details h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .admin-details p {
            font-size: 0.8rem;
            color: var(--text-light);
            margin: 0;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0 2rem 0.5rem;
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
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
                <div class="admin-info">
                    <div class="admin-avatar">
                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                    </div>
                    <div class="admin-details">
                        <h4><?php echo htmlspecialchars($name); ?></h4>
                        <p>Administrator</p>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Dashboard</div>
                    <div class="nav-item">
                        <a href="#" class="nav-link active">
                            <i class="fas fa-tachometer-alt"></i>
                            Overview
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">User Management</div>
                    <div class="nav-item">
                        <a href="manage_members.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            Manage Members
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="admin_trainers.php" class="nav-link">
                            <i class="fas fa-user-tie"></i>
                            Manage Trainers
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="admin_view_all_trainers.php" class="nav-link">
                            <i class="fas fa-eye"></i>
                            View All Trainers
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Content Management</div>
                    <div class="nav-item">
                        <a href="admin_add_category.php" class="nav-link">
                            <i class="fas fa-plus-circle"></i>
                            Add Category
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="admin_view_categories.php" class="nav-link">
                            <i class="fas fa-folder-open"></i>
                            View Categories
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="admin_view_all_plans.php" class="nav-link">
                            <i class="fas fa-clipboard-list"></i>
                            Manage Plans
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Operations</div>
                    <div class="nav-item">
                        <a href="admin_view_memberships_and_diet_requests.php" class="nav-link">
                            <i class="fas fa-credit-card"></i>
                            View Payments
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="admin_approvals.php" class="nav-link">
                            <i class="fas fa-shield-alt"></i>
                            Admin Management
                        </a>
                    </div>
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
                <p>Here's what's happening with your gym management system today.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_members; ?></div>
                    <div class="stat-label">Total Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_trainers; ?></div>
                    <div class="stat-label">Active Trainers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $pending_approvals; ?></div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
            </div>

            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="manage_members.php" class="action-btn">
                        <i class="fas fa-users"></i>
                        Manage Members
                    </a>
                    <a href="admin_trainers.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        Add Trainer
                    </a>
                    <a href="admin_add_category.php" class="action-btn">
                        <i class="fas fa-plus-circle"></i>
                        Add Category
                    </a>
                    <a href="admin_approvals.php" class="action-btn">
                        <i class="fas fa-check-circle"></i>
                        Review Approvals
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