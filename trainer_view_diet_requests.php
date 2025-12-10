<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$conn = new mysqli("localhost", "root", "", "gym_management");
if (!isset($_SESSION['trainer_id'])) {
    header("Location: login.php");
    exit;
}

$trainer_id = $_SESSION['trainer_id'];
$message = "";

// Handle Approve/Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if (in_array($action, ['Approved', 'Rejected'])) {
        $stmt = $conn->prepare("UPDATE diet_requests SET status = ? WHERE request_id = ? AND trainer_id = ?");
        $stmt->bind_param("sii", $action, $request_id, $trainer_id);
        if ($stmt->execute()) {
            $message = "Request status updated successfully.";

            // If approved, send email to user
            if ($action === 'Approved') {
                $email_query = $conn->prepare("
                    SELECT u.email, u.name, dp.title, dp.fee 
                    FROM diet_requests dr
                    JOIN gym_users u ON dr.user_id = u.user_id
                    JOIN diet_plans dp ON dr.diet_id = dp.diet_id
                    WHERE dr.request_id = ?
                ");
                $email_query->bind_param("i", $request_id);
                $email_query->execute();
                $result_email = $email_query->get_result();
                if ($result_email->num_rows > 0) {
                    $user = $result_email->fetch_assoc();
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'gymmanagement05@gmail.com';
                        $mail->Password   = 'soqv jeoa eohk evej';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;

                        $mail->setFrom('gymmanagement05@gmail.com', 'FitZone Gym Management');
                        $mail->addAddress($user['email'], $user['name']);
                        $mail->isHTML(true);
                        $mail->Subject = 'Diet Plan Approved – Payment Required';
                        $mail->Body = "
                            <h3>Hi {$user['name']},</h3>
                            <p>Your request for the diet plan <strong>{$user['title']}</strong> has been <b>approved</b>.</p>
                            <p>Please proceed to pay the fee: <strong>₹{$user['fee']}</strong></p>
                            
                            <br><p>Thank you!</p>
                        ";

                        $mail->send();
                        $message .= " Email notification sent to user.";
                    } catch (Exception $e) {
                        $message .= " Email error: {$mail->ErrorInfo}";
                    }
                }
            }
        } else {
            $message = "Failed to update request status.";
        }
    }
}

// Fetch all requests
$stmt = $conn->prepare("
    SELECT dr.*, u.name AS member_name, u.email, dp.title AS diet_title 
    FROM diet_requests dr
    JOIN gym_users u ON dr.user_id = u.user_id
    JOIN diet_plans dp ON dr.diet_id = dp.diet_id
    WHERE dr.trainer_id = ?
    ORDER BY dr.request_date DESC
");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Plan Requests</title>
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
            max-width: 1200px;
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
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .request-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #56ab2f;
        }
        
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .member-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
        }
        
        .member-email {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .request-id {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .diet-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .diet-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .request-date {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-approve {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.4);
            color: white;
        }
        
        .btn-reject {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
            color: white;
        }
        
        .no-action {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
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
        
        .alert-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .requests-grid {
                grid-template-columns: 1fr;
            }
            
            .header-title {
                font-size: 1.5rem;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-card">
            <h1 class="header-title">
                <i class="fas fa-inbox me-2"></i>
                Diet Plan Requests
            </h1>
            <p class="header-subtitle">Manage member diet plan requests and approvals</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php 
        $total_requests = $result ? $result->num_rows : 0;
        $approved = $pending = $rejected = 0;
        if ($result) {
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                if ($row['status'] === 'Approved') $approved++;
                elseif ($row['status'] === 'Pending') $pending++;
                elseif ($row['status'] === 'Rejected') $rejected++;
            }
            $result->data_seek(0);
        }
        ?>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon text-primary"><i class="fas fa-inbox"></i></div>
                <div class="stat-number text-primary"><?= $total_requests ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number text-success"><?= $approved ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon text-warning"><i class="fas fa-clock"></i></div>
                <div class="stat-number text-warning"><?= $pending ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon text-danger"><i class="fas fa-times-circle"></i></div>
                <div class="stat-number text-danger"><?= $rejected ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <?php if ($result->num_rows === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>No Diet Plan Requests</h3>
                <p>No members have requested diet plans yet.</p>
            </div>
        <?php else: ?>
            <div class="requests-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div>
                                <h3 class="member-name"><?= htmlspecialchars($row['member_name']) ?></h3>
                                <div class="member-email">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?= htmlspecialchars($row['email']) ?>
                                </div>
                            </div>
                            <div class="request-id">#<?= $row['request_id'] ?></div>
                        </div>
                        
                        <div class="diet-info">
                            <div class="diet-title">
                                <i class="fas fa-utensils"></i>
                                <?= htmlspecialchars($row['diet_title']) ?>
                            </div>
                            <div class="request-date">
                                <i class="fas fa-calendar-alt"></i>
                                Requested on <?= date("d M Y", strtotime($row['request_date'])) ?>
                            </div>
                        </div>
                        
                        <div class="status-section">
                            <span class="status-badge status-<?= strtolower($row['status']) ?>">
                                <?php if ($row['status'] === 'Approved'): ?>
                                    <i class="fas fa-check me-1"></i>
                                <?php elseif ($row['status'] === 'Rejected'): ?>
                                    <i class="fas fa-times me-1"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock me-1"></i>
                                <?php endif; ?>
                                <?= $row['status'] ?>
                            </span>
                        </div>
                        
                        <?php if ($row['status'] === 'Pending'): ?>
                            <form method="POST" class="action-buttons">
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                <button name="action" value="Approved" class="btn-approve">
                                    <i class="fas fa-check"></i>
                                    Approve
                                </button>
                                <button name="action" value="Rejected" class="btn-reject">
                                    <i class="fas fa-times"></i>
                                    Reject
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="no-action">
                                <i class="fas fa-info-circle me-1"></i>
                                Request already processed
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="trainer_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>