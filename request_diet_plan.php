<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// ✅ Check active membership
$checkStatus = $conn->prepare("SELECT is_active FROM memberships WHERE user_id = ?");
$checkStatus->bind_param("i", $user_id);
$checkStatus->execute();
$statusResult = $checkStatus->get_result();

if ($statusResult->num_rows > 0) {
    $row = $statusResult->fetch_assoc();
    if (strtolower($row['is_active']) !== 'yes') {
        echo "<h4 class='text-center text-danger mt-4'>❌ You must be an active member to request diet plans.</h4>";
        exit;
    }
} else {
    echo "<h4 class='text-center text-danger mt-4'>❌ Membership status not found.</h4>";
    exit;
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['diet_id'], $_POST['trainer_id'])) {
    $diet_id = $_POST['diet_id'];
    $trainer_id = $_POST['trainer_id'];

    $check = $conn->prepare("SELECT * FROM diet_requests WHERE user_id = ? AND diet_id = ?");
    $check->bind_param("ii", $user_id, $diet_id);
    $check->execute();
    $exists = $check->get_result();

    if ($exists->num_rows == 0) {
        // 🟡 Add payment logic here before inserting request
        // For now, we proceed without payment gateway.

        $stmt = $conn->prepare("INSERT INTO diet_requests (user_id, trainer_id, diet_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $trainer_id, $diet_id);
        if ($stmt->execute()) {
            $message = "✅ Diet plan request sent successfully!";
        } else {
            $message = "❌ Failed to send request.";
        }
    } else {
        $message = "⚠️ You've already requested this diet plan.";
    }
}

// ✅ Fetch diet plans from approved trainers
$plans = $conn->query("
    SELECT d.*, t.name AS trainer_name 
    FROM diet_plans d 
    JOIN gym_trainers t ON d.trainer_id = t.trainer_id 
    WHERE t.status = 'Approved'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Diet Plans - FitZone Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        .alert {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .alert-success {
            border-color: rgba(40, 167, 69, 0.3);
            color: #28a745;
        }

        .alert-warning {
            border-color: rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }

        .alert-info {
            border-color: rgba(23, 162, 184, 0.3);
            color: #17a2b8;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
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
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .plan-trainer {
            color: var(--text-light);
            font-size: 1rem;
        }

        .plan-actions {
            display: flex;
            gap: 1rem;
            flex-direction: column;
        }

        .btn-view {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-dark);
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-view:hover {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-dark);
        }

        .btn-request {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-request:hover:not(.disabled-btn) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .disabled-btn {
            opacity: 0.5;
            pointer-events: none;
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

        .modal-content {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            color: var(--text-dark);
        }

        .btn-close {
            filter: invert(1);
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

            .plan-actions {
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
            <a href="member_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Request Diet Plans</h1>
            <p>Browse and request personalized diet plans from approved trainers</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : (strpos($message, '⚠️') !== false ? 'alert-warning' : 'alert-info') ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($plans->num_rows == 0): ?>
            <div class="no-plans">
                <i class="fas fa-utensils"></i>
                <h3>No Diet Plans Available</h3>
                <p>No approved diet plans are available yet. Please check back later.</p>
            </div>
        <?php else: ?>
            <div class="plans-grid">
                <?php while ($row = $plans->fetch_assoc()): ?>
                    <div class="plan-card">
                        <div class="plan-header">
                            <h3 class="plan-title"><?= htmlspecialchars($row['title']) ?></h3>
                            <p class="plan-trainer">
                                <i class="fas fa-user-tie"></i>
                                Trainer: <?= htmlspecialchars($row['trainer_name']) ?>
                            </p>
                        </div>

                        <div class="plan-actions">
                            <button type="button"
                                    class="btn-view"
                                    data-bs-toggle="modal"
                                    data-bs-target="#fileModal"
                                    data-id="<?= $row['diet_id'] ?>"
                                    data-file="<?= htmlspecialchars($row['diet_file']) ?>">
                                <i class="fas fa-eye"></i>
                                View Diet Plan
                            </button>

                            <form method="POST" id="form-<?= $row['diet_id'] ?>">
                                <input type="hidden" name="diet_id" value="<?= $row['diet_id'] ?>">
                                <input type="hidden" name="trainer_id" value="<?= $row['trainer_id'] ?>">
                                <button type="submit"
                                        id="btn-<?= $row['diet_id'] ?>"
                                        class="btn-request disabled-btn">
                                    <i class="fas fa-paper-plane"></i>
                                    Request This Plan
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-utensils"></i>
                        Diet Plan Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <iframe id="dietFrame" src="" width="100%" height="600px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- JS: Enable request after file is viewed -->
    <script>
        const modal = document.getElementById('fileModal');
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const file = button.getAttribute('data-file');
            const dietId = button.getAttribute('data-id');
            const iframe = document.getElementById('dietFrame');
            iframe.src = file;

            modal.addEventListener('shown.bs.modal', () => {
                const requestBtn = document.getElementById('btn-' + dietId);
                if (requestBtn) {
                    requestBtn.classList.remove('disabled-btn');
                }
            }, { once: true });
        });
    </script>
</body>
</html>
