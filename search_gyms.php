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

$searchTerm = "";
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchTerm = trim($_POST['place']);
    $stmt = $conn->prepare("SELECT * FROM gym_trainers WHERE place LIKE ? AND status = 'Approved'");
    $likeTerm = "%$searchTerm%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $results = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM gym_trainers WHERE status = 'Approved'");
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Gyms - FitZone Pro</title>
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
            margin-bottom: 3rem;
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

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .gym-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .gym-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .gym-image-container {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
            background: var(--dark-bg);
        }

        .gym-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: var(--transition);
        }

        .gym-image:hover {
            transform: scale(1.05);
        }

        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-light);
            font-size: 3rem;
        }

        .gym-content {
            padding: 2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .gym-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .gym-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .gym-title h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .gym-location {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .gym-details {
            margin-bottom: 1.5rem;
            flex: 1;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .detail-icon {
            width: 20px;
            color: var(--primary-color);
            text-align: center;
        }

        .detail-text {
            font-size: 0.9rem;
        }

        .gym-actions {
            margin-top: auto;
        }

        .action-btn {
            width: 100%;
            background: var(--gradient-primary);
            color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .modal {
            backdrop-filter: blur(10px);
        }

        .modal-content {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }

            .results-grid {
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
            <a href="member_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Find Your Perfect Gym</h1>
            <p>Discover approved gyms and trainers in your area</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="POST" class="search-form">
                <input type="text" name="place" class="search-input" 
                       placeholder="Enter city or location..." 
                       value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </form>
        </div>

        <!-- Results Section -->
        <?php if ($results && $results->num_rows > 0): ?>
            <div class="results-grid">
                <?php while ($row = $results->fetch_assoc()): ?>
                    <div class="gym-card">
                        <?php
                        $imageFile = $row['gym_image'];
                        $imagePath = '' . $imageFile;
                        $serverPath = __DIR__ . '/' . $imagePath;
                        $modalId = "gymModal" . $row['trainer_id'];
                        ?>

                        <div class="gym-image-container">
                            <?php if (!empty($imageFile) && file_exists($serverPath)): ?>
                                <img src="<?= $imagePath ?>" class="gym-image" alt="Gym Image"
                                     data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-dumbbell"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="gym-content">
                            <div class="gym-header">
                                <div class="gym-icon">
                                    <i class="fas fa-dumbbell"></i>
                                </div>
                                <div class="gym-title">
                                    <h3><?= htmlspecialchars($row['gym_name']) ?></h3>
                                    <div class="gym-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($row['place']) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="gym-details">
                                <div class="detail-item">
                                    <i class="fas fa-user detail-icon"></i>
                                    <span class="detail-text"><?= htmlspecialchars($row['name']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-phone detail-icon"></i>
                                    <span class="detail-text"><?= htmlspecialchars($row['gym_contact']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt detail-icon"></i>
                                    <span class="detail-text"><?= htmlspecialchars($row['gym_location']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-certificate detail-icon"></i>
                                    <span class="detail-text">License: <?= htmlspecialchars($row['licence_number']) ?></span>
                                </div>
                            </div>

                            <div class="gym-actions">
                                <a href="view_gym_plans.php?trainer_id=<?= $row['trainer_id'] ?>" class="action-btn">
                                    <i class="fas fa-eye"></i>
                                    View Plans
                                </a>
                            </div>
                        </div>

                        <!-- Modal for Full Image -->
                        <?php if (!empty($imageFile) && file_exists($serverPath)): ?>
                            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-body text-center p-0">
                                            <img src="<?= $imagePath ?>" class="img-fluid" alt="Full Gym Image">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No Gyms Found</h3>
                <p><?= $searchTerm ? 'No gyms found for: <strong>' . htmlspecialchars($searchTerm) . '</strong>' : 'No approved gyms available at the moment.' ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>