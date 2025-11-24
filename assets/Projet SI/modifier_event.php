<?php
session_start();

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id_user = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

$event = null;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_event = $_POST['id_event'];
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $date_event = $_POST['date_event'];
    $lieu = trim($_POST['lieu']);

    if (empty($titre)) { $errors[] = "Le titre est obligatoire."; }
    if (empty($date_event)) { $errors[] = "La date est obligatoire."; }
    if (empty($lieu)) { $errors[] = "Le lieu est obligatoire."; }

    if (empty($errors)) {
        try {
            $can_edit = false;
            if ($user_role == 'admin') {
                $can_edit = true;
            } else {
                $stmt_check = $pdo->prepare("SELECT * FROM organisateurs_evenements WHERE id_user = ? AND id_event = ?");
                $stmt_check->execute([$id_user, $id_event]);
                if ($stmt_check->fetch()) {
                    $can_edit = true;
                }
            }

            if ($can_edit) {
                $stmt_update = $pdo->prepare("
                    UPDATE evenements 
                    SET titre = ?, description = ?, date_event = ?, lieu = ? 
                    WHERE id_event = ?
                ");
                $stmt_update->execute([$titre, $description, $date_event, $lieu, $id_event]);

                header("Location: dashboard.php?status=event_updated");
                exit;
            } else {
                $errors[] = "Permission refusée.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
    $event = $_POST;
    $event['id_event'] = $id_event;

} else if (isset($_GET['id'])) {
    $id_event = $_GET['id'];

    try {
        
        if ($user_role == 'admin') {
            $stmt_fetch = $pdo->prepare("SELECT * FROM evenements WHERE id_event = ?");
            $stmt_fetch->execute([$id_event]);
        } else {
            $stmt_fetch = $pdo->prepare("
                SELECT E.* FROM evenements AS E
                JOIN organisateurs_evenements AS O ON E.id_event = O.id_event
                WHERE E.id_event = ? AND O.id_user = ?
            ");
            $stmt_fetch->execute([$id_event, $id_user]);
        }
        
        $event = $stmt_fetch->fetch();

        if (!$event) {
            header("Location: dashboard.php?status=no_permission");
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur lors de la récupération de l'événement : " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un événement</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 600px; margin: 2rem auto; }
        h2 { text-align: center; color: #333; }
        form div { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="text"], input[type="datetime-local"], textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 120px; resize: vertical; }
        button { width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .errors { background-color: #ffebee; color: #c62828; border: 1px solid #c62828; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        nav { background-color: #fff; border-bottom: 1px solid #ddd; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { font-size: 1.5rem; font-weight: bold; color: #007bff; text-decoration: none; }
        nav .nav-links a { margin-left: 1rem; text-decoration: none; color: #555; }
        nav .nav-links a.button { background-color: #007bff; color: white; padding: 0.5rem 1rem; border-radius: 4px; }
        nav .nav-links a.button-secondary { background-color: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 4px; }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo">Events-Mgt</a>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="mon_profil.php">Mon Profil</a>
            <a href="dashboard.php">Tableau de Bord</a>
            <span>Bonjour, <strong><?php echo htmlspecialchars($user_email); ?></strong></span>
            <a href="creer_event.php" class="button">+ Créer</a>
            <a href="logout.php" class="button-secondary">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <h2>Modifier l'événement</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php 
        if ($event): 
            $date_formatted = date('Y-m-d\TH:i', strtotime($event['date_event']));
        ?>
            <form action="modifier_event.php" method="POST">
                <input type="hidden" name="id_event" value="<?php echo htmlspecialchars($event['id_event']); ?>">
                
                <div>
                    <label for="titre">Titre de l'événement :</label>
                    <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($event['titre']); ?>" required>
                </div>
                <div>
                    <label for="date_event">Date et Heure :</label>
                    <input type="datetime-local" id="date_event" name="date_event" value="<?php echo htmlspecialchars($date_formatted); ?>" required>
                </div>
                <div>
                    <label for="lieu">Lieu :</label>
                    <input type="text" id="lieu" name="lieu" value="<?php echo htmlspecialchars($event['lieu']); ?>" required>
                </div>
                <div>
                    <label for="description">Description :</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                <button type="submit">Mettre à jour l'événement</button>
            </form>
        <?php else: ?>
            <p>Impossible de charger les données de l'événement.</p>
        <?php endif; ?>
    </div>

</body>
</html>