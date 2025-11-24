<?php
session_start();

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'organisateur') {
    header('Location: index.php');
    exit;
}

$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

$errors = [];
$titre = $description = $date_event = $lieu = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $date_event = $_POST['date_event'];
    $lieu = trim($_POST['lieu']);
    
    if (empty($titre)) { $errors[] = "Le titre est obligatoire."; }
    if (empty($date_event)) { $errors[] = "La date est obligatoire."; }
    if (empty($lieu)) { $errors[] = "Le lieu est obligatoire."; }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $stmt_event = $pdo->prepare("INSERT INTO evenements (titre, description, date_event, lieu) VALUES (?, ?, ?, ?)");
            $stmt_event->execute([$titre, $description, $date_event, $lieu]);
            
            $id_event_cree = $pdo->lastInsertId();
            
            $id_organisateur = $_SESSION['user_id'];
            
            $stmt_orga = $pdo->prepare("INSERT INTO organisateurs_evenements (id_user, id_event) VALUES (?, ?)");
            $stmt_orga->execute([$id_organisateur, $id_event_cree]);
            
            $pdo->commit();
            
            header("Location: dashboard.php?status=event_created");
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de la création de l'événement : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un événement</title>
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
            
            <?php
            if ($user_role == 'admin' || $user_role == 'organisateur'):
            ?>
                <a href="creer_event.php" class="button" style="background-color: #218838; border-color: #1e7e34;">+ Créer</a>
            <?php endif; ?>

            <a href="logout.php" class="button-secondary">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <h2>Créer un nouvel événement</h2>

        <?php
        if (!empty($errors)):
        ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php
        endif;
        ?>

        <form action="creer_event.php" method="POST">
            <div>
                <label for="titre">Titre de l'événement :</label>
                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($titre); ?>" required>
            </div>
            <div>
                <label for="date_event">Date et Heure :</label>
                <input type="datetime-local" id="date_event" name="date_event" value="<?php echo htmlspecialchars($date_event); ?>" required>
            </div>
            <div>
                <label for="lieu">Lieu :</label>
                <input type="text" id="lieu" name="lieu" value="<?php echo htmlspecialchars($lieu); ?>" required>
            </div>
            <div>
                <label for="description">Description :</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <button type="submit">Créer l'événement</button>
        </form>
        </div>

</body>
</html>