<?php
/**
 * browse_item.php
 * 
 * Displays a table filled with item details.
 * 
 * Provides item search functionality with two modes:
 * - Category search: Finds items in a specific category
 * - Dual category search: Finds users who posted in two different categories on the same day
 * 
 * Workflow:
 * - Check if user is logged in, redirect to login if not
 * - Load all items by default and when text fields are empty
 * - For POST requests:
 *   - Category search: Filter items by single category (price descending)
 *   - Dual category search: Find users who posted in both categories on same day
 *     - Retrieve all items from these users on matching dates
 * - Display search results in a table with clickable rows that redirect to have items rated by user
 * 
 * Dependencies:
 * - Requires db.php for database connection
 * - Requires 'items' table with: 'id', 'title', 'description', 'category', 'price', 'posted', and 'posted_by' as it's fields
 * - Requires login.php for unauthorized users
 * - Requires dashboard.php for redirection
 * - Requires item_detail.php for item ratings
 *
 * @author Team 2
 * @version Phase 2
 */
require 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$users = [];
$items = $pdo->query("SELECT * FROM items")->fetchAll(PDO::FETCH_ASSOC);
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search_type'])) {
        if ($_POST['search_type'] == 'category' && !empty($_POST['category'])) {
            // Existing category search
            $category = $_POST['category'];
            $stmt = $pdo->prepare("SELECT * FROM items WHERE category = ? ORDER BY price DESC");
            $stmt->execute([$category]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $message = "Showing items in category: " . htmlspecialchars($category);
        } 
        elseif ($_POST['search_type'] == 'dual' && !empty($_POST['category_x']) && !empty($_POST['category_y'])) {
            $categoryX = $_POST['category_x'];
            $categoryY = $_POST['category_y'];

            // Find users who posted in both categories on the same day
            $query = "
                SELECT posted_by, DATE(posted) as post_date
                FROM items
                WHERE category IN (?, ?)
                GROUP BY posted_by, DATE(posted)
                HAVING COUNT(DISTINCT category) >= 2
            ";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$categoryX, $categoryY]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($users)) {
                // Get all items from these users on these dates
                $userDates = [];
                foreach ($users as $user) {
                    $userDates[] = "(posted_by = '".$user['posted_by']."' AND DATE(posted) = '".$user['post_date']."')";
                }

                $query = "SELECT * FROM items WHERE " . implode(" OR ", $userDates) . " ORDER BY posted_by, posted DESC";
                $items = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                $message = "Found ".count($users)." user(s) who posted in both categories on the same day";
            } else {
                $items = [];
                $message = "No users found who posted in both categories on the same day";
            }
        }
    }
}
?>
<html>
<body>
    <a href="dashboard.php">Dashboard</a>
    <br /><br />
    
    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <!-- Category search -->
        <input name="category" placeholder="Category">
        <button type="submit" name="search_type" value="category">Category Search</button>

        <br><br>
        <!-- Dual category search -->
        <input name="category_x" placeholder="Category X">
        <input name="category_y" placeholder="Category Y">
        <button type="submit" name="search_type" value="dual">Find Users</button>
    </form>

    <?php if (!empty($users)): ?>
        <h3>Users who posted both categories on the same day:</h3>
        <ul>
            <?php foreach ($users as $user): ?>
                <li><?= htmlspecialchars($user['posted_by']) ?> (on <?= htmlspecialchars($user['post_date']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

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
</body>
</html>