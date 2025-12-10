<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'email_config.php';

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Approval/Reject
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $trainer_id = filter_var($_POST['trainer_id'], FILTER_VALIDATE_INT);
    if (!$trainer_id) die("Invalid Trainer ID");

    $stmt = $conn->prepare("SELECT email, name FROM gym_trainers WHERE trainer_id = ?");
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $trainer = $result->fetch_assoc();
    $stmt->close();

    if (!$trainer) die("Trainer not found");

    $to = $trainer['email'];
    $name = $trainer['name'];
    $subject = "";
    $message = "";

    if (isset($_POST['approve'])) {
        $update = $conn->prepare("UPDATE gym_trainers SET status = 'Approved' WHERE trainer_id = ?");
        $update->bind_param("i", $trainer_id);
        $update->execute();
        $update->close();

        $subject = "🎉 Trainer Application Approved - Welcome to FitZone!";
        $message = "Dear $name,\n\nCongratulations! Your trainer registration has been approved.\n\nYou can now log in to your trainer dashboard using your registered email and password.\n\nLogin URL: http://localhost/gym/trainer_login.php\n\nWelcome to the FitZone team!\n\nBest regards,\nFitZone Admin Team";
    } elseif (isset($_POST['reject'])) {
        $notes = strip_tags($_POST['rejection_notes'] ?? '');
        $update = $conn->prepare("UPDATE gym_trainers SET status = 'Rejected' WHERE trainer_id = ?");
        $update->bind_param("i", $trainer_id);
        $update->execute();
        $update->close();

        $subject = "Trainer Application Update - FitZone";
        $message = "Dear $name,\n\nThank you for your interest in joining FitZone as a trainer.\n\nUnfortunately, we cannot approve your application at this time.\nReason: $notes\n\nYou may reapply after addressing the mentioned concerns.\n\nBest regards,\nFitZone Admin Team";
    }

    // Send email
    if (!empty($subject) && !empty($message)) {
        if (sendEmail($to, $name, $subject, $message)) {
            $_SESSION['message'] = "✅ Email sent successfully to $name";
        } else {
            $_SESSION['message'] = "❌ Failed to send email to $name";
        }
    }

    header("Location: admin_trainers.php");
    exit;
}

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $query = "SELECT * FROM gym_trainers WHERE (name LIKE ? OR email LIKE ?) AND status = 'Pending' ORDER BY trainer_id DESC";
    $stmt = $conn->prepare($query);
    $likeSearch = "%$search%";
    $stmt->bind_param("ss", $likeSearch, $likeSearch);
} else {
    $query = "SELECT * FROM gym_trainers WHERE status = 'Pending' ORDER BY trainer_id DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$pending_trainers = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trainers - FitZone Pro</title>
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

        .trainers-table {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: var(--gradient-primary);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .table-header h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            margin: 0;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table th {
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .trainer-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
        }

        .trainer-info {
            display: flex;
            align-items: center;
        }

        .trainer-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
        }

        .trainer-details p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-approve {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-approve:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(86, 171, 47, 0.3);
        }

        .btn-reject {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-reject:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(255, 65, 108, 0.3);
        }

        .reject-input {
            background: var(--dark-bg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            color: var(--text-dark);
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            width: 150px;
        }

        .reject-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .reject-input::placeholder {
            color: var(--text-light);
        }

        .no-trainers {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-trainers i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .alert {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
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

            .table-wrapper {
                font-size: 0.8rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .reject-input {
                width: 120px;
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
            <h1>Manage Trainers</h1>
            <p>Review and approve pending trainer applications</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="get" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search by name or email..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <a href="admin_trainers.php" class="search-btn clear-btn">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </form>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Trainers Table -->
        <?php if ($pending_trainers->num_rows > 0): ?>
            <div class="trainers-table">
                <div class="table-header">
                    <i class="fas fa-user-check"></i>
                    <h3>Pending Trainer Applications (<?= $pending_trainers->num_rows ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Trainer</th>
                                <th>Contact</th>
                                <th>Gym Details</th>
                                <th>License</th>
                                <th>Documents</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $pending_trainers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="trainer-info">
                                            <div class="trainer-avatar">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <div class="trainer-details">
                                                <h4><?= htmlspecialchars($row['name']) ?></h4>
                                                <p><?= htmlspecialchars($row['email']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-phone"></i>
                                            <?= htmlspecialchars($row['phone']) ?>
                                        </div>
                                        <div style="margin-top: 0.25rem; font-size: 0.8rem; color: var(--text-light);">
                                            <?= htmlspecialchars($row['address']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($row['gym_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-light);">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($row['gym_location']) ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-light);">
                                            <i class="fas fa-phone"></i>
                                            <?= htmlspecialchars($row['gym_contact']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.8rem;">
                                            <strong>License:</strong> <?= htmlspecialchars($row['licence_number']) ?>
                                        </div>
                                        <div style="font-size: 0.8rem; margin-top: 0.25rem;">
                                            <strong>GST:</strong> <?= htmlspecialchars($row['gst_number']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <?php if (!empty($row['id_proof'])): ?>
                                                <a href="uploads/trainer_id_proofs/<?= basename($row['id_proof']) ?>" target="_blank" 
                                                   style="color: var(--primary-color); font-size: 0.8rem;">
                                                    <i class="fas fa-id-card"></i> ID
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($row['gym_image'])): ?>
                                                <a href="uploads/gym_images/<?= basename($row['gym_image']) ?>" target="_blank" 
                                                   style="color: var(--primary-color); font-size: 0.8rem;">
                                                    <i class="fas fa-image"></i> Gym
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="trainer_id" value="<?= $row['trainer_id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button name="approve" class="btn-approve">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="display: flex; gap: 0.25rem; align-items: center;">
                                                <input type="hidden" name="trainer_id" value="<?= $row['trainer_id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="text" name="rejection_notes" placeholder="Reason..." required class="reject-input">
                                                <button name="reject" class="btn-reject">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="no-trainers">
                <i class="fas fa-user-check"></i>
                <h3>No Pending Applications</h3>
                <p><?= $search ? "No trainers found for '<strong>$search</strong>'" : 'All trainer applications have been processed.' ?></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
        
        // Add loading state to buttons
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[name="approve"], button[name="reject"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
</body>
</html>
