<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$trainer_id = $_SESSION['trainer_id'];

// Fetch trainer info
$trainerStmt = $conn->prepare("SELECT name FROM gym_trainers WHERE trainer_id = ?");
$trainerStmt->bind_param("i", $trainer_id);
$trainerStmt->execute();
$trainerResult = $trainerStmt->get_result();
$trainer = $trainerResult->fetch_assoc();

// Handle activation/deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_id'])) {
    $membership_id = $_POST['membership_id'];
    $action = $_POST['action']; // "activate" or "deactivate"
    $new_status = $action === 'activate' ? 'Yes' : 'No';
    $status_text = $action === 'activate' ? 'activated' : 'deactivated';

    // Get user email & name
    $userStmt = $conn->prepare("
        SELECT u.email, u.name 
        FROM memberships m 
        JOIN gym_users u ON m.user_id = u.user_id 
        WHERE m.membership_id = ?
    ");
    $userStmt->bind_param("i", $membership_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult && $userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        $email = $user['email'];
        $name = $user['name'];

        // Update activation status
        $stmt = $conn->prepare("UPDATE memberships SET is_active = ? WHERE membership_id = ?");
        $stmt->bind_param("si", $new_status, $membership_id);
        $stmt->execute();

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anusreeramesh112@gmail.com'; // ✅ Replace
            $mail->Password   = 'gofj aris sjrs kdbg';    // ✅ Replace
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('anusreeramesh112@gmail.com', 'Your Gym');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Membership ' . ucfirst($status_text);
            $mail->Body    = "Hi <strong>$name</strong>,<br>Your gym membership has been <b style='color:" . ($action === 'activate' ? 'green' : 'red') . ";'>$status_text</b> by your trainer.<br><br>Regards,<br>Your Gym";

            $mail->send();
        } catch (Exception $e) {
            echo "<script>alert('Email could not be sent.');</script>";
        }
    }
}

// Fetch all members under this trainer
$stmt = $conn->prepare("
    SELECT m.*, u.name, u.email, u.phone, p.plan_title
    FROM memberships m
    JOIN gym_users u ON m.user_id = u.user_id
    JOIN trainer_workout_plans p ON m.plan_id = p.plan_id
    WHERE p.trainer_id = ?
    ORDER BY m.booking_date DESC
");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
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
        
        .members-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            background: #f8f9fa;
            border: none;
            padding: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .table td {
            padding: 1rem;
            border: none;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-activate {
            background: #28a745;
            color: white;
        }
        
        .btn-deactivate {
            background: #dc3545;
            color: white;
        }
        
        .btn-activate:hover, .btn-deactivate:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .no-members {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
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
            
            .table-responsive {
                font-size: 0.85rem;
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
            <div class="trainer-name"><?= htmlspecialchars($trainer['name'] ?? 'Trainer') ?></div>
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
                <a href="trainer_manage_members.php" class="nav-link active">
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
            <h1 class="page-title">Manage Members</h1>
            <div class="text-muted">Activate or deactivate member accounts</div>
        </div>

        <div class="members-table">
            <div class="table-header">
                <i class="fas fa-users me-2"></i>Member Management
            </div>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Plan</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['plan_title']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['booking_date'])) ?></td>
                                    <td>
                                        <?php if ($row['is_active'] === 'Yes'): ?>
                                            <span class="status-badge status-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                            <input type="hidden" name="membership_id" value="<?= $row['membership_id'] ?>">
                                            <?php if ($row['is_active'] === 'Yes'): ?>
                                                <button type="submit" name="action" value="deactivate" class="action-btn btn-deactivate">Deactivate</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="activate" class="action-btn btn-activate">Activate</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-members">
                    <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                    <p>No members found under your training plans.</p>
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
