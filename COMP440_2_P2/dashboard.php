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
</body>
</html>