<?php
/**
 * login.php
 * 
 * Handles user login by verifying credentials against the database.
 * 
 * Workflow:
 * - Accepts POST requests with username and password.
 * - Checks credentials against the 'user' table.
 * - If valid, starts a session and redirects to dashboard.php.
 * - If invalid, displays an error message.
 * 
 * Dependencies:
 * - Requires db.php for database connection.
 * - Requires a 'user' table with 'username' and 'password' fields.
 * 
 * @author Team 2
 * @version Phase 1
 */

require 'db.php';
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare and execute SQL statement to fetch user by username
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify password and handle login
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar">
    Welcome to groupTWOS
</div>
<div class="centered-container">
    <h2>Login</h2>
    <form method="POST" autocomplete="off">
        <input name="username" required placeholder="Username" autofocus autocomplete="username"><br>
        <input name="password" type="password" required placeholder="Password" autocomplete="current-password"><br>
        <button type="submit">Login</button>
    </form>
    <?php if ($message): ?>
        <p style="color:#ff4444"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <br />
    <a href="signup.php">Sign Up</a>
</div>
</body>
</html>