<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
$message = "";

// Handle approval/rejection/deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['request_id']) && isset($_POST['action'])) {
        $request_id = $_POST['request_id'];
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE gym_users SET status = 'Active' WHERE user_id = ? AND role = 'Admin' AND status = 'Pending'");
            $stmt->bind_param("i", $request_id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = "Admin request approved successfully!";
            }
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE gym_users SET status = 'Rejected' WHERE user_id = ? AND role = 'Admin' AND status = 'Pending'");
            $stmt->bind_param("i", $request_id);
            if ($stmt->execute()) {
                $message = "Admin request rejected.";
            }
        }
    }
}

// Handle admin deletion
if (isset($_POST['delete_admin'])) {
    $admin_id = $_POST['admin_id'];
    if ($admin_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM gym_users WHERE user_id = ? AND role = 'Admin'");
        $stmt->bind_param("i", $admin_id);
        if ($stmt->execute()) {
            $message = "Admin deleted successfully!";
        }
    } else {
        $message = "You cannot delete yourself!";
    }
}

// Get pending requests
$pendingRequests = $conn->query("SELECT user_id, name, email, phone FROM gym_users WHERE role = 'Admin' AND status = 'Pending' ORDER BY user_id DESC");

// Get all active admins
$admins = $conn->query("SELECT user_id, name, email, phone FROM gym_users WHERE role = 'Admin' AND status = 'Active' ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - FitZone Pro</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header p {
            color: var(--text-light);
            font-size: 1.2rem;
        }

        .section-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .section-header {
            background: var(--gradient-primary);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            margin: 0;
        }

        .section-body {
            padding: 2rem;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        .admin-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .admin-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 600;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-approve {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.4);
            color: white;
        }

        .btn-reject {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.4);
            color: white;
        }

        .current-user-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-light);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .alert {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 1rem 15px;
            }

            .admin-info {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                justify-content: center;
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
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Admin Management</h1>
            <p>Manage admin registrations and permissions</p>
        </div>

        <?php if ($message): ?>
            <div class="alert">
                <i class="fas fa-info-circle"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Pending Admin Requests -->
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-clock"></i>
                <h2 class="section-title">Pending Admin Requests</h2>
            </div>
            <div class="section-body">
                <?php if ($pendingRequests->num_rows > 0): ?>
                    <?php while ($request = $pendingRequests->fetch_assoc()): ?>
                        <div class="admin-card">
                            <div class="admin-info">
                                <div class="info-item">
                                    <i class="fas fa-user" style="color: var(--primary-color);"></i>
                                    <div>
                                        <div class="info-label">Name</div>
                                        <div class="info-value"><?= htmlspecialchars($request['name']) ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                                    <div>
                                        <div class="info-label">Email</div>
                                        <div class="info-value"><?= htmlspecialchars($request['email']) ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone" style="color: var(--primary-color);"></i>
                                    <div>
                                        <div class="info-label">Phone</div>
                                        <div class="info-value"><?= htmlspecialchars($request['phone']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="action-buttons">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?= $request['user_id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn-approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?= $request['user_id'] ?>">
                                    <button type="submit" name="action" value="reject" class="btn-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h4>No Pending Requests</h4>
                        <p>All admin requests have been processed.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Current Admins -->
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-users"></i>
                <h2 class="section-title">Current Admins</h2>
            </div>
            <div class="section-body">
                <?php while ($admin = $admins->fetch_assoc()): ?>
                    <div class="admin-card">
                        <div class="admin-info">
                            <div class="info-item">
                                <i class="fas fa-user" style="color: var(--primary-color);"></i>
                                <div>
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?= htmlspecialchars($admin['name']) ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                                <div>
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?= htmlspecialchars($admin['email']) ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone" style="color: var(--primary-color);"></i>
                                <div>
                                    <div class="info-label">Phone</div>
                                    <div class="info-value"><?= htmlspecialchars($admin['phone']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <?php if ($admin['user_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this admin?')">
                                    <input type="hidden" name="admin_id" value="<?= $admin['user_id'] ?>">
                                    <button type="submit" name="delete_admin" class="btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="current-user-badge">
                                    <i class="fas fa-crown"></i> You
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <script>
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
        
        // Add loading state to buttons
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
</body>
</html>