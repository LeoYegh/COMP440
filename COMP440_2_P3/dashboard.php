<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<<<<<<< HEAD
    <title>Dashboard</title>
</head>
<body>
<h2>Welcome, <?= $_SESSION['user'] ?>!</h2>

<a href="add_item.php">Add Item</a>
<br />
<br />

<a href="browse_item.php">Search Items</a>
<br />
<br />

<a href="login.php">Logout</a>
=======
    <title>Page Title</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar">
    Welcome to groupTWOS
</div>
<div class="centered-container">
    <h2>Welcome, <?= $_SESSION['user'] ?>!</h2>

    <a href="add_item.php">Add Item</a>
    <br />
    <br />

    <a href="browse_item.php">Search Items</a>
    <br />
    <br />

    <a href="login.php">Logout</a>
</div>
>>>>>>> 66f2cb8 (Delete old COMP440_2_P2 files)
</body>
</html>