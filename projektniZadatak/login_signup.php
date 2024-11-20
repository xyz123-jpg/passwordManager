<?php
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'login') {
        header("Location: login.php");
        exit();
    } elseif ($action === 'signup') {
        header("Location: signup.php");
        exit();
    } elseif ($action === "admin") {
        header("Location: admin_login.php");
        exit();
    }else {
        echo "Invalid action.";
    }
}
?>
