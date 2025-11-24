<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mon_profil.php?status=error');
    exit;
}

$id_event = $_GET['id'];
$id_user = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id_user = ? AND id_event = ?");
    $stmt->execute([$id_user, $id_event]);

    header("Location: mon_profil.php?status=unsubscribed");
    exit;

} catch (PDOException $e) {
    header("Location: mon_profil.php?status=error");
    exit;
}
?>