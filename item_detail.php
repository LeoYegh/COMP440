<<<<<<< HEAD
<?php
/**
 * item_detail.php
 * 
 * Allows logged in users to make a review on an item.
 * 
 * Workflow:
 * - Check if user is logged in, redirect to login if not
 * - Check for item ID in GET request and store in session
 * - For POST requests:
 *   - Validate user isn't reviewing their own item
 *   - Check user hasn't already reviewed this item
 *   - Verify user hasn't exceeded daily review limit (2)
 *   - Process valid review submissions
 *   - Redirect to dashboard after successful submission
 * - Display review form with error messages as needed
 * 
 * Dependencies:
 * - Requires db.php for database connection
 * - Requires 'items' table with: 'id', 'title', 'description', 'category', 'price', 'posted', and 'posted_by' as it's fields
 * - Requires 'reviews' table with: 'item_id', 'rating', 'description', 'posted', and 'posted_by' as it's fields
 * - Requires login.php for unauthorized users
 * - Requires dashboard.php for redirection
 *
 *
 * @author Team 2
 * @version Phase 2
 */
require 'db.php';
session_start();

$message = '';
    
if (isset($_GET['id'])) {
    $_SESSION['item_id'] = (int)$_GET['id']; // Save ID in session
    header("Location: item_detail.php");     // Redirect to detail page
    exit();
}

$item_id = $_SESSION['item_id'];
$posted_by = $_SESSION['user'];
$posted = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // checking if reviewing own item
    $stmt = $pdo->prepare("SELECT * FROM items WHERE posted_by = ? AND id = ?");
    $stmt->execute([$posted_by, $item_id]);
    if ($stmt->rowCount() > 0) {
        $message = "Can't review own item.";
    } else{
        // checking if already reviewed item
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE posted_by = ? AND item_id = ?");
        $stmt->execute([$posted_by, $item_id]);
        if ($stmt->rowCount() > 0) {
            $message = "Can only review once per item.";
        } else{
            // checking if already reviewed twice today
            $stmt = $pdo->prepare("SELECT * FROM reviews WHERE posted_by = ? AND posted = ?");
            $stmt->execute([$posted_by, $posted]);
            if ($stmt->rowCount() > 1) {
            $message = "Max amount of reviews added for today: 2/2";
        } else{
            // Get and sanitize item input
            $rating = $_POST['rating'];
            $description = $_POST['description'];
    
            if (!empty($rating) && !empty($description)) {
                $insert = $pdo->prepare("INSERT INTO reviews (item_id, rating, description, posted, posted_by) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$item_id, $rating, $description, $posted, $posted_by]);
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Please fill all fields!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Add Review</title>
    </head>
    <body>
        <form method="POST">
            <!-- rating input -->
            <br>
            <label for="rating">Rating:</label>
            <select name="rating">
                <option value="excellent">excellent</option>
                <option value="good">good</option>
                <option value="fair">fair</option>
                <option value="poor">poor</option>
            </select>
            <br>
            <!-- description input -->
            <input type="text" name="description" required placeholder="description"><br>
            <br>
            <button type="submit">Add</button>
        </form>

        <p style="color:red"><?= $message ?></p>
        <a href="dashboard.php">Dashboard</a>
    </body>
=======
<?php
/**
 * item_detail.php
 * 
 * Allows logged in users to make a review on an item.
 * 
 * Workflow:
 * - Check if user is logged in, redirect to login if not
 * - Check for item ID in GET request and store in session
 * - For POST requests:
 *   - Validate user isn't reviewing their own item
 *   - Check user hasn't already reviewed this item
 *   - Verify user hasn't exceeded daily review limit (2)
 *   - Process valid review submissions
 *   - Redirect to dashboard after successful submission
 * - Display review form with error messages as needed
 * 
 * Dependencies:
 * - Requires db.php for database connection
 * - Requires 'items' table with: 'id', 'title', 'description', 'category', 'price', 'posted', and 'posted_by' as it's fields
 * - Requires 'reviews' table with: 'item_id', 'rating', 'description', 'posted', and 'posted_by' as it's fields
 * - Requires login.php for unauthorized users
 * - Requires dashboard.php for redirection
 *
 *
 * @author Team 2
 * @version Phase 2
 */
require 'db.php';
session_start();

$message = '';
    
if (isset($_GET['id'])) {
    $_SESSION['item_id'] = (int)$_GET['id']; // Save ID in session
    header("Location: item_detail.php");     // Redirect to detail page
    exit();
}

$item_id = $_SESSION['item_id'];
$posted_by = $_SESSION['user'];
$posted = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // checking if reviewing own item
    $stmt = $pdo->prepare("SELECT * FROM items WHERE posted_by = ? AND id = ?");
    $stmt->execute([$posted_by, $item_id]);
    if ($stmt->rowCount() > 0) {
        $message = "Can't review own item.";
    } else{
        // checking if already reviewed item
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE posted_by = ? AND item_id = ?");
        $stmt->execute([$posted_by, $item_id]);
        if ($stmt->rowCount() > 0) {
            $message = "Can only review once per item.";
        } else{
            // checking if already reviewed twice today
            $stmt = $pdo->prepare("SELECT * FROM reviews WHERE posted_by = ? AND posted = ?");
            $stmt->execute([$posted_by, $posted]);
            if ($stmt->rowCount() > 1) {
            $message = "Max amount of reviews added for today: 2/2";
        } else{
            // Get and sanitize item input
            $rating = $_POST['rating'];
            $description = $_POST['description'];
    
            if (!empty($rating) && !empty($description)) {
                $insert = $pdo->prepare("INSERT INTO reviews (item_id, rating, description, posted, posted_by) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$item_id, $rating, $description, $posted, $posted_by]);
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Please fill all fields!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Add Review</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <div class="top-bar">
        Welcome to groupTWOS
    </div>
    <div class="centered-container">
        <form method="POST">
            <!-- rating input -->
            <br>
            <label for="rating">Rating:</label>
            <select name="rating">
                <option value="excellent">excellent</option>
                <option value="good">good</option>
                <option value="fair">fair</option>
                <option value="poor">poor</option>
            </select>
            <br>
            <!-- description input -->
            <input type="text" name="description" required placeholder="description"><br>
            <br>
            <button type="submit">Add</button>
        </form>

        <p style="color:red"><?= $message ?></p>
        <a href="dashboard.php">Dashboard</a>
    </div>
    </body>
>>>>>>> 66f2cb8 (Delete old COMP440_2_P2 files)
</html>