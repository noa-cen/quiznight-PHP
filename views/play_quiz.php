<?php
$pageTitle = "QuizNight ! - Jouer au Quiz";
require_once 'views/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
    exit();
}

// Vérifier si un quiz est sélectionné
if (!isset($_GET['id'])) {
    die("Quiz introuvable.");
}

$quizId = (int) $_GET['id'];

// Récupérer les questions du quiz
$stmt = $pdo->prepare("SELECT id, question_text FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    die("Aucune question trouvée pour ce quiz.");
}

// Initialisation de la session pour la progression
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0; // Commence à la première question
    $_SESSION['score'] = 0; // Initialise le score
    $_SESSION['quiz_id'] = $quizId; // Enregistre l'ID du quiz en cours
}

$currentQuestionIndex = $_SESSION['current_question'];

if ($currentQuestionIndex >= count($questions)) {
    // Fin du quiz : enregistrement du score dans la base de données
    $score = $_SESSION['score'];
    $userId = $_SESSION['user_id'];  // Utiliser la session de l'utilisateur connecté

    // Vérifier que $userId n'est pas vide avant d'enregistrer
    if (!empty($userId)) {
        $stmt = $pdo->prepare("INSERT INTO user_scores (user_id, quiz_id, score, completed_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $quizId, $score]);
    } else {
        die("Erreur : utilisateur non connecté.");
    }

    // Réinitialiser la session pour un nouveau quiz
    $_SESSION['current_question'] = 0; // Réinitialise à la première question pour permettre de rejouer
    $_SESSION['score'] = 0; // Réinitialise le score pour un nouveau quiz

    // Recharger la page pour que l'utilisateur puisse recommencer le quiz
    header("Location: dashboard.php"); 
    exit();
}

$currentQuestion = $questions[$currentQuestionIndex];

// Récupérer les réponses associées
$stmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM answers WHERE question_id = ?");
$stmt->execute([$currentQuestion['id']]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur a répondu à la question précédente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $selectedAnswer = (int) $_POST['answer'];

    // Vérifier si la réponse est correcte
    foreach ($answers as $answer) {
        if ($answer['id'] === $selectedAnswer && $answer['is_correct']) {
            $_SESSION['score']++;
            break;
        }
    }

    $_SESSION['current_question']++; // Passer à la question suivante

    // Recharger la page pour afficher la question suivante
    header("Location: play_quiz.php?id=" . $quizId);
    exit();
}
?>

<main>
    <!-- Afficher le score de manière permanente -->
    <div>
        <strong>Score: <?= $_SESSION['score'] ?> / <?= count($questions) ?></strong>
    </div>

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

<?php require_once 'views/footer.php'; ?>
