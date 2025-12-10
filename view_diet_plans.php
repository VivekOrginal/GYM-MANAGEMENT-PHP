<?php
session_start();
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$trainer_id = $_SESSION['trainer_id'];

// Handle delete request
if (isset($_GET['delete'])) {
    $diet_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM diet_plans WHERE diet_id = ? AND trainer_id = ?");
    $stmt->bind_param("ii", $diet_id, $trainer_id);
    $stmt->execute();
    header("Location: view_diet_plans.php");
    exit;
}

// Fetch trainer info
$stmt = $conn->prepare("SELECT name FROM gym_trainers WHERE trainer_id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$trainer_result = $stmt->get_result();
$trainer = $trainer_result->fetch_assoc();

// Fetch plans including fee
$stmt = $conn->prepare("SELECT * FROM diet_plans WHERE trainer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Diet Plans</title>
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
        
        .content-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .add-btn i {
            margin-right: 0.5rem;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #212529;
            border: none;
        }
        
        .btn-edit:hover {
            background: #ffb300;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
        }
        
        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .file-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .file-link:hover {
            color: #764ba2;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
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
            
            .table-responsive {
                font-size: 0.875rem;
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
                <a href="trainer_dashboard.php" class="nav-link">
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
                <a href="view_diet_plans.php" class="nav-link active">
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
            <h1 class="page-title">Diet Plans Management</h1>
            <div class="text-muted">Manage your nutrition programs</div>
        </div>

        <div class="content-card">
            <a href="add_diet_plan.php" class="add-btn">
                <i class="fas fa-plus"></i>
                Add New Diet Plan
            </a>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>File</th>
                                <th>Fee (₹)</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sn = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $sn++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                    <td><?= nl2br(htmlspecialchars(substr($row['description'], 0, 100))) ?><?= strlen($row['description']) > 100 ? '...' : '' ?></td>
                                    <td>
                                        <?php if (!empty($row['diet_file'])): ?>
                                            <a href="<?= htmlspecialchars($row['diet_file']) ?>" target="_blank" class="file-link">
                                                <i class="fas fa-file-alt"></i> View File
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>₹<?= number_format($row['fee'], 2) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <a href="edit_diet_plan.php?id=<?= $row['diet_id'] ?>" class="btn btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?= $row['diet_id'] ?>" class="btn btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this diet plan?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-utensils"></i>
                    <h4>No Diet Plans Yet</h4>
                    <p>You haven't created any diet plans yet. Start by adding your first nutrition program!</p>
                </div>
            <?php endif; ?>
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
