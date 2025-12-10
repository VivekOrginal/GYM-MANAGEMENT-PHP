<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gym_management");
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Gmail SMTP function using existing credentials
function sendEmail($to, $subject, $body) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gymmanagement05@gmail.com';
        $mail->Password = 'soqv jeoa eohk evej';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('gymmanagement05@gmail.com', 'FitZone Gym Management');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}

$message = "";
$error = "";
$step = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['send_otp'])) {
        $email = trim($_POST['email']);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            $stmt = $conn->prepare("SELECT trainer_id, name FROM gym_trainers WHERE email = ? AND status = 'Approved'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $trainer = $result->fetch_assoc();
                $otp = rand(100000, 999999);
                
                // Store OTP in session
                $_SESSION['reset_otp'] = $otp;
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_trainer_id'] = $trainer['trainer_id'];
                
                // Send OTP via Gmail SMTP
                if (sendEmail($email, "Password Reset OTP - Gym Management", "Hello " . $trainer['name'] . ",\n\nYour OTP for password reset is: " . $otp . "\n\nThis OTP is valid for 10 minutes.\n\nBest regards,\nGym Management Team")) {
                    $message = "OTP sent to your email address.";
                    $step = 2;
                } else {
                    $error = "Failed to send OTP. Please try again.";
                }
            } else {
                $error = "No approved trainer account found with this email.";
            }
        }
    } elseif (isset($_POST['verify_otp'])) {
        $entered_otp = trim($_POST['otp']);
        
        if ($entered_otp == $_SESSION['reset_otp']) {
            // Generate new password
            $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password in database
            $stmt = $conn->prepare("UPDATE gym_trainers SET password = ? WHERE trainer_id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['reset_trainer_id']);
            
            if ($stmt->execute()) {
                // Send new password via Gmail SMTP
                if (sendEmail($_SESSION['reset_email'], "New Password - Gym Management", "Hello,\n\nYour password has been reset successfully.\n\nNew Login Details:\nEmail: " . $_SESSION['reset_email'] . "\nPassword: " . $new_password . "\n\nPlease login and change your password.\n\nBest regards,\nGym Management Team")) {
                    $message = "New password sent to your email address.";
                    // Clear session data
                    unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_trainer_id']);
                    $step = 3;
                } else {
                    $error = "Password updated but failed to send email.";
                }
            } else {
                $error = "Failed to update password.";
            }
        } else {
            $error = "Invalid OTP. Please try again.";
            $step = 2;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Trainer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-container {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-header h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            font-size: 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px;
            border-radius: 10px;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .step-indicator {
            text-align: center;
            margin-bottom: 20px;
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="reset-header">
        <h2>🔐 Reset Password</h2>
        <p class="text-muted">Trainer Account Recovery</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
        <div class="step-indicator">Step 1: Enter Email</div>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your registered email">
            </div>
            <button type="submit" name="send_otp" class="btn btn-primary w-100">Send OTP</button>
        </form>
    <?php elseif ($step == 2): ?>
        <div class="step-indicator">Step 2: Enter OTP</div>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Enter OTP</label>
                <input type="text" name="otp" class="form-control" required placeholder="Enter 6-digit OTP" maxlength="6">
                <small class="text-muted">Check your email for the OTP</small>
            </div>
            <button type="submit" name="verify_otp" class="btn btn-primary w-100">Verify OTP</button>
        </form>
    <?php elseif ($step == 3): ?>
        <div class="step-indicator">✅ Password Reset Complete</div>
        <div class="text-center">
            <p class="text-success">Your new password has been sent to your email.</p>
            <a href="trainer_login.php" class="btn btn-primary">Back to Login</a>
        </div>
    <?php endif; ?>

    <?php if ($step != 3): ?>
        <div class="text-center mt-3">
            <a href="trainer_login.php" class="btn btn-secondary">← Back to Login</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>