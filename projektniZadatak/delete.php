<?php
include 'db.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$id = $_GET["id"];
$stmt = $conn->prepare("DELETE FROM passwords WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION["user_id"]);
$stmt->execute();

header("Location: dashboard.php");
?>
