<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'organisateur')) {
    header('Location: index.php');
    exit;
}

$id_user = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

$evenements_a_gerer = [];
try {
    if ($user_role == 'admin') {
        $stmt = $pdo->query("SELECT * FROM evenements ORDER BY date_event DESC");
    } else {
        $stmt = $pdo->prepare("
            SELECT E.* FROM evenements AS E
            JOIN organisateurs_evenements AS O ON E.id_event = O.id_event
            WHERE O.id_user = ?
            ORDER BY E.date_event DESC
        ");
        $stmt->execute([$id_user]);
    }
    $evenements_a_gerer = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_db = "Erreur lors de la récupération des événements : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        nav { background-color: #fff; border-bottom: 1px solid #ddd; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5rem; font-weight: bold; color: #007bff; text-decoration: none; }
        nav .nav-links a { margin-left: 1rem; text-decoration: none; color: #555; }
        nav .nav-links a.button-secondary { background-color: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 4px; }
        .db-error { color: #c62828; }
        
        /* Style pour la liste de gestion */
        table { width: 100%; border-collapse: collapse; margin-top: 2rem; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        th, td { padding: 1rem; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .actions a { margin-right: 10px; text-decoration: none; }
        .actions .edit { color: #007bff; }
        .actions .delete { color: #dc3545; }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo">Events-Mgt</a>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="mon_profil.php">Mon Profil</a>
            <a href="dashboard.php" style="font-weight: bold;">Tableau de Bord</a>
            
            <?php if ($user_role == 'admin'): ?>
                <a href="gestion_utilisateurs.php" style="color: #6f42c1; font-weight: bold;">Gérer Utilisateurs</a>
            <?php endif; ?>
            
            <a href="creer_event.php" style="font-weight:bold; color: #28a745;">+ Créer</a>
            <a href="logout.php" class="button-secondary">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <h1>Tableau de Bord (Gestion)</h1>
        <p>Gérez ici les événements que vous avez créés.</p>

        <?php if (isset($error_db)): ?>
            <p class="db-error"><?php echo htmlspecialchars($error_db); ?></p>
        <?php elseif (empty($evenements_a_gerer)): ?>
            <p>Vous n'avez aucun événement à gérer pour le moment.</p>
            <a href="creer_event.php">Créer votre premier événement</a>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Titre de l'événement</th>
                        <th>Date</th>
                        <th>Lieu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evenements_a_gerer as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['titre']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($event['date_event']))); ?></td>
                            <td><?php echo htmlspecialchars($event['lieu']); ?></td>
                            <td class="actions">
                                <a href="modifier_event.php?id=<?php echo $event['id_event']; ?>" class="edit">Modifier</a>
                                <a href="supprimer_event.php?id=<?php echo $event['id_event']; ?>" class="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>