<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch diet requests with error handling
try {
    $stmt = $conn->prepare("
        SELECT dr.*, dp.title AS diet_title, dp.diet_file, t.name AS trainer_name 
        FROM diet_requests dr 
        JOIN diet_plans dp ON dr.diet_id = dp.diet_id 
        JOIN gym_trainers t ON dp.trainer_id = t.trainer_id 
        WHERE dr.user_id = ?
        ORDER BY dr.request_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Diet Plans - FitZone Pro</title>
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

        .diet-grid {
            display: grid;
            gap: 2rem;
        }

        .diet-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .diet-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--gradient-primary);
        }

        .diet-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .diet-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .diet-title {
            flex: 1;
        }

        .diet-title h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .diet-trainer {
            color: var(--text-light);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-approved {
            background: rgba(46, 213, 115, 0.2);
            color: #2ed573;
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .status-rejected {
            background: rgba(255, 71, 87, 0.2);
            color: var(--primary-color);
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        .diet-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.2rem;
        }

        .detail-label {
            color: var(--text-light);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .diet-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .action-btn {
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
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        .action-btn.secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .action-btn.view-file {
            background: linear-gradient(135deg, #2ed573 0%, #17c0eb 100%);
        }

        .no-diet-plans {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-diet-plans i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .no-diet-plans h3 {
            margin-bottom: 1rem;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
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

        @media (max-width: 768px) {
            .diet-header {
                flex-direction: column;
                gap: 1rem;
            }

            .diet-details {
                grid-template-columns: 1fr;
            }

            .diet-actions {
                flex-direction: column;
            }

            .page-header h1 {
                font-size: 2rem;
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
        <div class="page-header">
            <h1>My Diet Plans</h1>
            <p>Track your personalized nutrition plans and requests</p>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php
            $total_requests = 0;
            $approved_requests = 0;
            $pending_requests = 0;
            $diet_data = [];
            
            while ($row = $result->fetch_assoc()) {
                $diet_data[] = $row;
                $total_requests++;
                if ($row['status'] == 'Approved') $approved_requests++;
                if ($row['status'] == 'Pending') $pending_requests++;
            }
            ?>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-apple-alt"></i>
                    </div>
                    <div class="stat-value"><?= $total_requests ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?= $approved_requests ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?= $pending_requests ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>

            <!-- Diet Plans Grid -->
            <div class="diet-grid">
                <?php foreach ($diet_data as $row): ?>
                    <div class="diet-card">
                        <div class="diet-header">
                            <div class="diet-title">
                                <h3><?= htmlspecialchars($row['diet_title']) ?></h3>
                                <div class="diet-trainer">
                                    <i class="fas fa-user-md"></i>
                                    By <?= htmlspecialchars($row['trainer_name']) ?>
                                </div>
                            </div>
                            <div class="status-badge status-<?= strtolower($row['status']) ?>">
                                <?= $row['status'] ?>
                            </div>
                        </div>

                        <div class="diet-details">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="detail-label">Requested On</div>
                                <div class="detail-value"><?= date("d M Y", strtotime($row['request_date'])) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="detail-label">Diet File</div>
                                <div class="detail-value">Available</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="detail-label">Status</div>
                                <div class="detail-value"><?= $row['status'] ?></div>
                            </div>
                        </div>

                        <div class="diet-actions">
                            <a href="<?= htmlspecialchars($row['diet_file']) ?>" target="_blank" class="action-btn view-file">
                                <i class="fas fa-file-download"></i>
                                View Diet Plan
                            </a>
                            <?php if ($row['status'] == 'Approved'): ?>
                                <a href="pay_diet_fee.php?request_id=<?= $row['request_id'] ?>" class="action-btn">
                                    <i class="fas fa-credit-card"></i>
                                    Pay Fee
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-diet-plans">
                <i class="fas fa-apple-alt"></i>
                <h3>No Diet Plans Yet</h3>
                <p>You haven't requested any diet plans yet. Start your nutrition journey today!</p>
                <a href="request_diet_plan.php" class="action-btn" style="margin-top: 1rem; display: inline-flex;">
                    <i class="fas fa-plus"></i>
                    Request Diet Plan
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>