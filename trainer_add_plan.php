<?php
session_start();
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = $error = "";

// Fetch all categories
$categories = $conn->query("SELECT category_id, name FROM categories");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plan_title = trim($_POST['plan_title']);
    $goal = trim($_POST['goal']);
    $duration_weeks = $_POST['duration_weeks'];
    $workout_schedule = trim($_POST['workout_schedule']);
    $category_id = $_POST['category_id'];
    $trainer_id = $_SESSION['trainer_id'];
    $price = floatval($_POST['price']);
    $gym_video_path = "";

    // Handle video upload
    if (isset($_FILES['gym_video']) && $_FILES['gym_video']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/videos/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $video_name = basename($_FILES["gym_video"]["name"]);
        $video_tmp = $_FILES["gym_video"]["tmp_name"];
        $target_file = $target_dir . time() . "_" . $video_name;

        if (move_uploaded_file($video_tmp, $target_file)) {
            $gym_video_path = $target_file;
        }
    }

    if ($plan_title && $goal && $duration_weeks && $workout_schedule && $category_id) {
        $stmt = $conn->prepare("INSERT INTO trainer_workout_plans (trainer_id, plan_title, goal, duration_weeks, workout_schedule, category_id, price, gym_video) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issisiis", $trainer_id, $plan_title, $goal, $duration_weeks, $workout_schedule, $category_id,$price, $gym_video_path);
        if ($stmt->execute()) {
            $success = "Workout plan added successfully!";
        } else {
            $error = "Failed to add workout plan.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Workout Plan</title>
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
            max-width: 800px;
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
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }
        
        .form-control::placeholder {
            color: #adb5bd;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 2rem;
            border: 2px dashed #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-upload:hover .file-upload-label {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
            color: #667eea;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .header-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-card">
            <h1 class="header-title">
                <i class="fas fa-plus-circle me-2"></i>
                Add Workout Plan
            </h1>
            <p class="header-subtitle">Create a new workout plan with video content</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="plan_title" class="form-label">
                            <i class="fas fa-dumbbell"></i>
                            Plan Title
                        </label>
                        <input type="text" name="plan_title" class="form-control" required 
                               placeholder="e.g. Beginner Full Body Workout">
                    </div>

                    <div class="form-group">
                        <label for="goal" class="form-label">
                            <i class="fas fa-target"></i>
                            Goal
                        </label>
                        <input type="text" name="goal" class="form-control" required 
                               placeholder="e.g. Fat Loss, Strength Gain">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration_weeks" class="form-label">
                            <i class="fas fa-calendar-alt"></i>
                            Duration (months)
                        </label>
                        <input type="number" name="duration_weeks" class="form-control" required min="1" max="12">
                    </div>

                    <div class="form-group">
                        <label for="price" class="form-label">
                            <i class="fas fa-rupee-sign"></i>
                            Price per month
                        </label>
                        <input type="number" name="price" class="form-control" step="0.01" required 
                               placeholder="e.g. 499.00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="category_id" class="form-label">
                        <i class="fas fa-tags"></i>
                        Workout Category
                    </label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="workout_schedule" class="form-label">
                        <i class="fas fa-list"></i>
                        Workout Schedule
                    </label>
                    <textarea name="workout_schedule" class="form-control" required 
                              placeholder="e.g. Monday: Chest & Triceps&#10;Tuesday: Back & Biceps&#10;Wednesday: Rest Day&#10;Thursday: Legs & Shoulders&#10;Friday: Arms & Core&#10;Weekend: Cardio"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-video"></i>
                        Upload Gym Video (MP4 only)
                    </label>
                    <div class="file-upload">
                        <input type="file" name="gym_video" accept="video/mp4">
                        <div class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                            <div>
                                <div>Click to upload video</div>
                                <small>MP4 format only</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Workout Plan
                    </button>
                    <a href="trainer_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // File upload feedback
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const label = document.querySelector('.file-upload-label');
            if (e.target.files.length > 0) {
                label.innerHTML = `
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                    <div>
                        <div>File selected: ${e.target.files[0].name}</div>
                        <small>Click to change</small>
                    </div>
                `;
            }
        });

        // Form submission loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Plan...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>