<?php
session_start();
if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = $error = "";

// Get plan ID
if (!isset($_GET['id'])) {
    die("⚠️ Plan ID not provided.");
}

$plan_id = intval($_GET['id']);
$trainer_id = $_SESSION['trainer_id'];

// Fetch categories
$categories = $conn->query("SELECT category_id, name FROM categories");

// Fetch existing plan details
$stmt = $conn->prepare("SELECT * FROM trainer_workout_plans WHERE plan_id = ? AND trainer_id = ?");
$stmt->bind_param("ii", $plan_id, $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows != 1) {
    die("⚠️ Plan not found or access denied.");
}
$plan = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plan_title = trim($_POST['plan_title']);
    $goal = trim($_POST['goal']);
    $duration_weeks = $_POST['duration_weeks'];
    $workout_schedule = trim($_POST['workout_schedule']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];

    // Handle optional video upload
    $video_path = $plan['gym_video'];
    if (!empty($_FILES['gym_video']['name'])) {
        $video_name = basename($_FILES["gym_video"]["name"]);
        $target_dir = "uploads/videos/";
        $target_file = $target_dir . time() . "_" . $video_name;

        if (move_uploaded_file($_FILES["gym_video"]["tmp_name"], $target_file)) {
            $video_path = $target_file;
        } else {
            $error = "❌ Video upload failed.";
        }
    }

    if ($plan_title && $goal && $duration_weeks && $workout_schedule && $category_id && !$error) {
        $stmt = $conn->prepare("UPDATE trainer_workout_plans SET plan_title=?, goal=?, duration_weeks=?, workout_schedule=?, category_id=?, gym_video=?, price=? WHERE plan_id=? AND trainer_id=?");
        $stmt->bind_param("ssisssdii", $plan_title, $goal, $duration_weeks, $workout_schedule, $category_id, $video_path, $price, $plan_id, $trainer_id);

        if ($stmt->execute()) {
            $success = "✅ Workout plan updated successfully!";
            // Refresh plan details
            $plan = array_merge($plan, [
                'plan_title' => $plan_title,
                'goal' => $goal,
                'duration_weeks' => $duration_weeks,
                'workout_schedule' => $workout_schedule,
                'category_id' => $category_id,
                'gym_video' => $video_path,
                'price' => $price
            ]);
        } else {
            $error = "❌ Failed to update workout plan.";
        }
        $stmt->close();
    } else {
        $error = "⚠️ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Workout Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('gym-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 650px;
            margin-top: 40px;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        h3 {
            font-weight: bold;
            color: #0d6efd;
        }
        label {
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center mb-4">✏️ Edit Workout Plan</h3>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Plan Title</label>
            <input type="text" name="plan_title" class="form-control" required value="<?= htmlspecialchars($plan['plan_title']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Goal</label>
            <input type="text" name="goal" class="form-control" required value="<?= htmlspecialchars($plan['goal']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Duration (weeks)</label>
            <input type="number" name="duration_weeks" class="form-control" required value="<?= $plan['duration_weeks'] ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Workout Schedule</label>
            <textarea name="workout_schedule" class="form-control" rows="6" required><?= htmlspecialchars($plan['workout_schedule']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Workout Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $plan['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Price (₹)/month</label>
            <input type="number" name="price" class="form-control" step="0.01" required value="<?= htmlspecialchars($plan['price'] ?? '') ?>" placeholder="e.g. 499.00">
        </div>

        <div class="mb-3">
            <label class="form-label">Update Video (optional)</label>
            <input type="file" name="gym_video" class="form-control" accept="video/*">
            <?php if (!empty($plan['gym_video'])): ?>
                <div class="mt-2">
                    <small>Current video:</small><br>
                    <video width="100%" controls>
                        <source src="<?= htmlspecialchars($plan['gym_video']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">💾 Update Plan</button>
        <a href="trainer_view_plans.php" class="btn btn-secondary">← Back</a>
    </form>
</div>
</body>
</html>
