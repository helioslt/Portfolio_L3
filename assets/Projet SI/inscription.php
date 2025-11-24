<?php
// On démarre la session au tout début
session_start();

// On inclut le fichier de connexion à la base de données
require_once 'db.php';

// On initialise un tableau pour stocker les erreurs
$errors = [];
// On initialise $email pour la re-remplir dans le formulaire en cas d'erreur
$email = ""; 

// On vérifie si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Récupérer et nettoyer les données
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $role = $_POST['role'];

    // 2. Valider les données
    if (empty($email) || empty($password) || empty($password_confirm) || empty($role)) {
        $errors[] = "Tous les champs sont obligatoires.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    // On s'assure que le rôle est bien l'une des deux valeurs autorisées
    if (!in_array($role, ['user', 'organisateur'])) {
        $errors[] = "Le rôle sélectionné n'est pas valide.";
    }

    // 3. S'il n'y a pas d'erreurs de validation, on vérifie si l'email existe déjà
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id_user FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user_exists = $stmt->fetch();

            if ($user_exists) {
                $errors[] = "Cette adresse email est déjà utilisée.";
            } else {
                // 4. Hacher le mot de passe (NE JAMAIS STOCKER EN CLAIR)
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // 5. Insérer l'utilisateur dans la base de données
                $stmt_insert = $pdo->prepare("INSERT INTO utilisateurs (email, mdp, role) VALUES (?, ?, ?)");
                $stmt_insert->execute([$email, $hashed_password, $role]);

                // 6. Rediriger vers la page de connexion avec un message de succès
                header("Location: login.php?status=registered");
                exit; // Toujours appeler exit() après une redirection
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur d'inscription : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Gestion d'Événements</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 400px; }
        h2 { text-align: center; color: #333; }
        form div { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="email"], input[type="password"], select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .errors { background-color: #ffebee; color: #c62828; border: 1px solid #c62828; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .errors p { margin: 0; }
        .login-link { text-align: center; margin-top: 1rem; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Inscription</h2>

        <?php
        // S'il y a des erreurs, les afficher ici
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

        <form action="inscription.php" method="POST">
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div>
                <label for="role">Je suis un :</label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo ($role ?? '') == 'user' ? 'selected' : ''; ?>>Utilisateur (pour m'inscrire aux événements)</option>
                    <option value="organisateur" <?php echo ($role ?? '') == 'organisateur' ? 'selected' : ''; ?>>Organisateur (pour créer des événements)</option>
                </select>
                </div>
            <div>
                <label for="password">Mot de passe (6 caractères min.) :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="password_confirm">Confirmer le mot de passe :</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>

        <div class="login-link">
            <p>Déjà un compte ? <a href="login.php">Connectez-vous ici</a></p>
        </div>
    </div>

</body>
</html>