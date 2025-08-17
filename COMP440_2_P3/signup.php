<?php
/**
 * signup.php
 * 
 * Handles user registration by collecting user details and storing them in the database.
 * 
 * Workflow:
 * - Accepts POST requests with username, password, email, first name, and last name.
 * - Checks if passwords match.
 * - Checks if username or email already exists.
 * - If valid, hashes the password and inserts the new user into the 'user' table.
 * - Redirects to login.php upon successful registration.
 * 
 * Dependencies:
 * - Requires db.php for database connection.
 * - Requires a 'user' table with 'username', 'password', 'firstName', 'lastName', and 'email' fields.
 * 
 * @author Team 2
 * @version Phase 1
 */

require 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user input
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $email = trim($_POST['email']);
    $first = trim($_POST['firstName']);
    $last = trim($_POST['lastName']);

    // Check if passwords match
    if ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $message = "Username or Email already exists.";
        } else {
            // Hash the password and insert new user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO user (username, password, firstName, lastName, email) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$username, $hashed, $first, $last, $email]);
            header("Location: login.php");
            exit();
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
    <h2>Signup</h2>
    <form method="POST">
        <!-- Username input -->
        <input name="username" required placeholder="Username"><br>
        <!-- Password input -->
        <input name="password" type="password" required placeholder="Password"><br>
        <!-- Confirm password input -->
        <input name="confirm" type="password" required placeholder="Confirm Password"><br>
        <!-- First name input -->
        <input name="firstName" placeholder="First Name"><br>
        <!-- Last name input -->
        <input name="lastName" placeholder="Last Name"><br>
        <!-- Email input -->
        <input name="email" type="email" required placeholder="Email"><br>
        <button type="submit">Register</button>
    </form>
    <!-- Display error message if registration fails -->
    <p style="color:red"><?= $message ?></p>
</div>
</body>
</html>