<?php
$pageTitle = "QuizNight ! - Dashboard";
require_once 'views/header.php';


// Récupérer tous les quiz
$stmt = $pdo->query("SELECT id, name, image FROM quizzes");
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "";
} else {

    $userId = $_SESSION['user_id'];

    // Récupérer les scores du dernier quiz joué
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

        <div class="quiz-container">
            <!-- Carte pour créer un nouveau quiz -->
            <div class="quiz-card create-quiz">
                <a href="init_quiz.php">
                    <div class="quiz-content">
                        <p>Créer un quiz</p>
                    </div>
                </a>
            </div>

                
            <?php foreach ($quizzes as $quiz) : ?>
                <div class="quiz-card">
                    <a href="play_quiz.php?id=<?= $quiz['id'] ?>">
                        <img src="uploads/<?= htmlspecialchars($quiz['image']) ?>" alt="<?= htmlspecialchars($quiz['name']) ?>">
                        <div class="quiz-title"><?= htmlspecialchars($quiz['name']) ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php require_once 'views/footer.php'; ?>

    }

