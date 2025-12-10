<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!str_ends_with($email, '@gmail.com')) {
        $error = "Email must be a Gmail address (@gmail.com).";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $checkStmt = $conn->prepare("SELECT user_id FROM gym_users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO gym_users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'Admin', 'Pending')");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $phone);

            if ($stmt->execute()) {
                $success = "Registration request submitted! Awaiting admin approval.";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .registration-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .form-container {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 10px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="registration-container">
    <div class="header">
        <h2>🛡️ Admin Registration</h2>
        <p class="mb-0">Request administrative access</p>
    </div>
    
    <div class="form-container">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="admin@gmail.com">
            </div>
            
            <div class="form-group" style="position: relative;">
                <label class="form-label">Password</label>
                <input type="password" id="passwordField" name="password" class="form-control" required placeholder="Minimum 6 characters">
                <span style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #667eea;" onclick="togglePassword()">👁️</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" required placeholder="10-digit phone number">
            </div>
            

            
            <button type="submit" class="btn btn-register w-100">Submit Registration Request</button>
        </form>
        
        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passField = document.getElementById('passwordField');
    passField.type = passField.type === 'password' ? 'text' : 'password';
}

document.querySelector('form').addEventListener('submit', function(e) {
    const email = this.email.value;
    const phone = this.phone.value;
    
    if (!email.endsWith('@gmail.com')) {
        alert('Email must be a Gmail address (@gmail.com).');
        e.preventDefault();
        return;
    }
    
    if (!/^\d{10}$/.test(phone)) {
        alert('Phone number must be exactly 10 digits.');
        e.preventDefault();
        return;
    }
});
</script>

</body>
</html>