<?php
$pageTitle = "QuizNight ! - Ajouter Questions";
require_once(__DIR__ . "/views/header.php");

if (!isset($_SESSION['quiz_id'], $_SESSION['num_questions'], $_SESSION['current_question'])) {
    header("Location: index.php");
    exit();
}

$quizId = $_SESSION['quiz_id'];
$currentQuestion = $_SESSION['current_question'];
$numQuestions = $_SESSION['num_questions'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['question'], $_POST['answer1'], $_POST['answer2'], $_POST['answer3'], $_POST['answer4'], $_POST['correct_answer'])) {
        $questionText = htmlspecialchars($_POST['question']);
        $answers = [
            htmlspecialchars($_POST['answer1']),
            htmlspecialchars($_POST['answer2']),
            htmlspecialchars($_POST['answer3']),
            htmlspecialchars($_POST['answer4'])
        ];
        $correctAnswer = (int) $_POST['correct_answer']; 

       
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)"); 
        $stmt->execute([$quizId, $questionText]);
        $questionId = $pdo->lastInsertId(); 
        
        $stmt = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
        for ($i = 0; $i < 4; $i++) {
            $isCorrect = ($i === $correctAnswer) ? 1 : 0; 
            $stmt->execute([$questionId, $answers[$i], $isCorrect]);
        }

        $_SESSION['current_question']++;

        if ($_SESSION['current_question'] > $numQuestions) {
            session_unset();
            session_destroy();
            echo "Le quiz a été créé avec succès !";
            exit();
        }

        header("Location: dashboard.php");
        exit();
    }
}
?>

<main>
    <form action="" method="POST" class="form">
        <h2>Question <?php echo $currentQuestion; ?></h2>
            <section class="form-body">

            <article class="form-items">
            <label class="form-question">Question :</label>
            <input type="text" name="question" required class="form-question">
            </article>

            <article class="form-items">
            <label class="form-question">Réponse 1 :</label>
            <input type="text" class="form-question" name="answer1" required>
            </article>

            <article class="form-items">
            <label class="form-question">Réponse 2 :</label>
            <input class="form-question" type="text" name="answer2" required>
            </article>

            <article class="form-items">
            <label class="form-question">Réponse 3 :</label>
            <input type="text" class="form-question" name="answer3" required>
            </article>

            <article class="form-items">
            <label class="form-question">Réponse 4 :</label>
            <input type="text" name="answer4" required class="form-question">
            </article>

            <article class="form-items">
            <label class="form-question" >Réponse correcte :</label>
            <select name="correct_answer" required class="button">
                <option value="0">Réponse 1</option>
                <option value="1">Réponse 2</option>
                <option value="2">Réponse 3</option>
                <option value="3">Réponse 4</option>
            </select>
            </article>

            <button type="submit" class="form-question button">Valider la question</button>
        </section>
    </form>
</main>

<?php require_once(__DIR__ . "/views/footer.php"); ?>
