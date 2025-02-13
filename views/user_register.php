<?php

$pageTitle = "QuizNight ! - Inscription";
require_once "header.php";
require_once  '../models/User.php';

$errors = [];

if (isset($_SESSION['success'])) {
    echo '<p class="message success">' . htmlspecialchars($_SESSION['success']) . '</p>';
    unset($_SESSION['success']);
}

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
            $_SESSION['success'] = "Compte créé, identifiez-vous pour jouer !";  
            header("Location: user_login.php"); 
            exit;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<main>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li class="error"><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="user_register.php" method="post" class="form">
    <h2>Inscription !</h2>
        <section class="form-body">
            <article class ="form-items">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" name="username" required>
            </article>

            <article class ="form-items">
            <label for="email">Email :</label>
            <input type="email" name="email" required>
            </article>

            <article class ="form-items">
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" required>
            <p>8 caractères minimum, 1 majuscule et 1 chiffre.</p>
            </article>

            <button type="submit" class="button">S'inscrire</button>
        </section>
    </form>
</main>
<?php require_once 'footer.php';?>