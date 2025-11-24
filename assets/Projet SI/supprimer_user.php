<?php
session_start();
require_once 'db.php';

// 1. SÉCURITÉ : Seul l'admin peut passer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// 2. Vérifier l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: gestion_utilisateurs.php');
    exit;
}

$id_to_delete = $_GET['id'];

// 3. SÉCURITÉ ULTIME : Ne pas se supprimer soi-même
if ($id_to_delete == $_SESSION['user_id']) {
    die("Vous ne pouvez pas supprimer votre propre compte admin ici.");
}

try {
    // 4. Suppression
    // Grâce aux contraintes "ON DELETE CASCADE" que nous avons mises dans le SQL au début,
    // supprimer l'utilisateur supprimera automatiquement ses événements et inscriptions !
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id_user = ?");
    $stmt->execute([$id_to_delete]);

    header('Location: gestion_utilisateurs.php?status=deleted');
    exit;

} catch (PDOException $e) {
    echo "Erreur lors de la suppression : " . $e->getMessage();
}
?>