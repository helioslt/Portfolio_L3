<?php
session_start();

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id_user = $_SESSION['user_id'];
$is_logged_in = true; 
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

$mes_inscriptions = [];
try {
    $stmt = $pdo->prepare("
        SELECT E.* FROM evenements AS E
        JOIN inscriptions AS I ON E.id_event = I.id_event
        WHERE I.id_user = ?
        ORDER BY E.date_event ASC
    ");
    
    $stmt->execute([$id_user]);
    
    $mes_inscriptions = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_db = "Erreur lors de la récupération de vos inscriptions : " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Mes Inscriptions</title>
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
        .event-card h2 { margin-top: 0; }
        .event-card .meta { color: #555; margin-bottom: 1rem; }
        .db-error { background-color: #ffebee; color: #c62828; padding: 1rem; border-radius: 4px; }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo">Events-Mgt</a>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="mon_profil.php" style="font-weight: bold;">Mon Profil</a>
            
            <span>Bonjour, <strong><?php echo htmlspecialchars($user_email); ?></strong></span>
            
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
        </div>
    </nav>

    <div class="container">
        <h1>Mes Inscriptions</h1>
        <p>Voici la liste des événements auxquels vous êtes inscrit.</p>

        <?php
        if (isset($error_db)):
        ?>
            <p class="db-error"><?php echo htmlspecialchars($error_db); ?></p>
        <?php
        elseif (empty($mes_inscriptions)):
        ?>
            <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
            <a href="index.php">Voir la liste des événements disponibles</a>
        <?php
        else:
        ?>
            <div class="event-list">
                <?php foreach ($mes_inscriptions as $event): ?>
                    <div class="event-card">
                        <h2><?php echo htmlspecialchars($event['titre']); ?></h2>
                        <div class="meta">
                            <span>🗓️ <?php echo htmlspecialchars(date('d/m/Y \à H:i', strtotime($event['date_event']))); ?></span>
                            <span>📍 <?php echo htmlspecialchars($event['lieu']); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

                        <a href="desinscription_event.php?id=<?php echo $event['id_event']; ?>" 
                            style="color: #dc3545; text-decoration: none; font-weight: bold; border: 1px solid #dc3545; padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 10px;"
                            onclick="return confirm('Êtes-vous sûr de vouloir vous désinscrire de cet événement ?');">
                            Se désinscrire ❌
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php
        endif;
        ?>
    </div>

</body>
</html>