<?php
$pageTitle = "QuizNight ! - Dashboard";
require_once "header.php";


$stmt = $pdo->query("SELECT id, name, image FROM quizzes");
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id'])) {
    echo "";
} else {

    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT quiz_id, score, completed_at FROM user_scores WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $lastScore = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastScore) {
        echo "<h2>Dernier score : " . htmlspecialchars($lastScore['score']) . "</h2>";
    } else {
        echo "";
    }

}
?>

    <main>
        <h1>Bienvenue sur QuizNight !</h1>

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
