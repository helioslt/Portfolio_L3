<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id_user = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php?status=error');
    exit;
}

$id_event = $_GET['id'];

try {
    $can_delete = false;

    if ($user_role == 'admin') {
        $can_delete = true;
    } else if ($user_role == 'organisateur') {
        $stmt_check = $pdo->prepare("SELECT * FROM organisateurs_evenements WHERE id_user = ? AND id_event = ?");
        $stmt_check->execute([$id_user, $id_event]);
        if ($stmt_check->fetch()) {
            $can_delete = true;
        }
    }

    if ($can_delete) {
        $stmt_delete = $pdo->prepare("DELETE FROM evenements WHERE id_event = ?");
        $stmt_delete->execute([$id_event]);
        header("Location: dashboard.php?status=event_deleted");
        exit;
    } else {
        header("Location: dashboard.php?status=no_permission");
        exit;
    }

} catch (PDOException $e) {
    header("Location: dashboard.php?status=error");
    exit;
}
?>