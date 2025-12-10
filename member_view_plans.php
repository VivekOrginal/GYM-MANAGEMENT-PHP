<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Member') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "gym_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all workout plan titles
$sql = "SELECT plan_title FROM trainer_workout_plans ORDER BY plan_id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plans | Member Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
        }
        .plan-card {
            background: white;
            padding: 15px;
            border-left: 5px solid #6c5ce7;
            margin-bottom: 15px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            border-radius: 6px;
        }
        h2 {
            margin-bottom: 30px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🏋️ Workout Plan Titles</h2>

    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='plan-card'>";
            echo "<strong>" . htmlspecialchars($row['plan_title']) . "</strong>";
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>No workout plans available yet.</div>";
    }

    $conn->close();
    ?>
</div>

</body>
</html>
