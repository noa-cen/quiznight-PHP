<?php
$pageTitle = "QuizNight ! - Jouer au Quiz";
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php"); 
    exit();
}

if (!isset($_GET['id'])) {
    die("Quiz introuvable.");
}

$quizId = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT id, question_text FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    die("Aucune question trouvée pour ce quiz.");
}

if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0; 
    $_SESSION['score'] = 0; 
    $_SESSION['quiz_id'] = $quizId; 
}

$currentQuestionIndex = $_SESSION['current_question'];

if ($currentQuestionIndex >= count($questions)) {
    $score = $_SESSION['score'];
    $userId = $_SESSION['user_id']; 

    if (!empty($userId)) {
        $stmt = $pdo->prepare("INSERT INTO user_scores (user_id, quiz_id, score, completed_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $quizId, $score]);
    } else {
        die("Erreur : utilisateur non connecté.");
    }

    
    $_SESSION['current_question'] = 0; 
    $_SESSION['score'] = 0; 

    header("Location: dashboard.php"); 
    exit();
}

$currentQuestion = $questions[$currentQuestionIndex];

$stmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM answers WHERE question_id = ?");
$stmt->execute([$currentQuestion['id']]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $selectedAnswer = (int) $_POST['answer'];

    foreach ($answers as $answer) {
        if ($answer['id'] === $selectedAnswer && $answer['is_correct']) {
            $_SESSION['score']++;
            break;
        }
    }

    $_SESSION['current_question']++;

    header("Location: play_quiz.php?id=" . $quizId);
    exit();
}
?>

<main>
    <section>
        <p>Score: <?= $_SESSION['score'] ?> / <?= count($questions) ?></p>
    </section>

    <section class="form form-container">
        <h2>Question <?= $currentQuestionIndex + 1 ?> / <?= count($questions) ?></h2>

        <p class="form-question"><?= htmlspecialchars($currentQuestion['question_text']) ?></p>

        <form class="form-container" action="" method="POST">
            <?php foreach ($answers as $answer) : ?>
                <div class="question">
                    <input type="radio" id="answer<?= $answer['id'] ?>" name="answer" value="<?= $answer['id'] ?>" required>
                    <label for="answer<?= $answer['id'] ?>"><?= htmlspecialchars($answer['answer_text']) ?></label>
                </div>
            <?php endforeach; ?>
            <button class="button" type="submit">Valider</button>
        </form>
    </section>
</main>

<?php require_once __DIR__ . "/../views/footer.php"; ?>
