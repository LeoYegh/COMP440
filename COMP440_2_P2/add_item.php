<?php

require 'db.php';
session_start();

$message = '';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted = date('Y-m-d');
    $posted_by = $_SESSION['user'];

    // Get and sanitize item input
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    
    if (!empty($title) && !empty($description) && !empty($category) && !empty($price)) {
        $insert = $pdo->prepare("INSERT INTO items (title, description, category, price, posted, posted_by) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$title, $description, $category, $price, $posted, $posted_by]);
        $message = "Item added successfully!";
    } else {
        $message = "Please fill all fields!";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Item</title>
</head>
<body>

<form method="POST">
    <!-- title input -->
    <input type="text" name="title" required placeholder="title"><br>
    <!-- description input -->
    <input type="text" name="description" required placeholder="description"><br>
    <!-- category input -->
    <input type="text" name="category" required placeholder="category"><br>
    <!-- price input -->
    <input type="text" name="price" required placeholder="price"><br>
    <button type="submit">Add</button>
</form>

<p style="color:red"><?= $message ?></p>
<a href="dashboard.php">Dashboard</a>
</body>
</html>