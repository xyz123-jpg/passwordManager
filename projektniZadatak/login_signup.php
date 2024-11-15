<?php
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'login') {
        header("Location: login.php");
        exit();
    } elseif ($action === 'signup') {
        header("Location: signup.php");
        exit();
    } else {
        echo "Invalid action.";
    }
}
?>
