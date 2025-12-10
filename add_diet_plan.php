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

$upload_success = false;
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trainer_id = $_SESSION['trainer_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $fee = $_POST['fee'];
    $diet_file = "";

    // Handle file upload
    if (isset($_FILES['diet_file']) && $_FILES['diet_file']['error'] == 0) {
        $target_dir = "uploads/diet_plans/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $diet_file = $target_dir . time() . "_" . basename($_FILES['diet_file']['name']);
        if (!move_uploaded_file($_FILES['diet_file']['tmp_name'], $diet_file)) {
            $error_message = "Failed to upload file.";
        }
    }

    if (empty($error_message)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO diet_plans (trainer_id, title, description, diet_file, fee) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssd", $trainer_id, $title, $description, $diet_file, $fee);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $upload_success = true;
        } else {
            $error_message = "Failed to save diet plan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Diet Plan</title>
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
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
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
            border-color: #56ab2f;
            box-shadow: 0 0 0 0.2rem rgba(86, 171, 47, 0.25);
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
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
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
            box-shadow: 0 8px 25px rgba(86, 171, 47, 0.4);
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
            border-color: #56ab2f;
            background: rgba(86, 171, 47, 0.05);
            color: #56ab2f;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .feature-highlight {
            background: linear-gradient(135deg, rgba(86, 171, 47, 0.1) 0%, rgba(168, 230, 207, 0.1) 100%);
            border: 1px solid rgba(86, 171, 47, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #56ab2f;
            font-weight: 500;
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
            
            .feature-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-card">
            <h1 class="header-title">
                <i class="fas fa-utensils me-2"></i>
                Add Diet Plan
            </h1>
            <p class="header-subtitle">Create a comprehensive nutrition plan for your members</p>
        </div>

        <?php if ($upload_success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Diet plan uploaded successfully!
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <div class="feature-highlight">
            <h5 class="mb-3"><i class="fas fa-star me-2"></i>Diet Plan Features</h5>
            <ul class="feature-list">
                <li class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    Customized nutrition plans
                </li>
                <li class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    PDF & image support
                </li>
                <li class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    Flexible pricing options
                </li>
                <li class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    Member progress tracking
                </li>
            </ul>
        </div>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="form-label">
                        <i class="fas fa-heading"></i>
                        Diet Plan Title
                    </label>
                    <input type="text" name="title" class="form-control" required 
                           placeholder="e.g. Weight Loss Diet Plan, Muscle Gain Nutrition">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i>
                        Description
                    </label>
                    <textarea name="description" class="form-control" required 
                              placeholder="Describe the diet plan, its benefits, target audience, and key features..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-file-upload"></i>
                        Upload Diet Plan File
                    </label>
                    <div class="file-upload">
                        <input type="file" name="diet_file" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                            <div>
                                <div>Click to upload diet plan</div>
                                <small>PDF, JPG, PNG formats supported</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fee" class="form-label">
                        <i class="fas fa-rupee-sign"></i>
                        Diet Plan Fee
                    </label>
                    <input type="number" name="fee" class="form-control" step="0.01" required 
                           placeholder="e.g. 299.00">
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i>
                        Upload Diet Plan
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
                const file = e.target.files[0];
                label.innerHTML = `
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                    <div>
                        <div>File selected: ${file.name}</div>
                        <small>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    </div>
                `;
            }
        });

        // Form submission loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>