<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch trainer info
$trainer_id = $_SESSION['trainer_id'];
$stmt = $conn->prepare("SELECT name FROM gym_trainers WHERE trainer_id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            text-align: center;
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }
        
        .trainer-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        
        .trainer-name {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .trainer-role {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 1rem;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .top-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .action-btn i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
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
            
            .action-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="trainer-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="trainer-name"><?= htmlspecialchars($trainer['name']) ?></div>
            <div class="trainer-role">Fitness Trainer</div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_add_plan.php" class="nav-link">
                    <i class="fas fa-plus"></i>
                    Add Workout Plan
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_view_plans.php" class="nav-link">
                    <i class="fas fa-dumbbell"></i>
                    View Plans
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_view_members.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    View Members
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_manage_members.php" class="nav-link">
                    <i class="fas fa-cogs"></i>
                    Manage Members
                </a>
            </li>
            <li class="nav-item">
                <a href="add_diet_plan.php" class="nav-link">
                    <i class="fas fa-utensils"></i>
                    Add Diet Plan
                </a>
            </li>
            <li class="nav-item">
                <a href="view_diet_plans.php" class="nav-link">
                    <i class="fas fa-list"></i>
                    View Diet Plans
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_view_diet_requests.php" class="nav-link">
                    <i class="fas fa-inbox"></i>
                    Diet Requests
                </a>
            </li>
            <li class="nav-item">
                <a href="update_trainer_profile.php" class="nav-link">
                    <i class="fas fa-user-edit"></i>
                    Profile Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="index.html" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Dashboard Overview</h1>
            <div class="text-muted">Welcome back, <?= htmlspecialchars($trainer['name']) ?>!</div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="stat-number">12</div>
                <div class="stat-label">Workout Plans</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number">45</div>
                <div class="stat-label">Active Members</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-number">8</div>
                <div class="stat-label">Diet Plans</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="stat-number">3</div>
                <div class="stat-label">New Requests</div>
            </div>
        </div>

        <div class="quick-actions">
            <h2 class="section-title">Quick Actions</h2>
            <div class="action-grid">
                <a href="trainer_add_plan.php" class="action-btn">
                    <i class="fas fa-plus"></i>
                    Add New Plan
                </a>
                <a href="trainer_view_members.php" class="action-btn">
                    <i class="fas fa-eye"></i>
                    View Members
                </a>
                <a href="add_diet_plan.php" class="action-btn">
                    <i class="fas fa-utensils"></i>
                    Create Diet Plan
                </a>
                <a href="trainer_view_diet_requests.php" class="action-btn">
                    <i class="fas fa-inbox"></i>
                    Check Requests
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.querySelector('.mobile-toggle');
                
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>