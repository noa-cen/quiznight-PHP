<?php

$pageTitle = "QuizNight ! - Accueil";
require_once 'views/header.php';

if (isset($_SESSION['success'])) {
    echo '<p class="message success">' . htmlspecialchars($_SESSION['success']) . '</p>';
    unset($_SESSION['success']); 
}
?>

<main>
    <section class="home">
        <article class="text">
            <h2>Bienvenue sur QuizNight !</h2>
            <p>Testez vos connaissances et affrontez vos amis dans un quiz fun et dynamique !</p><br>
            <p>Saurez-vous relever le défi ?</p> 

            <article class="button jump">
                <a href="views/dashboard.php" aria-label="Accéder à la page quiz">Prêt à jouer ?</a>
            </article>
        </article>

        <img src="assets\img\logo.png" alt="Point d'interrogation" class="questionMark">
    </section>
</main>

<?php require_once 'views\footer.php'; ?>
