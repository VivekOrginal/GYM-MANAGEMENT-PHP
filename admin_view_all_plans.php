<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $workout_query = "SELECT wp.*, t.name AS trainer_name 
                      FROM trainer_workout_plans wp 
                      JOIN gym_trainers t ON wp.trainer_id = t.trainer_id
                      WHERE (wp.plan_title LIKE ? OR t.name LIKE ?)
                      ORDER BY wp.created_at DESC";
    $stmt = $conn->prepare($workout_query);
    $likeSearch = "%$search%";
    $stmt->bind_param("ss", $likeSearch, $likeSearch);
    $stmt->execute();
    $workout_result = $stmt->get_result();
    
    $diet_query = "SELECT dp.*, t.name AS trainer_name 
                   FROM diet_plans dp
                   JOIN gym_trainers t ON dp.trainer_id = t.trainer_id
                   WHERE (dp.title LIKE ? OR t.name LIKE ?)
                   ORDER BY dp.created_at DESC";
    $stmt2 = $conn->prepare($diet_query);
    $stmt2->bind_param("ss", $likeSearch, $likeSearch);
    $stmt2->execute();
    $diet_result = $stmt2->get_result();
} else {
    $workout_query = "SELECT wp.*, t.name AS trainer_name 
                      FROM trainer_workout_plans wp 
                      JOIN gym_trainers t ON wp.trainer_id = t.trainer_id
                      ORDER BY wp.created_at DESC";
    $workout_result = $conn->query($workout_query);
    
    $diet_query = "SELECT dp.*, t.name AS trainer_name 
                   FROM diet_plans dp
                   JOIN gym_trainers t ON dp.trainer_id = t.trainer_id
                   ORDER BY dp.created_at DESC";
    $diet_result = $conn->query($diet_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Plans - FitZone Pro</title>
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

        .search-section {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 1rem;
            background: var(--dark-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-dark);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.1);
        }

        .search-input::placeholder {
            color: var(--text-light);
        }

        .search-btn {
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
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .clear-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .plans-section {
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

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .plan-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            transition: var(--transition);
        }

        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .plan-id {
            background: var(--gradient-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .plan-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 0.5rem 0;
        }

        .plan-trainer {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .plan-content {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .plan-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .plan-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .plan-fee {
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 1rem 15px;
            }

            .plans-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .stats-section {
                grid-template-columns: repeat(2, 1fr);
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
            <h1>All Training Plans</h1>
            <p>Complete overview of workout and diet plans</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="get" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search by plan title or trainer name..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <a href="admin_view_all_plans.php" class="search-btn clear-btn">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </form>
        </div>

        <?php 
        $workout_count = $workout_result ? $workout_result->num_rows : 0;
        $diet_count = $diet_result ? $diet_result->num_rows : 0;
        $total_plans = $workout_count + $diet_count;
        ?>
        
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-number"><?= $total_plans ?></div>
                <div class="stat-label">Total Plans</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #ff6b7a;"><i class="fas fa-dumbbell"></i></div>
                <div class="stat-number" style="color: #ff6b7a;"><?= $workout_count ?></div>
                <div class="stat-label">Workout Plans</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #22c55e;"><i class="fas fa-utensils"></i></div>
                <div class="stat-number" style="color: #22c55e;"><?= $diet_count ?></div>
                <div class="stat-label">Diet Plans</div>
            </div>
        </div>

        <!-- Workout Plans Section -->
        <div class="plans-section">
            <div class="section-header">
                <i class="fas fa-dumbbell"></i>
                <h2 class="section-title">Workout Plans (<?= $workout_count ?>)</h2>
            </div>
            
            <?php if ($workout_result && $workout_result->num_rows > 0): ?>
                <div class="plans-grid">
                    <?php while($row = $workout_result->fetch_assoc()): ?>
                        <div class="plan-card">
                            <div class="plan-header">
                                <div>
                                    <h3 class="plan-title"><?= htmlspecialchars($row['plan_title']) ?></h3>
                                    <div class="plan-trainer">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?= htmlspecialchars($row['trainer_name']) ?></span>
                                    </div>
                                </div>
                                <div class="plan-id">#<?= $row['plan_id'] ?></div>
                            </div>
                            
                            <div class="plan-content">
                                <strong>Goal:</strong><br>
                                <?= nl2br(htmlspecialchars($row['goal'])) ?>
                            </div>
                            
                            <div class="plan-meta">
                                <div class="plan-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h4>No Workout Plans</h4>
                    <p><?= $search ? "No workout plans found for '<strong>$search</strong>'" : 'No workout plans have been created yet.' ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Diet Plans Section -->
        <div class="plans-section">
            <div class="section-header">
                <i class="fas fa-utensils"></i>
                <h2 class="section-title">Diet Plans (<?= $diet_count ?>)</h2>
            </div>
            
            <?php if ($diet_result && $diet_result->num_rows > 0): ?>
                <div class="plans-grid">
                    <?php while($row = $diet_result->fetch_assoc()): ?>
                        <div class="plan-card">
                            <div class="plan-header">
                                <div>
                                    <h3 class="plan-title"><?= htmlspecialchars($row['title']) ?></h3>
                                    <div class="plan-trainer">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?= htmlspecialchars($row['trainer_name']) ?></span>
                                    </div>
                                </div>
                                <div class="plan-id">#<?= $row['diet_id'] ?></div>
                            </div>
                            
                            <div class="plan-content">
                                <?= nl2br(htmlspecialchars($row['description'])) ?>
                            </div>
                            
                            <div class="plan-meta">
                                <div class="plan-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                                </div>
                                <div class="plan-fee">
                                    ₹<?= number_format($row['fee'], 2) ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h4>No Diet Plans</h4>
                    <p><?= $search ? "No diet plans found for '<strong>$search</strong>'" : 'No diet plans have been created yet.' ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
