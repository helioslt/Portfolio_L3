<?php
// On démarre la session au tout début
session_start();

// On inclut le fichier de connexion à la base de données
require_once 'db.php';

// Initialisations
$errors = [];
$email = "";

// Vérifier si un message de succès est passé (après l'inscription)
$success_message = '';
if (isset($_GET['status']) && $_GET['status'] == 'registered') {
    $success_message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Récupérer et nettoyer les données
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Validation simple
    if (empty($email) || empty($password)) {
        $errors[] = "L'email et le mot de passe sont requis.";
    } else {
        try {
            // 3. Récupérer l'utilisateur correspondant à l'email
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // 4. Vérifier si l'utilisateur existe ET si le mot de passe est correct
            // On utilise password_verify() pour comparer le mot de passe fourni
            // avec le mot de passe haché dans la base de données.
            if ($user && password_verify($password, $user['mdp'])) {
                
                // 5. Le mot de passe est correct ! Création de la session.
                
                // On régénère l'ID de session (sécurité contre la fixation de session)
                session_regenerate_id(true); 
                
                // On stocke les informations vitales dans la session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role']; // 'admin', 'organisateur' ou 'user'

                // 6. Rediriger l'utilisateur vers son tableau de bord
                // (ou la page d'accueil)
                header("Location: index.php"); // Ou "dashboard.php"
                exit;

            } else {
                // Utilisateur non trouvé ou mot de passe incorrect
                $errors[] = "Email ou mot de passe incorrect.";
            }

        } catch (PDOException $e) {
            $errors[] = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion d'Événements</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 400px; }
        h2 { text-align: center; color: #333; }
        form div { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="email"], input[type="password"] { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .errors { background-color: #ffebee; color: #c62828; border: 1px solid #c62828; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .errors p { margin: 0; }
        .success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #2e7d32; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .register-link { text-align: center; margin-top: 1rem; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Connexion</h2>

        <?php
        // Afficher le message de succès (si on vient de s'inscrire)
        if (!empty($success_message)):
        ?>
            <div class="success">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php
        endif;
        ?>

        <?php
        // Afficher les erreurs de connexion
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

        <form action="login.php" method="POST">
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>

        <div class="register-link">
            <p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous ici</a></p>
        </div>
    </div>

</body>
</html>