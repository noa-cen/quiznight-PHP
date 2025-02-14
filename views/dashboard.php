<?php
$pageTitle = "QuizNight ! - Dashboard";
require_once "header.php";
require_once "../models/Quiz.php";
require_once "../models/Score.php";

$quizzes = Quiz::getAllQuizzes();
?>

<main>
    <h1>Bienvenue sur QuizNight !</h1>

    <?php if (isset($_SESSION['user_id'])) : ?>
        <?php
        $score = new Score($_SESSION['user_id']);
        $lastScore = $score->getLastScore();
        ?>
        <?php if ($lastScore) : ?>
            <h2>Dernier score : <?= htmlspecialchars($lastScore['score']) ?></h2>
        <?php endif; ?>
    <?php endif; ?>

    <section class="quiz-container">
        <article class="quiz-card create-quiz">
            <a href="init_quiz.php">
                <div class="quiz-content"><p>Créer un quiz</p></div>
            </a>
        </article>

        <?php foreach ($quizzes as $quiz) : ?>
            <article class="quiz-card">
                <a href="play_quiz.php?id=<?= $quiz['id'] ?>">
                    <img src="/quiznight-PHP/uploads/<?= htmlspecialchars($quiz['image']) ?>" alt="<?= htmlspecialchars($quiz['name']) ?>">
                    <div class="quiz-title"><?= htmlspecialchars($quiz['name']) ?></div>
                </a>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php require_once 'footer.php'; ?>
