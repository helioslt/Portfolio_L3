<?php
session_start();

require_once 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? 'guest';

try {
    $stmt = $pdo->query("SELECT * FROM evenements ORDER BY date_event DESC");
    $evenements = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_db = "Erreur lors de la récupération des événements : " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Gestion d'Événements</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        
        nav { background-color: #fff; border-bottom: 1px solid #ddd; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5rem; font-weight: bold; color: #007bff; text-decoration: none; }
        nav .nav-links a { margin-left: 1rem; text-decoration: none; color: #555; }
        nav .nav-links a.button { background-color: #007bff; color: white; padding: 0.5rem 1rem; border-radius: 4px; }
        nav .nav-links a.button-secondary { background-color: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 4px; }
        
        .event-list { margin-top: 2rem; }
        .event-card { background-color: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 1.5rem; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .event-card h2 { margin-top: 0; color: #333; }
        .event-card .meta { color: #555; margin-bottom: 1rem; }
        .event-card .meta span { margin-right: 1.5rem; }
        .event-card p { color: #444; line-height: 1.6; }
        .event-card .action-link { display: inline-block; margin-top: 1rem; background-color: #28a745; color: white; padding: 0.6rem 1.2rem; border-radius: 4px; text-decoration: none; }
        
        .db-error { background-color: #ffebee; color: #c62828; padding: 1rem; border-radius: 4px; }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo">Events-Mgt</a>
        <div class="nav-links">
            <?php if ($is_logged_in): ?>
                
                <span>Bonjour, <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong></span>
                
                <?php
                if ($user_role == 'admin' || $user_role == 'organisateur'):
                ?>
                    <a href="dashboard.php">Tableau de Bord</a>
                    <?php if ($user_role == 'admin'): ?>
                        <a href="gestion_utilisateurs.php" style="color: #6f42c1; font-weight: bold;">Gérer Utilisateurs</a>
                    <?php endif; ?>
                    <a href="creer_event.php" class="button">Créer un événement</a>
                <?php endif; ?>

                <a href="logout.php" class="button-secondary">Déconnexion</a>
                
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="inscription.php" class="button">Inscription</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1>Événements à venir</h1>

        <?php
        if (isset($error_db)):
        ?>
            <p class="db-error"><?php echo htmlspecialchars($error_db); ?></p>
        <?php
        elseif (empty($evenements)):
        ?>
            <p>Il n'y a aucun événement prévu pour le moment.</p>
        <?php
        else:
        ?>
            <div class="event-list">
                <?php foreach ($evenements as $event): ?>
                    <div class="event-card">
                        <h2><?php echo htmlspecialchars($event['titre']); ?></h2>
                        <div class="meta">
                            <span>🗓️ <?php echo htmlspecialchars(date('d/m/Y \à H:i', strtotime($event['date_event']))); ?></span>
                            <span>📍 <?php echo htmlspecialchars($event['lieu']); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        
                        <?php
                        if ($is_logged_in): 
                        ?>
                            <a href="inscription_event.php?id=<?php echo $event['id_event']; ?>" class="action-link">S'inscrire à cet événement</a>
                        <?php 
                        endif; 
                        ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php
        endif;
        ?>
    </div>

</body>
</html>

