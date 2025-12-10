<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch user's latest membership
$stmt = $conn->prepare("
    SELECT m.is_active, p.plan_title, m.booking_date
    FROM memberships m
    JOIN trainer_workout_plans p ON m.plan_id = p.plan_id
    WHERE m.user_id = ?
    ORDER BY m.booking_date DESC LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Membership Status - FitZone Pro</title>
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
            max-width: 800px;
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

        .membership-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .membership-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .plan-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .membership-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }

        .info-label {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .status-active {
            color: #22c55e;
        }

        .status-inactive {
            color: #ef4444;
        }

        .action-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        .btn-disabled {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: not-allowed;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .no-membership {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-membership i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 1rem 15px;
            }

            .membership-info {
                grid-template-columns: 1fr;
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
        <div class="page-header">
            <h1>My Membership Status</h1>
            <p>View your current membership details and status</p>
        </div>

        <?php if ($result && $result->num_rows > 0): 
            $row = $result->fetch_assoc();
            $is_active = $row['is_active'];
            $status = $is_active === 'Yes' ? 'Active' : 'Not Active';
            $status_class = $is_active === 'Yes' ? 'status-active' : 'status-inactive';
            $status_icon = $is_active === 'Yes' ? 'fas fa-check-circle' : 'fas fa-times-circle';
        ?>
            <div class="membership-card">
                <h2 class="plan-title">
                    <i class="fas fa-dumbbell"></i>
                    <?= htmlspecialchars($row['plan_title']) ?>
                </h2>
                
                <div class="membership-info">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-alt"></i> Booking Date
                        </div>
                        <div class="info-value"><?= date('M d, Y', strtotime($row['booking_date'])) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-flag"></i> Status
                        </div>
                        <div class="info-value <?= $status_class ?>">
                            <i class="<?= $status_icon ?>"></i>
                            <?= $status ?>
                        </div>
                    </div>
                </div>
                
                <div class="action-section">
                    <?php if ($is_active === 'Yes'): ?>
                        <a href="request_diet_plan.php" class="btn-primary">
                            <i class="fas fa-utensils"></i>
                            Request Diet Plan
                        </a>
                    <?php else: ?>
                        <button class="btn-disabled" disabled>
                            <i class="fas fa-lock"></i>
                            Only Active Members Can Request Diet Plans
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="no-membership">
                <i class="fas fa-user-slash"></i>
                <h3>No Active Membership</h3>
                <p>You don't have an active membership yet. Contact your trainer to get started!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
