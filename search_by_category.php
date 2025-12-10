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

$searchCategory = "";
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchCategory = trim($_POST['category']);
    $stmt = $conn->prepare("SELECT * FROM gym_trainers WHERE category = ? AND status = 'Approved'");
    $stmt->bind_param("s", $searchCategory);
    $stmt->execute();
    $results = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM gym_trainers WHERE status = 'Approved'");
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Gyms by Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            background: #fff;
            padding: 20px;
        }
        img.gym-img {
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #ddd;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center mb-4 text-success">🏋️ Search Gyms by Trainer Category</h3>

    <!-- Search Form -->
    <form method="POST" class="row justify-content-center mb-4">
        <div class="col-md-6">
            <select name="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="Cardio" <?= $searchCategory == 'Cardio' ? 'selected' : '' ?>>Cardio</option>
                <option value="Weight Training" <?= $searchCategory == 'Weight Training' ? 'selected' : '' ?>>Weight Training</option>
                <option value="Yoga" <?= $searchCategory == 'Yoga' ? 'selected' : '' ?>>Yoga</option>
                <option value="Crossfit" <?= $searchCategory == 'Crossfit' ? 'selected' : '' ?>>Crossfit</option>
                <option value="Zumba" <?= $searchCategory == 'Zumba' ? 'selected' : '' ?>>Zumba</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <!-- Results Section -->
    <div class="row">
        <?php if ($results && $results->num_rows > 0): ?>
            <?php while ($row = $results->fetch_assoc()): ?>
                <div class="col-md-6">
                    <div class="card">
                        <h4 class="text-primary"><?= htmlspecialchars($row['gym_name']) ?> - <?= htmlspecialchars($row['place']) ?></h4>
                        <p><strong>Trainer Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($row['category']) ?></p>
                        <p><strong>License:</strong> <?= htmlspecialchars($row['licence_number']) ?></p>
                        <p><strong>GST Number:</strong> <?= htmlspecialchars($row['gst_number']) ?></p>
                        <p><strong>Contact:</strong> <?= nl2br(htmlspecialchars($row['gym_contact'])) ?></p>
                        <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($row['address'])) ?></p>
                        <p><strong>Gym Location:</strong> <?= nl2br(htmlspecialchars($row['gym_location'])) ?></p>

                        <?php
                        $imageFile = $row['gym_image'];
                        $imagePath = '' . $imageFile;
                        $serverPath = __DIR__ . '/' . $imagePath;
                        $modalId = "gymModal" . $row['trainer_id'];
                        ?>

                        <?php if (!empty($imageFile) && file_exists($serverPath)): ?>
                            <img src="<?= $imagePath ?>" class="img-fluid gym-img mt-3" alt="Gym Image"
                                 data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                            <a href="view_gym_plans.php?trainer_id=<?= $row['trainer_id'] ?>" class="btn btn-success mt-3">
                                💪 View Workout Plans
                            </a>
                            <!-- Modal -->
                            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-body text-center">
                                            <img src="<?= $imagePath ?>" class="img-fluid" alt="Full Gym Image">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mt-2">❌ No image available or file missing.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">
                ❌ No gyms found <?= $searchCategory ? 'for category: <strong>' . htmlspecialchars($searchCategory) . '</strong>' : '' ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="text-center mt-4">
        <a href="member_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
