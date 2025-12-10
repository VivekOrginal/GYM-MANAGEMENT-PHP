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

$plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;
$user_id = $_SESSION['user_id'];

if (!$plan_id) {
    echo "<h4 class='text-danger text-center mt-5'>❌ Invalid Plan ID</h4>";
    exit;
}

$planStmt = $conn->prepare("SELECT * FROM trainer_workout_plans WHERE plan_id = ?");
$planStmt->bind_param("i", $plan_id);
$planStmt->execute();
$planResult = $planStmt->get_result();
$plan = $planResult->fetch_assoc();

if (!$plan) {
    echo "<h4 class='text-danger text-center mt-5'>❌ Plan not found</h4>";
    exit;
}

$trainer_id = $plan['trainer_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $phone = $_POST['phone'];
    $goals = $_POST['goals'];

    $insertStmt = $conn->prepare("INSERT INTO memberships (user_id, plan_id, trainer_id, full_name, age, weight, phone, goals, booking_date, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')");
    $insertStmt->bind_param("iiisidss", $user_id, $plan_id, $trainer_id, $full_name, $age, $weight, $phone, $goals);

    if ($insertStmt->execute()) {
        $message = "✅ You have successfully booked the plan: " . htmlspecialchars($plan['plan_title']);
    } else {
        $message = "❌ Failed to book the plan. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Membership - FitZone Pro</title>
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

        .booking-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            background: var(--dark-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-dark);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .success-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 2rem;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2rem;
            color: white;
        }

        .success-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 0.75rem 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
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

            .booking-card {
                padding: 1.5rem;
            }

            .action-buttons {
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
            <a href="search_gyms.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Plans
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
            <div class="page-header">
                <h1>Booking Confirmed</h1>
                <p>Your membership booking has been processed</p>
            </div>
            
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="success-message">
                    <?= str_replace(['✅', '❌'], '', $message) ?>
                </div>
                <div class="action-buttons">
                    <a href="search_gyms.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Plans
                    </a>
                    <a href="member_dashboard.php" class="back-btn">
                        <i class="fas fa-home"></i>
                        Go to Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="page-header">
                <h1>Book Membership</h1>
                <p>Complete your booking for: <?= htmlspecialchars($plan['plan_title']) ?></p>
            </div>
            
            <div class="booking-card">
                <form method="post">
                    <div class="form-group">
                        <label for="full_name" class="form-label">
                            <i class="fas fa-user" style="margin-right: 0.5rem;"></i>Full Name
                        </label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="age" class="form-label">
                            <i class="fas fa-birthday-cake" style="margin-right: 0.5rem;"></i>Age
                        </label>
                        <input type="number" name="age" class="form-control" placeholder="Enter your age" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="weight" class="form-label">
                            <i class="fas fa-weight" style="margin-right: 0.5rem;"></i>Weight (kg)
                        </label>
                        <input type="number" step="0.1" name="weight" class="form-control" placeholder="Enter your weight" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone" style="margin-right: 0.5rem;"></i>Phone Number
                        </label>
                        <input type="tel" name="phone" class="form-control" placeholder="Enter your phone number" required pattern="[0-9]{10}">
                    </div>
                    
                    <div class="form-group">
                        <label for="goals" class="form-label">
                            <i class="fas fa-target" style="margin-right: 0.5rem;"></i>Fitness Goals
                        </label>
                        <textarea name="goals" class="form-control" rows="3" placeholder="Describe your fitness goals..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-calendar-check"></i>
                        Confirm Booking
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
