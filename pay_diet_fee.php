<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['request_id'])) {
    echo "<h4>❗ Invalid request.</h4>";
    exit;
}

$request_id = intval($_GET['request_id']);
$user_id = $_SESSION['user_id'];

// Fetch request and diet info
$stmt = $conn->prepare("
    SELECT dr.*, dp.title, dp.fee, t.name AS trainer_name 
    FROM diet_requests dr 
    JOIN diet_plans dp ON dr.diet_id = dp.diet_id 
    JOIN gym_trainers t ON dp.trainer_id = t.trainer_id 
    WHERE dr.request_id = ? AND dr.user_id = ?
");
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h4>❌ No such diet request found for you.</h4>";
    exit;
}

$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate successful payment
    $update = $conn->prepare("UPDATE diet_requests SET payment_status = 'Paid' WHERE request_id = ?");
    $update->bind_param("i", $request_id);
    $update->execute();

    echo "<script>alert('✅ Payment successful!'); window.location.href='member_view_diet.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Diet Fee - FitZone Pro</title>
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
            max-width: 600px;
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
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .diet-info {
            margin-bottom: 2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-weight: 600;
            color: var(--text-dark);
        }

        .fee-amount {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .status-paid {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .alert {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            color: #22c55e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-pay {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
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
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-dark);
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 1rem 15px;
            }

            .payment-actions {
                flex-direction: column;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
            <a href="member_view_diet.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Diet Plans
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Payment</h1>
            <p>Complete your diet plan payment</p>
        </div>

        <div class="payment-card">
            <div class="diet-info">
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-utensils"></i>
                        Diet Plan
                    </div>
                    <div class="info-value"><?= htmlspecialchars($row['title']) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user-tie"></i>
                        Trainer
                    </div>
                    <div class="info-value"><?= htmlspecialchars($row['trainer_name']) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-rupee-sign"></i>
                        Fee
                    </div>
                    <div class="info-value fee-amount">₹<?= number_format($row['fee'], 2) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-flag"></i>
                        Status
                    </div>
                    <div class="info-value">
                        <?php if ($row['payment_status'] === 'Paid'): ?>
                            <span class="status-paid">
                                <i class="fas fa-check"></i> Paid
                            </span>
                        <?php else: ?>
                            <span class="status-pending">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($row['payment_status'] === 'Paid'): ?>
                <div class="alert">
                    <i class="fas fa-check-circle"></i>
                    You have already paid for this diet plan.
                </div>
                <div class="payment-actions">
                    <a href="member_view_diet.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to Diet Plans
                    </a>
                </div>
            <?php else: ?>
                <div class="payment-actions">
                    <form method="POST" style="display: inline;">
                        <button type="submit" class="btn-pay">
                            <i class="fas fa-credit-card"></i>
                            Pay ₹<?= number_format($row['fee'], 2) ?> Now
                        </button>
                    </form>
                    <a href="member_view_diet.php" class="btn-cancel">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
