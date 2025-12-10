<?php
$conn = new mysqli("localhost", "root", "", "gym_management");

$sql = "DROP TABLE IF EXISTS admin_requests";

if ($conn->query($sql) === TRUE) {
    echo "Table 'admin_requests' removed successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>