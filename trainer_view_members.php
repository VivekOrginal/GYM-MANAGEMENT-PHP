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

// Update status or payment approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['membership_id'], $_POST['status'])) {
        $membership_id = $_POST['membership_id'];
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE memberships SET status = ? WHERE membership_id = ?");
        $stmt->bind_param("si", $new_status, $membership_id);
        $stmt->execute();
    }

    if (isset($_POST['request_id'], $_POST['payment_status']) && $_POST['payment_status'] == 'Paid') {
        $request_id = $_POST['request_id'];
        $stmt = $conn->prepare("UPDATE diet_requests SET payment_status = 'Paid' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    }

    header("Location: trainer_view_members.php");
    exit;
}

// Fetch trainer members and their diet requests
$stmt = $conn->prepare("
    SELECT m.*, u.name, u.email, u.phone, p.plan_title, p.duration_weeks, p.price,
           d.request_id, d.status AS diet_status, d.payment_status
    FROM memberships m
    JOIN gym_users u ON m.user_id = u.user_id
    JOIN trainer_workout_plans p ON m.plan_id = p.plan_id
    LEFT JOIN diet_requests d ON d.user_id = u.user_id AND d.trainer_id = p.trainer_id
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
    <title>My Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 2rem 1rem;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .header-title {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
            margin: 0;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .member-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }
        
        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .member-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .member-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
        }
        
        .member-contact {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .member-id {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .member-info {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-icon {
            color: #667eea;
            width: 20px;
            text-align: center;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 80px;
        }
        
        .info-value {
            color: #6c757d;
            flex: 1;
        }
        
        .price-badge {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-section {
            margin: 1.5rem 0;
        }
        
        .status-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem;
            font-size: 0.9rem;
            flex: 1;
        }
        
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .diet-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .diet-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .diet-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }
        
        .btn-paid {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            border: none;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-paid:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(86, 171, 47, 0.4);
            color: white;
        }
        
        .booking-date {
            color: #6c757d;
            font-size: 0.9rem;
            text-align: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .members-grid {
                grid-template-columns: 1fr;
            }
            
            .header-title {
                font-size: 1.5rem;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-card">
            <h1 class="header-title">
                <i class="fas fa-users me-2"></i>
                My Members
            </h1>
            <p class="header-subtitle">Members enrolled in your workout plans</p>
        </div>

        <?php 
        $total_members = $result ? $result->num_rows : 0;
        $approved = $pending = $rejected = 0;
        if ($result) {
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                if ($row['status'] === 'Approved') $approved++;
                elseif ($row['status'] === 'Pending') $pending++;
                elseif ($row['status'] === 'Rejected') $rejected++;
            }
            $result->data_seek(0);
        }
        ?>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
                <div class="stat-number text-primary"><?= $total_members ?></div>
                <div class="stat-label">Total Members</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number text-success"><?= $approved ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon text-warning"><i class="fas fa-clock"></i></div>
                <div class="stat-number text-warning"><?= $pending ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon text-danger"><i class="fas fa-times-circle"></i></div>
                <div class="stat-number text-danger"><?= $rejected ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="members-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="member-card">
                        <div class="member-header">
                            <div>
                                <h3 class="member-name"><?= htmlspecialchars($row['name']) ?></h3>
                                <div class="member-contact">
                                    <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($row['email']) ?><br>
                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($row['phone']) ?>
                                </div>
                            </div>
                            <div class="member-id">#<?= $row['membership_id'] ?></div>
                        </div>
                        
                        <div class="member-info">
                            <div class="info-item">
                                <i class="fas fa-dumbbell info-icon"></i>
                                <span class="info-label">Plan:</span>
                                <span class="info-value"><?= htmlspecialchars($row['plan_title']) ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-calendar-alt info-icon"></i>
                                <span class="info-label">Duration:</span>
                                <span class="info-value"><?= htmlspecialchars($row['duration_weeks']) ?> months</span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-rupee-sign info-icon"></i>
                                <span class="info-label">Price:</span>
                                <span class="price-badge">₹<?= number_format($row['price'], 2) ?></span>
                            </div>
                        </div>
                        
                        <div class="status-section">
                            <form method="POST" class="status-form">
                                <input type="hidden" name="membership_id" value="<?= $row['membership_id'] ?>">
                                <select name="status" class="form-select" required>
                                    <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Approved" <?= $row['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn-update">Update</button>
                            </form>
                        </div>
                        
                        <?php if ($row['diet_status']): ?>
                            <div class="diet-section">
                                <div class="diet-title">
                                    <i class="fas fa-utensils"></i>
                                    Diet Plan Request
                                </div>
                                <div class="diet-status">
                                    <span class="status-badge status-<?= strtolower($row['diet_status']) ?>">
                                        <?= $row['diet_status'] ?>
                                    </span>
                                    <?php if ($row['request_id'] && $row['payment_status'] !== 'Paid'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                            <input type="hidden" name="payment_status" value="Paid">
                                            <button type="submit" class="btn-paid">Mark Paid</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="booking-date">
                            <i class="fas fa-calendar me-1"></i>
                            Booked on <?= date('d M Y', strtotime($row['booking_date'])) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h3>No Members Found</h3>
                <p>No members have enrolled in your workout plans yet.</p>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="trainer_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>