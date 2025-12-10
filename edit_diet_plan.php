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
$diet_id = $_GET['id'] ?? null;

if (!$diet_id) {
    header("Location: view_diet_plans.php");
    exit;
}

// Fetch plan
$stmt = $conn->prepare("SELECT * FROM diet_plans WHERE diet_id = ? AND trainer_id = ?");
$stmt->bind_param("ii", $diet_id, $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$diet = $result->fetch_assoc();

if (!$diet) {
    die("Plan not found or access denied.");
}

// Update logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $diet_file = $diet['diet_file']; // keep old if not changed

    if (isset($_FILES['diet_file']) && $_FILES['diet_file']['error'] == 0) {
        $target_dir = "uploads/diet_plans/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $diet_file = $target_dir . basename($_FILES['diet_file']['name']);
        move_uploaded_file($_FILES['diet_file']['tmp_name'], $diet_file);
    }

    $update = $conn->prepare("UPDATE diet_plans SET title = ?, description = ?, diet_file = ? WHERE diet_id = ? AND trainer_id = ?");
    $update->bind_param("sssii", $title, $description, $diet_file, $diet_id, $trainer_id);
    $update->execute();

    header("Location: view_diet_plans.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Diet Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="text-warning text-center">✏️ Edit Diet Plan</h3>

    <form method="POST" enctype="multipart/form-data" class="shadow p-4 bg-light rounded">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($diet['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($diet['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Replace File (optional)</label>
            <input type="file" name="diet_file" class="form-control">
            <?php if ($diet['diet_file']): ?>
                <p class="mt-2">Current: <a href="<?= $diet['diet_file'] ?>" target="_blank">View File</a></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">💾 Update</button>
        <a href="view_diet_plans.php" class="btn btn-secondary">← Cancel</a>
    </form>
</div>
</body>
</html>
