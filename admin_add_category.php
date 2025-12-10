<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);

        if ($stmt->execute()) {
            $message = "✅ Category added successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "❌ Category name is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - FitZone Pro</title>
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
            max-width: 600px;
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

        .form-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            background: var(--dark-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-dark);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .alert {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            border-color: rgba(34, 197, 94, 0.3);
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .alert-danger {
            border-color: rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .container {
                padding: 1rem 15px;
            }

            .form-card {
                padding: 1.5rem;
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
            <h1>Add Category</h1>
            <p>Create a new category for gym equipment and services</p>
        </div>

        <div class="form-card">
            <?php if ($message): ?>
                <?php if (strpos($message, '✅') !== false): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= str_replace('✅ ', '', $message) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= str_replace('❌ ', '', $message) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <form method="POST" id="categoryForm">
                <div class="form-group">
                    <label for="categoryName" class="form-label">
                        <i class="fas fa-tag" style="margin-right: 0.5rem;"></i>Category Name
                    </label>
                    <input type="text" name="name" class="form-control" id="categoryName" 
                           placeholder="Enter category name" required>
                </div>
                
                <div class="form-group">
                    <label for="categoryDescription" class="form-label">
                        <i class="fas fa-align-left" style="margin-right: 0.5rem;"></i>Description (Optional)
                    </label>
                    <textarea name="description" class="form-control" id="categoryDescription" 
                              placeholder="Enter category description"></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus"></i>
                    Add Category
                </button>
            </form>
        </div>
    </div>
    <script>
        // Add smooth form validation feedback
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
        });
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>
