<?php

require 'db.php';
session_start();

$message = '';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM items");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user input
    $category = $_POST['category'];

    // Prepare and execute SQL statement to fetch user by username
    $stmt = $pdo->prepare("SELECT * FROM items WHERE category = ?");
    $stmt->execute([$category]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<html>
    <body>
        <a href="dashboard.php">Dashboard</a>
        <br />
        <br />
        <form method="POST">
            <!-- category input -->
            <input name="category" required placeholder="category"><br><br>
            <button type="submit">Search</button>
        </form>
    </body>
    <table border="1" cellspacing="10" cellpadding="8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Category</th>
                <th>Price</th>
                <th>Posted On</th>
                <th>Posted By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <tr style="cursor: pointer;" onclick="window.location='item_detail.php?id=<?= $item['id'] ?>'">
                    <td><?= htmlspecialchars($item['id']) ?></td>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td>$<?= htmlspecialchars($item['price']) ?></td>
                    <td><?= htmlspecialchars($item['posted']) ?></td>
                    <td><?= htmlspecialchars($item['posted_by']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</html>