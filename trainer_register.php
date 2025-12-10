<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $place = trim($_POST['place']);
    $license_number = trim($_POST['license_number']);
    $gst_number = trim($_POST['gst_number']);
    $gym_name = trim($_POST['gym_name']);
    $gym_location = trim($_POST['gym_location']);
    $gym_contact = trim($_POST['gym_contact']);

    // Server-side validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!str_ends_with($email, '@gmail.com')) {
        $error = "Email must be a Gmail address (@gmail.com).";
    } elseif (!preg_match("/^\d{10}$/", $phone)) {
        $error = "Phone number must be 10 digits.";
    } elseif (!preg_match("/^\d{10}$/", $gym_contact)) {
        $error = "Gym contact number must be 10 digits.";
    } elseif (strlen($password_raw) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        $id_proof = '';
        $gym_image = '';
        $upload_dir = 'uploads/trainer_id_proofs/';
        $gym_images = 'uploads/gym_images/';
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];

        // ID Proof upload
        if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] == 0) {
            if (in_array($_FILES['id_proof']['type'], $allowed_types)) {
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $id_proof = $upload_dir . time() . '_' . basename($_FILES["id_proof"]["name"]);
                move_uploaded_file($_FILES["id_proof"]["tmp_name"], $id_proof);
            } else {
                $error = "Invalid ID proof format.";
            }
        }

        // Gym Image upload
        if (isset($_FILES['gym_image']) && $_FILES['gym_image']['error'] == 0) {
            if (in_array($_FILES['gym_image']['type'], $allowed_types)) {
                if (!is_dir($gym_images)) mkdir($gym_images, 0755, true);
                $gym_image = $gym_images . time() . '_' . basename($_FILES["gym_image"]["name"]);
                move_uploaded_file($_FILES["gym_image"]["tmp_name"], $gym_image);
            } else {
                $error = "Invalid gym image format.";
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO gym_trainers 
                (name, email, password, phone, address, place, licence_number, gst_number, id_proof, gym_name, gym_location, gym_contact, gym_image, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("sssssssssssss", $name, $email, $password, $phone, $address, $place, $license_number, $gst_number, $id_proof, $gym_name, $gym_location, $gym_contact, $gym_image);

            if ($stmt->execute()) {
                $msg = "Registration successful! Awaiting admin approval.";
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Registration - FitZone Pro</title>
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
            position: relative;
            overflow-x: hidden;
        }

        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .video-background video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
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
            max-width: 900px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }

        .registration-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .card-header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
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

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 1.2rem;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: var(--dark-bg);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            color: var(--text-light);
        }

        .file-input-label:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            padding: 1.2rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #2ed573;
        }

        .alert-danger {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: var(--primary-color);
        }

        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .container {
                margin: 100px auto 30px;
                padding: 0 15px;
            }

            .registration-card {
                padding: 2rem;
            }

            .card-header h1 {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .nav-container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Video Background -->
    <div class="video-background">
        <video autoplay muted loop>
            <source src="assets/videos/trainer-bg.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>FitZone<span>Pro</span></h2>
            </div>
            <a href="modern.html" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="registration-card">
            <div class="card-header">
                <h1>Trainer Registration</h1>
                <p>Join our team of professional fitness trainers</p>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $msg ?>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" novalidate>
                <!-- Personal Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required placeholder="example@gmail.com">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 characters">
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" required pattern="\d{10}" placeholder="10-digit mobile number">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" required placeholder="Enter your full address" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="place">City/Town</label>
                        <input type="text" id="place" name="place" class="form-control" required placeholder="Enter your city or town">
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-certificate"></i>
                        Professional Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="license_number">License Number</label>
                            <input type="text" id="license_number" name="license_number" class="form-control" required pattern="[A-Z0-9]{6,20}" placeholder="E.g. LIC1234567">
                        </div>
                        <div class="form-group">
                            <label for="gst_number">GST Number</label>
                            <input type="text" id="gst_number" name="gst_number" class="form-control" required pattern="\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}Z[A-Z\d]{1}" placeholder="E.g. 22ABCDE1234F1Z5">
                        </div>
                    </div>
                </div>

                <!-- Gym Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-dumbbell"></i>
                        Gym Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="gym_name">Gym Name</label>
                            <input type="text" id="gym_name" name="gym_name" class="form-control" required placeholder="Enter gym name">
                        </div>
                        <div class="form-group">
                            <label for="gym_contact">Gym Contact</label>
                            <input type="text" id="gym_contact" name="gym_contact" class="form-control" required pattern="\d{10}" placeholder="10-digit contact number">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="gym_location">Gym Location</label>
                        <input type="text" id="gym_location" name="gym_location" class="form-control" required placeholder="Enter gym location">
                    </div>
                </div>

                <!-- File Uploads -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-upload"></i>
                        Documents & Images
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Gym Image</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="gym_image" name="gym_image" class="file-input" accept=".jpg,.jpeg,.png" required>
                                <label for="gym_image" class="file-input-label">
                                    <i class="fas fa-camera"></i>
                                    Choose gym image
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>ID Proof</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="id_proof" name="id_proof" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                <label for="id_proof" class="file-input-label">
                                    <i class="fas fa-file-upload"></i>
                                    Choose ID proof (PDF/Image)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Register as Trainer
                </button>

                <div class="login-link">
                    Already registered? <a href="trainer_login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // File input labels update
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const label = document.querySelector(`label[for="${this.id}"]`);
                const fileName = this.files[0]?.name || 'Choose file';
                label.innerHTML = `<i class="fas fa-check"></i> ${fileName}`;
            });
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const email = form.email.value;
            const phone = form.phone.value;
            const gym_contact = form.gym_contact.value;
            const emailRegex = /^[^\s@]+@gmail\.com$/;
            const phoneRegex = /^\d{10}$/;

            if (!emailRegex.test(email)) {
                alert('Email must be a Gmail address (@gmail.com).');
                e.preventDefault();
            } else if (!phoneRegex.test(phone) || !phoneRegex.test(gym_contact)) {
                alert('Phone and Gym Contact must be 10 digits.');
                e.preventDefault();
            }
        });

        // Smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.registration-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>