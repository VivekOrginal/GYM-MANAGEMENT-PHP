<?php
$conn = new mysqli("localhost", "root", "", "gym_management");

$sql = "ALTER TABLE gym_users ADD COLUMN status ENUM('Active', 'Pending', 'Rejected') DEFAULT 'Active'";

if ($conn->query($sql) === TRUE) {
    echo "Status column added successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>