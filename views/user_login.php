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
            $_SESSION['success'] = "Tu es connecté.e, à toi de jouer !";
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
<main>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li class="message error"><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="user_login.php" method="post" class="form">
        <h2>Connexion !</h2>
            <section class="form-body">
                
                <article class="form-items">
                <label for="email">Email :</label>
                <input type="email" name="email" required>
                </article>

                <article class="form-items">
                <label for="password">Mot de passe :</label>
                <input type="password" name="password" required>
                </article>

                <button type="submit" class="button">Se connecter</button>
                <a href="user_register.php">Pas encore inscrit ? C'est ici !</a>
            </section>
     </form>
 </main>
 <?php require_once 'footer.php';?>
