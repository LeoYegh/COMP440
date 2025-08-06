<?php
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
</html>