<?php

$pageTitle = "QuizNight ! - login";
require_once "header.php";
require_once  '../models/User.php';

$errors=[];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email || empty($password)){
        $errors[]="Veuillez remplir tous les champs.";
    } else {
        try {
            $user = New User();
            $loggedUser = $user->login($email,$password);
        
        if ($loggedUser) {
            $_SESSION['user_id'] = $loggedUser['id'];
            $_SESSION['username'] = $loggedUser['username'];
            header("Location: ../index.php");
            exit;
        } else {
            $errors[] = "Email ou mot de passe incorrect.";
        }
        } catch (Exception $e) {
        $errors[] = "Une erreur est survenue : " . htmlspecialchars($e->getMessage());
        }
    }
}

?>

        <h2>Connexion</h2>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="user_login.php" method="post">
            <label for="email">Email :</label>
            <input type="email" name="email" required>
            
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>

        <a href="user_register.php" class="register-link">Pas encore inscrit ? C'est ici !</a>
    </body>
</html>