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

$trainer_id = isset($_GET['trainer_id']) ? intval($_GET['trainer_id']) : 0;
if (!$trainer_id) {
    echo "<h4 class='text-center text-danger mt-5'>❌ Invalid Trainer ID</h4>";
    exit;
}

// Fetch trainer info
$trainerStmt = $conn->prepare("SELECT * FROM gym_trainers WHERE trainer_id = ?");
$trainerStmt->bind_param("i", $trainer_id);
$trainerStmt->execute();
$trainerResult = $trainerStmt->get_result();
$trainer = $trainerResult->fetch_assoc();

if (!$trainer) {
    echo "<h4 class='text-center text-danger mt-5'>❌ Trainer not found</h4>";
    exit;
}

// Fetch plans
$planStmt = $conn->prepare("SELECT * FROM trainer_workout_plans WHERE trainer_id = ?");
$planStmt->bind_param("i", $trainer_id);
$planStmt->execute();
$plans = $planStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Plans - FitZone Pro</title>
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

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .plan-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 2rem;
            transition: var(--transition);
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .plan-header {
            margin-bottom: 1.5rem;
        }

        .plan-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .plan-details {
            margin-bottom: 1.5rem;
        }

        .plan-detail {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .plan-detail i {
            color: var(--primary-color);
            margin-top: 0.25rem;
            min-width: 16px;
        }

        .plan-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .video-container {
            margin: 1.5rem 0;
        }

        .video-container video {
            width: 100%;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .video-placeholder {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }

        .btn-book {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        .btn-booked {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
            cursor: not-allowed;
        }

        .no-plans {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
        }

        .no-plans i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .plans-grid {
                grid-template-columns: 1fr;
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
            <a href="search_gyms.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Gym Search
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1><?= htmlspecialchars($trainer['name'] ?? 'Trainer') ?> Plans</h1>
            <p>Choose from available membership plans and workout programs</p>
        </div>

        <?php if ($plans->num_rows > 0): ?>
            <div class="plans-grid">
                <?php while ($plan = $plans->fetch_assoc()): ?>
                    <div class="plan-card">
                        <div class="plan-header">
                            <h3 class="plan-title"><?= htmlspecialchars($plan['plan_title']) ?></h3>
                        </div>

                        <div class="plan-details">
                            <div class="plan-detail">
                                <i class="fas fa-calendar-alt"></i>
                                <span><strong>Duration:</strong> <?= htmlspecialchars($plan['duration_weeks']) ?> weeks</span>
                            </div>
                            <div class="plan-detail">
                                <i class="fas fa-dumbbell"></i>
                                <div>
                                    <strong>Workout Schedule:</strong><br>
                                    <?= nl2br(htmlspecialchars($plan['workout_schedule'])) ?>
                                </div>
                            </div>
                        </div>

                        <div class="plan-price">
                            <i class="fas fa-rupee-sign"></i>
                            <?= number_format($plan['price'], 2) ?> / month
                        </div>

                        <!-- Display Video if exists -->
                        <div class="video-container">
                            <?php 
                            if (!empty($plan['gym_video'])) {
                                $videoPath = $plan['gym_video'];
                                if (file_exists($videoPath)): ?>
                                    <video controls>
                                        <source src="<?= $videoPath ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else: ?>
                                    <div class="video-placeholder">
                                        <i class="fas fa-video"></i>
                                        <p>Video file not found</p>
                                    </div>
                                <?php endif;
                            } else { ?>
                                <div class="video-placeholder">
                                    <i class="fas fa-video"></i>
                                    <p>No video uploaded</p>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Check if user already booked this plan -->
                        <?php
                        $checkStmt = $conn->prepare("SELECT * FROM memberships WHERE user_id = ? AND plan_id = ?");
                        $checkStmt->bind_param("ii", $_SESSION['user_id'], $plan['plan_id']);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        $isAlreadyBooked = $checkResult->num_rows > 0;
                        ?>

                        <?php if ($isAlreadyBooked): ?>
                            <button class="btn-booked" disabled>
                                <i class="fas fa-check"></i>
                                Already Booked
                            </button>
                        <?php else: ?>
                            <a href="book_membership.php?plan_id=<?= $plan['plan_id'] ?>" class="btn-book">
                                <i class="fas fa-credit-card"></i>
                                Book This Plan
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-plans">
                <i class="fas fa-dumbbell"></i>
                <h3>No Plans Available</h3>
                <p>No membership plans are available for this trainer at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>