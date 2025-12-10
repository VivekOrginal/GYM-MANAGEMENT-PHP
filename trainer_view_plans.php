<?php
session_start();
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$trainer_id = $_SESSION['trainer_id'];

// Delete plan
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM trainer_workout_plans WHERE plan_id = ? AND trainer_id = ?");
    $stmt->bind_param("ii", $delete_id, $trainer_id);
    $stmt->execute();
    $stmt->close();
    header("Location: trainer_view_plans.php");
    exit;
}

// Fetch workout plans
$query = "
    SELECT t.plan_id, t.plan_title, t.goal, t.duration_weeks, t.price, t.workout_schedule, t.gym_video, c.name AS category_name
    FROM trainer_workout_plans t
    JOIN categories c ON t.category_id = c.category_id
    WHERE t.trainer_id = $trainer_id
    ORDER BY t.plan_id DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Workout Plans</title>
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
            max-width: 1200px;
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
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .add-btn {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
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
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .plan-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .plan-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .plan-id {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .plan-info {
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
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .schedule-section {
            margin: 1.5rem 0;
        }
        
        .schedule-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .schedule-content {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            color: #6c757d;
            line-height: 1.6;
            white-space: pre-line;
        }
        
        .video-section {
            margin: 1.5rem 0;
        }
        
        .video-container {
            position: relative;
            padding-top: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 10px;
            background: #000;
        }
        
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .no-video {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            color: #6c757d;
            border: 2px dashed #dee2e6;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            flex: 1;
            justify-content: center;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            flex: 1;
            justify-content: center;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
            color: white;
            text-decoration: none;
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
            .plans-grid {
                grid-template-columns: 1fr;
            }
            
            .header-title {
                font-size: 1.5rem;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-card">
            <h1 class="header-title">
                <i class="fas fa-dumbbell me-2"></i>
                My Workout Plans
            </h1>
            <p class="header-subtitle">Manage and view all your created workout plans</p>
        </div>

        <div class="action-bar">
            <div>
                <h4 class="mb-0">Total Plans: <?= $result ? $result->num_rows : 0 ?></h4>
            </div>
            <a href="trainer_add_plan.php" class="add-btn">
                <i class="fas fa-plus"></i>
                Add New Plan
            </a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="plans-grid">
                <?php while ($plan = $result->fetch_assoc()): ?>
                    <div class="plan-card">
                        <div class="plan-header">
                            <h3 class="plan-title"><?= htmlspecialchars($plan['plan_title']) ?></h3>
                            <div class="plan-id">#<?= $plan['plan_id'] ?></div>
                        </div>
                        
                        <div class="plan-info">
                            <div class="info-item">
                                <i class="fas fa-tag info-icon"></i>
                                <span class="info-label">Category:</span>
                                <span class="info-value"><?= htmlspecialchars($plan['category_name']) ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-target info-icon"></i>
                                <span class="info-label">Goal:</span>
                                <span class="info-value"><?= htmlspecialchars($plan['goal']) ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-calendar-alt info-icon"></i>
                                <span class="info-label">Duration:</span>
                                <span class="info-value"><?= $plan['duration_weeks'] ?> months</span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-rupee-sign info-icon"></i>
                                <span class="info-label">Price:</span>
                                <span class="price-badge">₹<?= number_format($plan['price'], 2) ?>/month</span>
                            </div>
                        </div>
                        
                        <div class="schedule-section">
                            <div class="schedule-title">
                                <i class="fas fa-list"></i>
                                Workout Schedule
                            </div>
                            <div class="schedule-content"><?= htmlspecialchars($plan['workout_schedule']) ?></div>
                        </div>
                        
                        <div class="video-section">
                            <?php if (!empty($plan['gym_video'])): ?>
                                <div class="video-container">
                                    <video controls>
                                        <source src="<?= htmlspecialchars($plan['gym_video']) ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php else: ?>
                                <div class="no-video">
                                    <i class="fas fa-video fa-2x mb-2"></i>
                                    <div>No video uploaded</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="trainer_edit_plan.php?id=<?= $plan['plan_id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i>
                                Edit Plan
                            </a>
                            <a href="?delete=<?= $plan['plan_id'] ?>" class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this plan?')">
                                <i class="fas fa-trash"></i>
                                Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h3>No Workout Plans Found</h3>
                <p>You haven't created any workout plans yet. Start by adding your first plan.</p>
                <a href="trainer_add_plan.php" class="add-btn mt-3">
                    <i class="fas fa-plus"></i>
                    Add First Plan
                </a>
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