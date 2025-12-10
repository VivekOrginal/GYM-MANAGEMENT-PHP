<?php
$conn = new mysqli("localhost", "root", "", "gym_management");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM categories WHERE category_id = $id");
}

header("Location: admin_view_categories.php");
exit();
