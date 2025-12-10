<?php
session_start();

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $query = "SELECT * FROM gym_users WHERE (name LIKE ? OR email LIKE ?) AND role = 'Member' ORDER BY user_id DESC";
    $stmt = $conn->prepare($query);
    $likeSearch = "%$search%";
    $stmt->bind_param("ss", $likeSearch, $likeSearch);
} else {
    $query = "SELECT * FROM gym_users WHERE role = 'Member' ORDER BY user_id DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - FitZone Pro</title>
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

        .members-table {
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

        .member-avatar {
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

        .member-info {
            display: flex;
            align-items: center;
        }

        .member-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
        }

        .member-details p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .no-members {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-members i {
            font-size: 4rem;
            margin-bottom: 1rem;
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

            .table-wrapper {
                font-size: 0.8rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
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
            <h1>Manage Members</h1>
            <p>View and manage all registered gym members</p>
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
                <a href="manage_members.php" class="search-btn clear-btn">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </form>
        </div>

        <!-- Members Table -->
        <?php if ($result->num_rows > 0): ?>
            <div class="members-table">
                <div class="table-header">
                    <i class="fas fa-users"></i>
                    <h3>Registered Members (<?= $result->num_rows ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Date of Birth</th>
                                <th>Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="member-info">
                                            <div class="member-avatar">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <div class="member-details">
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
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($row['place']) ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($row['dob'])) ?></td>
                                    <td>
                                        <span style="padding: 0.25rem 0.75rem; background: rgba(255, 71, 87, 0.2); border-radius: 50px; font-size: 0.8rem;">
                                            <?= htmlspecialchars($row['gender']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="no-members">
                <i class="fas fa-users-slash"></i>
                <h3>No Members Found</h3>
                <p><?= $search ? "No members found for '<strong>$search</strong>'" : 'No members registered yet.' ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>