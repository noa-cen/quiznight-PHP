<?php

$pageTitle = "QuizNight ! - Inscription";
require_once "header.php";
require_once  '../models/User.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $user = new User();

        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);

        if ($user->register()) {
            header("Location: user_login.php"); 
            exit;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>
    <h2>Inscription</h2>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="user_register.php" method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" name="username" required>

        <label for="email">Email :</label>
        <input type="email" name="email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" required>
        <small>8 caractères minimum, 1 majuscule et 1 chiffre.</small>

        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>