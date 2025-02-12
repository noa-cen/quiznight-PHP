<?php

$pageTitle = "QuizNight ! - Accueil";
require_once(__DIR__ . "/views/header.php");
?>

<main>
    <section id="dashboard">
    <h1>Quizzes</h1>
    <div>
        <?php foreach ($quizzes as $quiz): ?>
            <div class="card" style="background-image: url('<?php echo htmlspecialchars($quiz['image']); ?>');">
                <div class="card-name"><?php echo htmlspecialchars($quiz['name']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>


    </section>
    
</main>

    <?php require_once(__DIR__ . "/views/footer.php"); ?>
</body>

</html>