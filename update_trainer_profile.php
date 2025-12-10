<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

if (!isset($_SESSION['trainer_id'])) {
    header("Location: trainer_login.php");
    exit;
}

$trainer_id = $_SESSION['trainer_id'];
$msg = "";
$error = "";

// Load existing trainer data
$stmt = $conn->prepare("SELECT * FROM gym_trainers WHERE trainer_id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name         = trim($_POST['name']);
        $phone        = trim($_POST['phone']);
        $address      = trim($_POST['address']);
        $place        = trim($_POST['place']);
        $gym_name     = trim($_POST['gym_name']);
        $gym_location = trim($_POST['gym_location']);
        $gym_contact  = trim($_POST['gym_contact']);

        $id_proof = $trainer['id_proof'];
        $gym_image = $trainer['gym_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];

        if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] == 0) {
            if (in_array($_FILES['id_proof']['type'], $allowed_types)) {
                $id_proof = 'uploads/trainer_id_proofs/' . time() . '_' . basename($_FILES["id_proof"]["name"]);
                move_uploaded_file($_FILES["id_proof"]["tmp_name"], $id_proof);
            } else {
                $error = "Invalid ID proof format.";
            }
        }

        if (isset($_FILES['gym_image']) && $_FILES['gym_image']['error'] == 0) {
            if (in_array($_FILES['gym_image']['type'], $allowed_types)) {
                $gym_image = 'uploads/gym_images/' . time() . '_' . basename($_FILES["gym_image"]["name"]);
                move_uploaded_file($_FILES["gym_image"]["tmp_name"], $gym_image);
            } else {
                $error = "Invalid gym image format.";
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE gym_trainers SET 
                name = ?, phone = ?, address = ?, place = ?, 
                id_proof = ?, gym_name = ?, gym_location = ?, gym_contact = ?, gym_image = ?
                WHERE trainer_id = ?");

            $stmt->bind_param("sssssssssi", $name, $phone, $address, $place, $id_proof, $gym_name, $gym_location, $gym_contact, $gym_image, $trainer_id);

            if ($stmt->execute()) {
                $msg = "Profile updated successfully.";
            } else {
                $error = "Update failed: " . $stmt->error;
            }

            $stmt->close();

            // Reload updated data
            $stmt = $conn->prepare("SELECT * FROM gym_trainers WHERE trainer_id = ?");
            $stmt->bind_param("i", $trainer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $trainer = $result->fetch_assoc();
            $stmt->close();
        }
    } elseif (isset($_POST['change_password'])) {
        $new_password = $_POST['new_password'];
        
        if (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE gym_trainers SET password = ? WHERE trainer_id = ?");
            $stmt->bind_param("si", $hashed_password, $trainer_id);
            
            if ($stmt->execute()) {
                $msg = "Password changed successfully.";
            } else {
                $error = "Failed to change password.";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            text-align: center;
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }
        
        .trainer-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        
        .trainer-name {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .trainer-role {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 1rem;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .top-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: #dc3545;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }
        
        .section-divider {
            border: none;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 2rem 0;
            border-radius: 2px;
        }
        
        .file-preview {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="trainer-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="trainer-name"><?= htmlspecialchars($trainer['name']) ?></div>
            <div class="trainer-role">Fitness Trainer</div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="trainer_dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_add_plan.php" class="nav-link">
                    <i class="fas fa-plus"></i>
                    Add Workout Plan
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_view_plans.php" class="nav-link">
                    <i class="fas fa-dumbbell"></i>
                    View Plans
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_view_members.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    View Members
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_manage_members.php" class="nav-link">
                    <i class="fas fa-cogs"></i>
                    Manage Members
                </a>
            </li>
            <li class="nav-item">
                <a href="add_diet_plan.php" class="nav-link">
                    <i class="fas fa-utensils"></i>
                    Add Diet Plan
                </a>
            </li>
            <li class="nav-item">
                <a href="view_diet_plans.php" class="nav-link">
                    <i class="fas fa-list"></i>
                    View Diet Plans
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_view_diet_requests.php" class="nav-link">
                    <i class="fas fa-inbox"></i>
                    Diet Requests
                </a>
            </li>
            <li class="nav-item">
                <a href="update_trainer_profile.php" class="nav-link active">
                    <i class="fas fa-user-edit"></i>
                    Profile Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="index.html" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Profile Settings</h1>
            <div class="text-muted">Update your profile information</div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $msg ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-user-edit me-2"></i>Personal Information
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($trainer['name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($trainer['email']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($trainer['phone']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Place</label>
                            <input type="text" name="place" class="form-control" required value="<?= htmlspecialchars($trainer['place']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($trainer['address']) ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">License Number</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($trainer['licence_number']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GST Number</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($trainer['gst_number']) ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ID Proof (PDF/IMG)</label>
                        <?php if ($trainer['id_proof']): ?>
                            <div class="file-preview">
                                <a href="<?= $trainer['id_proof'] ?>" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-file-alt me-2"></i>View Current ID Proof
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="id_proof" class="form-control mt-2">
                    </div>

                    <hr class="section-divider">
                    
                    <h5 class="mb-3"><i class="fas fa-dumbbell me-2"></i>Gym Details</h5>

                    <div class="mb-3">
                        <label class="form-label">Gym Name</label>
                        <input type="text" name="gym_name" class="form-control" required value="<?= htmlspecialchars($trainer['gym_name']) ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Gym Location</label>
                            <input type="text" name="gym_location" class="form-control" required value="<?= htmlspecialchars($trainer['gym_location']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gym Contact</label>
                            <input type="text" name="gym_contact" class="form-control" required value="<?= htmlspecialchars($trainer['gym_contact']) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Gym Image (PNG/JPG)</label>
                        <?php if ($trainer['gym_image']): ?>
                            <div class="file-preview">
                                <img src="<?= $trainer['gym_image'] ?>" width="120" class="rounded">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="gym_image" class="form-control mt-2">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>

        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-lock me-2"></i>Change Password
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3" style="position: relative;">
                        <label class="form-label">New Password</label>
                        <input type="password" id="newPasswordField" name="new_password" class="form-control" required minlength="6" placeholder="Enter new password (min 6 characters)">
                        <span style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #667eea;" onclick="toggleNewPassword()">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-danger">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        function toggleNewPassword() {
            const passField = document.getElementById('newPasswordField');
            const icon = event.target;
            if (passField.type === 'password') {
                passField.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passField.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.querySelector('.mobile-toggle');
                
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>
