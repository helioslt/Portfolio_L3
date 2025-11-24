<?php
// On démarre la session
session_start();

// On inclut la connexion à la BDD
require_once 'db.php';

// --- SÉCURITÉ ---
// 1. On vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si non, redirection vers la page de connexion
    header('Location: login.php');
    exit;
}

// 2. On vérifie si un ID d'événement a été passé dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Si non, redirection vers l'accueil
    header('Location: index.php?status=missing_id');
    exit;
}

// --- TRAITEMENT DE L'INSCRIPTION ---
$id_event = $_GET['id'];
$id_user = $_SESSION['user_id'];

try {
    // 3. On vérifie si l'utilisateur est DÉJÀ inscrit à cet événement
    $stmt_check = $pdo->prepare("SELECT * FROM inscriptions WHERE id_user = ? AND id_event = ?");
    $stmt_check->execute([$id_user, $id_event]);
    $existing_inscription = $stmt_check->fetch();

    if ($existing_inscription) {
        // L'utilisateur est déjà inscrit, on le redirige
        header('Location: index.php?status=already_registered');
        exit;
    }

    // 4. L'utilisateur n'est pas encore inscrit, on l'ajoute
    $stmt_insert = $pdo->prepare("INSERT INTO inscriptions (id_user, id_event) VALUES (?, ?)");
    $stmt_insert->execute([$id_user, $id_event]);

    // 5. On redirige vers l'accueil avec un message de succès
    header('Location: index.php?status=registration_success');
    exit;

} catch (PDOException $e) {
    // En cas d'erreur BDD (ex: l'événement n'existe pas), on redirige avec une erreur
    header('Location: index.php?status=error');
    exit;
}
?>
