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

try {
    $stmt = $conn->prepare("
        SELECT b.*, p.plan_title, p.price, p.duration_weeks,
               t.name AS trainer_name, t.gym_name, t.gym_location, t.gym_contact
        FROM memberships b 
        JOIN trainer_workout_plans p ON b.plan_id = p.plan_id
        JOIN gym_trainers t ON p.trainer_id = t.trainer_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
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
    <title>My Bookings - FitZone Pro</title>
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

        .bookings-grid {
            display: grid;
            gap: 2rem;
        }

        .booking-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            transition: var(--transition);
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .booking-title {
            flex: 1;
        }

        .booking-title h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .booking-date {
            color: var(--text-light);
            font-size: 0.9rem;
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

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-group {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 8px;
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
            font-size: 1rem;
        }

        .price-highlight {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .booking-actions {
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

        .no-bookings {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-bookings i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .no-bookings h3 {
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
            .booking-header {
                flex-direction: column;
                gap: 1rem;
            }

            .booking-details {
                grid-template-columns: 1fr;
            }

            .booking-actions {
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
            <h1>My Bookings</h1>
            <p>Track your workout plan subscriptions and memberships</p>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php
            $total_bookings = 0;
            $approved_bookings = 0;
            $pending_bookings = 0;
            $bookings_data = [];
            
            while ($row = $result->fetch_assoc()) {
                $bookings_data[] = $row;
                $total_bookings++;
                if ($row['status'] == 'Approved') $approved_bookings++;
                if ($row['status'] == 'Pending') $pending_bookings++;
            }
            ?>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?= $total_bookings ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?= $approved_bookings ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?= $pending_bookings ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>

            <!-- Bookings Grid -->
            <div class="bookings-grid">
                <?php foreach ($bookings_data as $row): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="booking-title">
                                <h3><?= htmlspecialchars($row['plan_title']) ?></h3>
                                <div class="booking-date">
                                    <i class="fas fa-calendar"></i>
                                    Booked on <?= date('d M Y', strtotime($row['booking_date'])) ?>
                                </div>
                            </div>
                            <div class="status-badge status-<?= strtolower($row['status']) ?>">
                                <?= $row['status'] ?>
                            </div>
                        </div>

                        <div class="booking-details">
                            <div class="detail-group">
                                <div class="detail-label">Trainer</div>
                                <div class="detail-value"><?= htmlspecialchars($row['trainer_name']) ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Gym</div>
                                <div class="detail-value"><?= htmlspecialchars($row['gym_name']) ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Location</div>
                                <div class="detail-value"><?= htmlspecialchars($row['gym_location']) ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Contact</div>
                                <div class="detail-value"><?= htmlspecialchars($row['gym_contact']) ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Duration</div>
                                <div class="detail-value"><?= $row['duration_weeks'] ?> months</div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Price</div>
                                <div class="detail-value price-highlight">₹<?= number_format($row['price'], 2) ?>/month</div>
                            </div>
                        </div>

                        <?php if ($row['status'] == 'Approved'): ?>
                            <div class="booking-actions">
                                <a href="pay_membership_fee.php?membership_id=<?= $row['membership_id'] ?>" class="action-btn">
                                    <i class="fas fa-credit-card"></i>
                                    Pay Now
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-bookings">
                <i class="fas fa-calendar-times"></i>
                <h3>No Bookings Yet</h3>
                <p>You haven't booked any workout plans yet. Start your fitness journey today!</p>
                <a href="search_gyms.php" class="action-btn" style="margin-top: 1rem; display: inline-flex;">
                    <i class="fas fa-search"></i>
                    Find Gyms
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>