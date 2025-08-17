<<<<<<< HEAD
<?php
/**
 * add_item.php
 * 
 * Allows logged in users to add their own items to the database.
 * 
 * Workflow:
 * - Check if user is logged in, redirect to login if not
 * - For POST requests:
 *   - Check if user has already posted maximum items (2) today
 *   - Insert new item into database if validation passes
 *   - Redirect to dashboard on success or show error message
 *   - Accepts title, description, category, and price
 * 
 * Dependencies:
 * - Requires db.php for database connection
 * - Requires 'items' table with: 'id', 'title', 'description', 'category', 'price', 'posted', and 'posted_by' as it's fields
 * - Requires login.php for unauthorized users
 * - Requires dashboard.php for redirection
 * 
 * @author Team 2
 * @version Phase 2
 */
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

    $stmt = $pdo->prepare("SELECT * FROM items WHERE posted_by = ? AND posted = ?");
    $stmt->execute([$posted_by, $posted]);

    if ($stmt->rowCount() > 1) {
        $message = "Max amount of items added for today: 2/2";
    } else{
        // Get and sanitize item input
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $price = $_POST['price'];
    
        if (!empty($title) && !empty($description) && !empty($category) && !empty($price)) {
            $insert = $pdo->prepare("INSERT INTO items (title, description, category, price, posted, posted_by) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$title, $description, $category, $price, $posted, $posted_by]);
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Please fill all fields!";
        }
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
=======
<?php
/**
 * add_item.php
 * 
 * Allows logged in users to add their own items to the database.
 * 
 * Workflow:
 * - Check if user is logged in, redirect to login if not
 * - For POST requests:
 *   - Check if user has already posted maximum items (2) today
 *   - Insert new item into database if validation passes
 *   - Redirect to dashboard on success or show error message
 *   - Accepts title, description, category, and price
 * 
 * Dependencies:
 * - Requires db.php for database connection
 * - Requires 'items' table with: 'id', 'title', 'description', 'category', 'price', 'posted', and 'posted_by' as it's fields
 * - Requires login.php for unauthorized users
 * - Requires dashboard.php for redirection
 * 
 * @author Team 2
 * @version Phase 2
 */
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

    $stmt = $pdo->prepare("SELECT * FROM items WHERE posted_by = ? AND posted = ?");
    $stmt->execute([$posted_by, $posted]);

    if ($stmt->rowCount() > 1) {
        $message = "Max amount of items added for today: 2/2";
    } else{
        // Get and sanitize item input
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $price = $_POST['price'];
    
        if (!empty($title) && !empty($description) && !empty($category) && !empty($price)) {
            $insert = $pdo->prepare("INSERT INTO items (title, description, category, price, posted, posted_by) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$title, $description, $category, $price, $posted, $posted_by]);
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Please fill all fields!";
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar">
    Welcome to groupTWOS
</div>
<div class="centered-container">

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
</div>
</body>
>>>>>>> 66f2cb8 (Delete old COMP440_2_P2 files)
</html>