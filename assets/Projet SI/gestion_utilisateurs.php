<?php
session_start();
require_once 'db.php';

// --- SÉCURITÉ : RÉSERVÉ AUX ADMINS ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$user_email = $_SESSION['user_email'];

// --- RÉCUPÉRATION DES UTILISATEURS ---
try {
    // On sélectionne tous les utilisateurs
    $stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY role ASC, email ASC");
    $utilisateurs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_db = "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs</title>
    <style>
        body { font-family: sans-serif; background-color: #f9f9f9; padding: 0; margin: 0; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        
        /* Navigation (identique aux autres pages) */
        nav { background-color: #fff; border-bottom: 1px solid #ddd; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5rem; font-weight: bold; color: #007bff; text-decoration: none; }
        nav .nav-links a { margin-left: 1rem; text-decoration: none; color: #555; }
        nav .nav-links a.button { background-color: #007bff; color: white; padding: 0.5rem 1rem; border-radius: 4px; }
        nav .nav-links a.button-secondary { background-color: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 4px; }

        /* Tableau */
        table { width: 100%; border-collapse: collapse; margin-top: 2rem; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        th, td { padding: 1rem; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; }
        .badge-admin { background-color: #6f42c1; color: white; } /* Violet */
        .badge-organisateur { background-color: #ffc107; color: #333; } /* Jaune */
        .badge-user { background-color: #e2e6ea; color: #333; } /* Gris */
        .delete-btn { color: #dc3545; text-decoration: none; font-weight: bold; border: 1px solid #dc3545; padding: 5px 10px; border-radius: 4px; }
        .delete-btn:hover { background-color: #dc3545; color: white; }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo">Events-Mgt</a>
        <div class="nav-links">
            <a href="dashboard.php">Tableau de Bord</a>
            <a href="gestion_utilisateurs.php" style="font-weight: bold;">Utilisateurs</a>
            <span>Admin: <strong><?php echo htmlspecialchars($user_email); ?></strong></span>
            <a href="logout.php" class="button-secondary">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <h1>Gestion des Utilisateurs</h1>
        <p>Liste de tous les comptes enregistrés sur la plateforme.</p>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <p style="color: green;">Utilisateur supprimé avec succès.</p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $user): ?>
                    <tr>
                        <td><?php echo $user['id_user']; ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                            // Affichage joli du rôle avec des couleurs
                            $class = 'badge-user';
                            if ($user['role'] == 'admin') $class = 'badge-admin';
                            if ($user['role'] == 'organisateur') $class = 'badge-organisateur';
                            ?>
                            <span class="badge <?php echo $class; ?>"><?php echo $user['role']; ?></span>
                        </td>
                        <td>
                            <?php 
                            // On empêche l'admin de se supprimer lui-même !
                            if ($user['id_user'] != $_SESSION['user_id']): 
                            ?>
                                <a href="supprimer_user.php?id=<?php echo $user['id_user']; ?>" 
                                   class="delete-btn"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Tout son historique sera effacé.');">
                                   Supprimer
                                </a>
                            <?php else: ?>
                                <span style="color: #ccc;">(C'est vous)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>