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
<p>You have successfully logged in.</p>
<a href="login.php">Logout</a>
</body>
</html>