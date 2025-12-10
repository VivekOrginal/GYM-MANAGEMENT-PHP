<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Memberships Query
$membership_query = "SELECT m.*, gu.name AS member_name, tp.plan_title 
                     FROM memberships m
                     JOIN gym_users gu ON m.user_id = gu.user_id
                     JOIN trainer_workout_plans tp ON m.plan_id = tp.plan_id
                     ORDER BY m.booking_date DESC";
$membership_result = $conn->query($membership_query);

// Diet Requests Query
$diet_request_query = "SELECT dr.*, gu.name, dp.title 
                       FROM diet_requests dr
                       JOIN gym_users gu ON dr.user_id = gu.user_id
                       JOIN diet_plans dp ON dr.diet_id = dp.diet_id
                       ORDER BY dr.request_date DESC";
$diet_request_result = $conn->query($diet_request_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memberships & Diet Requests - FitZone Pro</title>
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
            max-width: 1400px;
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
            max-width: 1400px;
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

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .section-table {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .table-header {
            background: var(--gradient-primary);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .table-header h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            margin: 0;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table th {
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
        }

        .member-info {
            display: flex;
            align-items: center;
        }

        .member-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
        }

        .member-details p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-inactive {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .payment-paid {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .payment-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .payment-failed {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .no-records {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-records i {
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

            .stats-section {
                grid-template-columns: repeat(2, 1fr);
            }

            .table-wrapper {
                font-size: 0.8rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
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
            <h1>Memberships & Diet Requests</h1>
            <p>Complete overview of member activities and requests</p>
        </div>

        <?php 
        $membership_count = $membership_result ? $membership_result->num_rows : 0;
        $diet_count = $diet_request_result ? $diet_request_result->num_rows : 0;
        $total_records = $membership_count + $diet_count;
        ?>
        
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="stat-number"><?= $total_records ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-id-card"></i></div>
                <div class="stat-number"><?= $membership_count ?></div>
                <div class="stat-label">Memberships</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-utensils"></i></div>
                <div class="stat-number"><?= $diet_count ?></div>
                <div class="stat-label">Diet Requests</div>
            </div>
        </div>

        <!-- Memberships Table -->
        <?php if ($membership_result && $membership_result->num_rows > 0): ?>
            <div class="section-table">
                <div class="table-header">
                    <i class="fas fa-id-card"></i>
                    <h3>Membership Records (<?= $membership_count ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member</th>
                                <th>Plan</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while($row = $membership_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="member-info">
                                            <div class="member-avatar">
                                                <?= strtoupper(substr($row['member_name'], 0, 1)) ?>
                                            </div>
                                            <div class="member-details">
                                                <h4><?= htmlspecialchars($row['member_name']) ?></h4>
                                                <p>ID: #<?= $row['membership_id'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['plan_title']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['booking_date'])) ?></td>
                                    <td>
                                        <?php
                                            $status = strtolower($row['status']);
                                            $statusClass = $status === 'active' ? 'status-active' : ($status === 'pending' ? 'status-pending' : 'status-inactive');
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $payment = strtolower($row['payment_status']);
                                            $paymentClass = $payment === 'paid' ? 'payment-paid' : ($payment === 'pending' ? 'payment-pending' : 'payment-failed');
                                        ?>
                                        <span class="status-badge <?= $paymentClass ?>">
                                            <?= $row['payment_status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="section-table">
                <div class="table-header">
                    <i class="fas fa-id-card"></i>
                    <h3>Membership Records (0)</h3>
                </div>
                <div class="no-records">
                    <i class="fas fa-id-card"></i>
                    <h3>No Membership Records</h3>
                    <p>No membership records have been found.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Diet Requests Table -->
        <?php if ($diet_request_result && $diet_request_result->num_rows > 0): ?>
            <div class="section-table">
                <div class="table-header">
                    <i class="fas fa-utensils"></i>
                    <h3>Diet Plan Requests (<?= $diet_count ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member</th>
                                <th>Diet Plan</th>
                                <th>Request Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while($row = $diet_request_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="member-info">
                                            <div class="member-avatar">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <div class="member-details">
                                                <h4><?= htmlspecialchars($row['name']) ?></h4>
                                                <p>ID: #<?= $row['request_id'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['request_date'])) ?></td>
                                    <td>
                                        <?php
                                            $status = strtolower($row['status']);
                                            $statusClass = $status === 'active' ? 'status-active' : ($status === 'pending' ? 'status-pending' : 'status-inactive');
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $payment = strtolower($row['payment_status']);
                                            $paymentClass = $payment === 'paid' ? 'payment-paid' : ($payment === 'pending' ? 'payment-pending' : 'payment-failed');
                                        ?>
                                        <span class="status-badge <?= $paymentClass ?>">
                                            <?= $row['payment_status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="section-table">
                <div class="table-header">
                    <i class="fas fa-utensils"></i>
                    <h3>Diet Plan Requests (0)</h3>
                </div>
                <div class="no-records">
                    <i class="fas fa-utensils"></i>
                    <h3>No Diet Requests</h3>
                    <p>No diet plan requests have been found.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
