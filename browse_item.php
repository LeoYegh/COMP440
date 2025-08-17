<<<<<<< HEAD
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
 *   - Good: Finds items with Good or Excellent ratings
 *   - Date: Finds user with most items posted on search date
 *   - Bad: Finds all users who have made a poor review
 *   - quality_posters: Finds all users who have never recieved a poor review
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
 * @version Phase 3
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
        elseif ($_POST['search_type'] == 'good'){
            // find all good or excellent rated items
            $stmt = $pdo->prepare("
                SELECT i.*
                FROM items i
                JOIN reviews r ON i.id = r.item_id
                WHERE r.rating IN ('good', 'excellent')
                GROUP BY i.id
            ");
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $message = "Showing items with good or excellent ratings: ";
        }
        elseif ($_POST['search_type'] == 'date' && !empty($_POST['date'])) {
            $date = $_POST['date'];

            // First find the maximum count of items posted by any user on this date
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as item_count
                FROM items
                WHERE posted = ?
                GROUP BY posted_by
                ORDER BY item_count DESC
                LIMIT 1
            ");
            $stmt->execute([$date]);
            $max_count = $stmt->fetchColumn();

            if ($max_count) {
                // Now find all users who posted this maximum number of items on the date
                $stmt = $pdo->prepare("
                    SELECT posted_by, COUNT(*) as item_count
                    FROM items
                    WHERE posted = ?
                    GROUP BY posted_by
                    HAVING COUNT(*) = ?
                ");
                $stmt->execute([$date, $max_count]);
                $maxUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get all items from these top posting users on this date
                $user_list = array_column($maxUsers, 'posted_by');
                $placeholders = implode(',', array_fill(0, count($user_list), '?'));
        
                $query = "
                    SELECT * 
                    FROM items 
                    WHERE posted = ? 
                    AND posted_by IN ($placeholders)
                    ORDER BY posted_by, id
                ";
                $params = array_merge([$date], $user_list);
        
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $message = "Found ".count($maxUsers)." user(s) with the most items (".$max_count.") posted on ".htmlspecialchars($date);
                } else {
                    $items = [];
                    $message = "No items found posted on ".htmlspecialchars($date);
                }
        }
        elseif ($_POST['search_type'] == 'bad'){
            // find user poor reviews
            $stmt = $pdo->prepare("
                SELECT DISTINCT r.posted_by
                FROM reviews r
                WHERE r.rating = 'poor'
            ");
            $stmt->execute();
            $poorRaters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($poorRaters)) {
                // Get all items these users rated as poor
                $user_list = array_column($poorRaters, 'posted_by');
                $placeholders = implode(',', array_fill(0, count($user_list), '?'));
                
                $query = "
                    SELECT i.*, r.rating, r.description as review_description
                    FROM items i
                    JOIN reviews r ON i.id = r.item_id
                    WHERE r.rating = 'poor'
                    AND r.posted_by IN ($placeholders)
                    ORDER BY r.posted_by, i.posted DESC
                ";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($user_list);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $message = "Found ".count($poorRaters)." user(s) who gave poor ratings";
            } else {
                $items = [];
                $message = "No users found who gave poor ratings";
            }
        }
        elseif ($_POST['search_type'] == 'quality_posters') {
            // Find users who have never had any poor reviews on any of their items
            $stmt = $pdo->prepare("
                SELECT DISTINCT i.posted_by
                FROM items i
                WHERE i.posted_by NOT IN (
                    SELECT DISTINCT i2.posted_by
                    FROM items i2
                    JOIN reviews r ON i2.id = r.item_id
                    WHERE r.rating = 'poor'
                )
                ORDER BY i.posted_by
            ");
            $stmt->execute();
            $qualityPosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($qualityPosters)) {
                // Get all items from these quality posters
                $user_list = array_column($qualityPosters, 'posted_by');
                $placeholders = implode(',', array_fill(0, count($user_list), '?'));
    
                $query = "SELECT * FROM items WHERE posted_by IN ($placeholders) ORDER BY posted_by, price DESC";
                $stmt = $pdo->prepare($query);
                $stmt->execute($user_list);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                $message = "Showing ".count($items)." items from ".count($qualityPosters)." quality user(s)";
            } else {
                $items = [];
                $message = "No users with items that have never received poor reviews";
            }
        }
    }
}
?>
<html>
<body>
    <a href="dashboard.php">Dashboard</a>
    <br /><br />
    
    <form method="POST">
        <!-- Category search -->
        <input name="category" placeholder="Category">
        <button type="submit" name="search_type" value="category">Category Search</button>

        <br><br>
        <!-- Dual category search -->
        <input name="category_x" placeholder="Category X">
        <input name="category_y" placeholder="Category Y">
        <button type="submit" name="search_type" value="dual">Find Users</button>

        <br><br>
        <!-- Excellent or Good reviewed items -->
         <button type="submit" name="search_type" value="good">Highly Rated Items</button> 

        <br><br>
        <!-- Date Most Search -->
        <input name="date" placeholder="Date: Ex.(2025-08-16)">
        <button type="submit" name="search_type" value="date">Find Users with Most Posted On</button>
        
        <br><br>
        <!-- Most Poor Reviews -->
         <button type="submit" name="search_type" value="bad">Hated Reviews</button> 

        <br><br>
        <!-- Quality Posters Search -->
        <button type="submit" name="search_type" value="quality_posters">Find Quality Posters</button>
    </form>

    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <h3>Users who posted both categories on the same day:</h3>
        <ul>
            <?php foreach ($users as $user): ?>
                <li><?= htmlspecialchars($user['posted_by']) ?> (on <?= htmlspecialchars($user['post_date']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($maxUsers)): ?>
        <h3>Users who posted the most on <?= htmlspecialchars($date) ?>:</h3>
        <ul>
            <?php foreach ($maxUsers as $user): ?>
                <li><?= htmlspecialchars($user['posted_by']) ?> (<?= $user['item_count'] ?> items)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($poorRaters)): ?>
        <h3>Users who gave poor ratings:</h3>
        <ul>
            <?php foreach ($poorRaters as $rater): ?>
                <li><?= htmlspecialchars($rater['posted_by']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($qualityPosters)): ?>
        <h3>Users whose items have never received poor ratings:</h3>
        <ul>
            <?php foreach ($qualityPosters as $poster): ?>
                <li><?= htmlspecialchars($poster['posted_by']) ?></li>
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
=======
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
 *   - Good: Finds items with Good or Excellent ratings
 *   - Date: Finds user with most items posted on search date
 *   - Bad: Finds all users who have made a poor review
 *   - quality_posters: Finds all users who have never recieved a poor review
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
 * @version Phase 3
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
        elseif ($_POST['search_type'] == 'good'){
            // find all good or excellent rated items
            $stmt = $pdo->prepare("
                SELECT i.*
                FROM items i
                JOIN reviews r ON i.id = r.item_id
                WHERE r.rating IN ('good', 'excellent')
                GROUP BY i.id
            ");
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $message = "Showing items with good or excellent ratings: ";
        }
        elseif ($_POST['search_type'] == 'date' && !empty($_POST['date'])) {
            $date = $_POST['date'];

            // First find the maximum count of items posted by any user on this date
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as item_count
                FROM items
                WHERE posted = ?
                GROUP BY posted_by
                ORDER BY item_count DESC
                LIMIT 1
            ");
            $stmt->execute([$date]);
            $max_count = $stmt->fetchColumn();

            if ($max_count) {
                // Now find all users who posted this maximum number of items on the date
                $stmt = $pdo->prepare("
                    SELECT posted_by, COUNT(*) as item_count
                    FROM items
                    WHERE posted = ?
                    GROUP BY posted_by
                    HAVING COUNT(*) = ?
                ");
                $stmt->execute([$date, $max_count]);
                $maxUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get all items from these top posting users on this date
                $user_list = array_column($maxUsers, 'posted_by');
                $placeholders = implode(',', array_fill(0, count($user_list), '?'));
        
                $query = "
                    SELECT * 
                    FROM items 
                    WHERE posted = ? 
                    AND posted_by IN ($placeholders)
                    ORDER BY posted_by, id
                ";
                $params = array_merge([$date], $user_list);
        
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $message = "Found ".count($maxUsers)." user(s) with the most items (".$max_count.") posted on ".htmlspecialchars($date);
                } else {
                    $items = [];
                    $message = "No items found posted on ".htmlspecialchars($date);
                }
        }
        elseif ($_POST['search_type'] == 'bad'){
            // find user poor reviews
            $stmt = $pdo->prepare("
                SELECT DISTINCT r.posted_by
                FROM reviews r
                WHERE r.rating = 'poor'
            ");
            $stmt->execute();
            $poorRaters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($poorRaters)) {
                // Get all items these users rated as poor
                $user_list = array_column($poorRaters, 'posted_by');
                $placeholders = implode(',', array_fill(0, count($user_list), '?'));
                
                $query = "
                    SELECT i.*, r.rating, r.description as review_description
                    FROM items i
                    JOIN reviews r ON i.id = r.item_id
                    WHERE r.rating = 'poor'
                    AND r.posted_by IN ($placeholders)
                    ORDER BY r.posted_by, i.posted DESC
                ";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($user_list);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $message = "Found ".count($poorRaters)." user(s) who gave poor ratings";
            } else {
                $items = [];
                $message = "No users found who gave poor ratings";
            }
        }
        elseif ($_POST['search_type'] == 'quality_posters') {
            // Find users who have never had any poor reviews on any of their items
            $stmt = $pdo->prepare("
                SELECT DISTINCT i.posted_by
                FROM items i
                WHERE i.posted_by NOT IN (
                    SELECT DISTINCT i2.posted_by
                    FROM items i2
                    JOIN reviews r ON i2.id = r.item_id
                    WHERE r.rating = 'poor'
                )
                ORDER BY i.posted_by
            ");
            $stmt->execute();
            $qualityPosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($qualityPosters)) {
                // Get all items from these quality posters
                $user_list = array_column($qualityPosters, 'posted_by');
                $placeholders = implode(',', array_fill(0, count($user_list), '?'));
    
                $query = "SELECT * FROM items WHERE posted_by IN ($placeholders) ORDER BY posted_by, price DESC";
                $stmt = $pdo->prepare($query);
                $stmt->execute($user_list);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                $message = "Showing ".count($items)." items from ".count($qualityPosters)." quality user(s)";
            } else {
                $items = [];
                $message = "No users with items that have never received poor reviews";
            }
        }
    }
}
?>
<html>
<head>
    <title>Page Title</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar">
    Welcome to groupTWOS
</div>

<!-- Remove empty centered-container, move form and messages outside -->
<div style="width:100%; max-width:350px; margin:100px auto 40px auto; text-align:center;">
    <a href="dashboard.php">Dashboard</a>
    <br /><br />
    
    <form method="POST">
        <!-- Category search -->
        <input name="category" placeholder="Category">
        <button type="submit" name="search_type" value="category">Category Search</button>

        <br><br>
        <!-- Dual category search -->
        <input name="category_x" placeholder="Category X">
        <input name="category_y" placeholder="Category Y">
        <button type="submit" name="search_type" value="dual">Find Users</button>

        <br><br>
        <!-- Excellent or Good reviewed items -->
         <button type="submit" name="search_type" value="good">Highly Rated Items</button> 

        <br><br>
        <!-- Date Most Search -->
        <input name="date" placeholder="Date: Ex.(2025-08-16)">
        <button type="submit" name="search_type" value="date">Find Users with Most Posted On</button>
        
        <br><br>
        <!-- Most Poor Reviews -->
         <button type="submit" name="search_type" value="bad">Hated Reviews</button> 

        <br><br>
        <!-- Quality Posters Search -->
        <button type="submit" name="search_type" value="quality_posters">Find Quality Posters</button>
    </form>

    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <h3>Users who posted both categories on the same day:</h3>
        <ul>
            <?php foreach ($users as $user): ?>
                <li><?= htmlspecialchars($user['posted_by']) ?> (on <?= htmlspecialchars($user['post_date']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($maxUsers)): ?>
        <h3>Users who posted the most on <?= htmlspecialchars($date) ?>:</h3>
        <ul>
            <?php foreach ($maxUsers as $user): ?>
                <li><?= htmlspecialchars($user['posted_by']) ?> (<?= $user['item_count'] ?> items)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($poorRaters)): ?>
        <h3>Users who gave poor ratings:</h3>
        <ul>
            <?php foreach ($poorRaters as $rater): ?>
                <li><?= htmlspecialchars($rater['posted_by']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($qualityPosters)): ?>
        <h3>Users whose items have never received poor ratings:</h3>
        <ul>
            <?php foreach ($qualityPosters as $poster): ?>
                <li><?= htmlspecialchars($poster['posted_by']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<div class="table-container">
    <table>
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
                <tr onclick="window.location='item_detail.php?id=<?= $item['id'] ?>'">
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
</div>
</body>
>>>>>>> 66f2cb8 (Delete old COMP440_2_P2 files)
</html>