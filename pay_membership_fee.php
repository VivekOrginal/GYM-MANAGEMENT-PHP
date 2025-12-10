<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['membership_id'])) {
    echo "<h4>❗ Invalid request</h4>";
    exit;
}

$membership_id = intval($_GET['membership_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT m.*, p.plan_title, p.price
    FROM memberships m 
    JOIN trainer_workout_plans p ON m.plan_id = p.plan_id 
    WHERE m.membership_id = ? AND m.user_id = ?
");
$stmt->bind_param("ii", $membership_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h4>❌ No such booking found.</h4>";
    exit;
}

$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $conn->prepare("UPDATE memberships SET payment_status = 'Paid' WHERE membership_id = ?");
    $update->bind_param("i", $membership_id);
    $update->execute();

    echo "<script>alert('✅ Payment successful!'); window.location.href='my_bookings.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Membership Fee - FitZone Pro</title>
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
            max-width: 800px;
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

        .payment-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-medium);
        }

        .plan-details {
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .detail-value {
            color: var(--text-dark);
            font-weight: 600;
        }

        .price-value {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .status-paid {
            color: #28a745;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pending {
            color: #ffc107;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-pay {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            justify-content: center;
            cursor: pointer;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 1rem 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-dark);
            text-decoration: none;
        }

        .alert {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 1rem 15px;
            }

            .payment-card {
                padding: 2rem;
            }

            .payment-actions {
                flex-direction: column;
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
            <a href="my_bookings.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Bookings
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Payment</h1>
            <p>Complete your membership payment</p>
        </div>

        <div class="payment-card">
            <div class="plan-details">
                <div class="detail-item">
                    <span class="detail-label">
                        <i class="fas fa-dumbbell"></i>
                        Plan
                    </span>
                    <span class="detail-value"><?= htmlspecialchars($row['plan_title']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">
                        <i class="fas fa-rupee-sign"></i>
                        Price
                    </span>
                    <span class="price-value"><?= number_format($row['price'], 2) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">
                        <i class="fas fa-info-circle"></i>
                        Status
                    </span>
                    <span class="<?= $row['payment_status'] == 'Paid' ? 'status-paid' : 'status-pending' ?>">
                        <?php if ($row['payment_status'] == 'Paid'): ?>
                            <i class="fas fa-check-circle"></i>
                            Already Paid
                        <?php else: ?>
                            <i class="fas fa-clock"></i>
                            Pending Payment
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <?php if ($row['payment_status'] != 'Paid'): ?>
                <form method="POST">
                    <div class="payment-actions">
                        <button type="submit" class="btn-pay">
                            <i class="fas fa-credit-card"></i>
                            Pay ₹<?= number_format($row['price'], 2) ?>
                        </button>
                        <a href="my_bookings.php" class="btn-cancel">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert">
                    <i class="fas fa-check-circle"></i>
                    <span>You have already paid for this plan. Thank you!</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
