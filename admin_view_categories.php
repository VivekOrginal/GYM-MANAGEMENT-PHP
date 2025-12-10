<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

$result = $conn->query("SELECT * FROM categories ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - FitZone Pro</title>
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

        .action-section {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .add-btn {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        .categories-table {
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

        .category-icon {
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

        .category-info {
            display: flex;
            align-items: center;
        }

        .category-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
        }

        .category-details p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-edit:hover {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107;
            text-decoration: none;
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-delete:hover {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            text-decoration: none;
        }

        .no-categories {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-categories i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .action-section {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
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
            <h1>Manage Categories</h1>
            <p>View and manage all workout and fitness categories</p>
        </div>

        <!-- Action Section -->
        <div class="action-section">
            <div>
                <h4 style="margin: 0; color: var(--text-dark);">All Categories (<?= $result->num_rows ?>)</h4>
            </div>
            <a href="admin_add_category.php" class="add-btn">
                <i class="fas fa-plus"></i>
                Add New Category
            </a>
        </div>

        <!-- Categories Table -->
        <?php if ($result->num_rows > 0): ?>
            <div class="categories-table">
                <div class="table-header">
                    <i class="fas fa-tags"></i>
                    <h3>Registered Categories (<?= $result->num_rows ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="category-info">
                                            <div class="category-icon">
                                                <i class="fas fa-tag"></i>
                                            </div>
                                            <div class="category-details">
                                                <h4><?= htmlspecialchars($row['name']) ?></h4>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px;">
                                            <?= !empty($row['description']) ? htmlspecialchars($row['description']) : '<em style="color: var(--text-light);">No description provided</em>' ?>
                                        </div>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_category.php?id=<?= $row['category_id'] ?>" class="btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <a href="delete_category.php?id=<?= $row['category_id'] ?>" 
                                               class="btn-delete" 
                                               onclick="return confirm('Are you sure you want to delete this category?');">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="no-categories">
                <i class="fas fa-tags"></i>
                <h3>No Categories Found</h3>
                <p>No categories have been created yet. Start by adding your first category.</p>
                <a href="admin_add_category.php" class="add-btn" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Add First Category
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
