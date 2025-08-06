<?php
    if (isset($_GET['id'])) {
    $_SESSION['item_id'] = (int)$_GET['id']; // Save ID in session
    header("Location: item_detail.php");     // Redirect to detail page
    exit();
}
?>
