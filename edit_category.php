<?php
$conn = new mysqli("localhost", "root", "", "gym_management");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM categories WHERE category_id = $id");
    $category = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $conn->query("UPDATE categories SET name='$name', description='$desc' WHERE category_id=$id");
    header("Location: admin_view_categories.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Category</h2>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" value="<?= $category['name'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?= $category['description'] ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="view_categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
