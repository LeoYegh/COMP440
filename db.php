<?php
$host = 'localhost';
$db   = 'OnlineStore';
$user = 'root'; // your MySQL username
$pass = '';     // your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
<<<<<<< HEAD
?>
=======
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar">
    Welcome to groupTWOS
</div>
<div class="centered-container">
    <!-- Your page content or form here -->
</div>
</body>
</html>
>>>>>>> 66f2cb8 (Delete old COMP440_2_P2 files)
