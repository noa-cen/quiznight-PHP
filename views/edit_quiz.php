<?php 
$pageTitle = "Modifier le Quiz";
require_once(__DIR__ . "/../views/header.php");
require_once(__DIR__ . "/../models/Quiz.php");
require_once(__DIR__ . "/../models/Question.php");

// Récupérer l'ID du quiz à modifier
$quizId = $_GET['quiz_id'] ?? null;
if (!$quizId) {
    header("Location: dashboard.php");
    exit();
}

// Instancier et récupérer les détails du quiz
$quiz = new Quiz($quizId);
$quizDetails = $quiz->getQuizDetails();

// Instancier l'objet Question pour récupérer les questions existantes
$questionObj = new Question();
$questions = $questionObj->getQuestionsByQuizId($quizId);

// Initialisation de la variable pour le nombre de nouvelles questions à ajouter
// Celle-ci est conservée dans un champ caché "new_questions_count"
$newQuestionsCount = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si le formulaire est soumis pour ajouter une question, on incrémente le compteur
    if (isset($_POST['add_question'])) {
        $newQuestionsCount = (int)($_POST['new_questions_count'] ?? 0) + 1;
    } else {
        // Si on valide le quiz, on récupère le nombre de nouvelles questions renseigné
        $newQuestionsCount = (int)($_POST['new_questions_count'] ?? 0);
    }
}

// Pour pré-remplir les champs du quiz (nom, nombre de questions) avec les données postées si disponibles
$nameVal = $_POST['name'] ?? $quizDetails['name'];
$numQuestionsVal = $_POST['num_questions'] ?? $quizDetails['num_questions'];

// Si le formulaire est soumis pour mettre à jour le quiz (bouton "Modifier le quiz")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    if (isset($_POST['name'], $_POST['num_questions'])) {
        // Mise à jour des infos du quiz
        $quiz->setName($_POST['name']);
        $quiz->setNumQuestions($_POST['num_questions']);
        
        // Traitement de l'image du quiz
        if (isset($_FILES['quiz_image']) && $_FILES['quiz_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $newImage = $quiz->uploadImage($_FILES['quiz_image']);
                $quiz->setImage($newImage);
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        } else {
            $quiz->setImage($quizDetails['image']);
        }
        
        try {
            $quiz->update();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        // Suppression des questions cochées
        if (isset($_POST['delete_questions']) && is_array($_POST['delete_questions'])) {
            foreach ($_POST['delete_questions'] as $deleteQuestionId) {
                $questionObj->delete($deleteQuestionId);
            }
        }

        // Traitement des questions (mise à jour des existantes et création des nouvelles)
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $questionId => $data) {
                $questionText = htmlspecialchars($data['text']);
                $answers = [
                    htmlspecialchars($data['answer1']),
                    htmlspecialchars($data['answer2']),
                    htmlspecialchars($data['answer3']),
                    htmlspecialchars($data['answer4'])
                ];
                $correctAnswer = (int)$data['correct_answer'];

                // Si l'identifiant commence par "new_", il s'agit d'une question à créer
                if (strpos($questionId, 'new_') === 0) {
                    $questionObj->create($quizId, $questionText, $answers, $correctAnswer);
                } else {
                    $questionObj->update($questionId, $questionText, $answers, $correctAnswer);
                }
            }
        }
        header("Location: dashboard.php");
        exit();
    }
}
?>

<main>
    <h1>Modifier le quiz : <?= htmlspecialchars($quizDetails['name']) ?></h1>

    <?php if (isset($errorMessage)): ?>
        <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <!-- Le formulaire contient un champ caché pour mémoriser le nombre de nouvelles questions -->
    <form action="edit_quiz.php?quiz_id=<?= $quizId ?>" method="POST" class="form" enctype="multipart/form-data">
        <input type="hidden" name="new_questions_count" value="<?= $newQuestionsCount ?>">
        <section class="form-body">
            <article class="form-items">
                <label for="name">Nom du quiz :</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($nameVal) ?>" required>
            </article>
            <article class="form-items">
                <label for="quiz_image">Image du quiz :</label>
                <input type="file" id="quiz_image" name="quiz_image">
            </article>
            
            <h2>Questions du quiz</h2>
            <!-- Affichage des questions existantes -->
            <?php foreach ($questions as $question): 
                // Pour chaque question existante, on pré-remplit avec les données postées (si elles existent) sinon avec la BDD
                $questionTextVal = $_POST['questions'][$question['id']]['text'] ?? $question['question_text'];
                $qAnswers = $questionObj->getAnswersByQuestionId($question['id']);
                // Si le formulaire avait déjà été soumis, on remplace les valeurs par celles postées
                for ($i = 0; $i < count($qAnswers); $i++) {
                    $qAnswers[$i]['answer_text'] = $_POST['questions'][$question['id']]["answer".($i+1)] ?? $qAnswers[$i]['answer_text'];
                }
                // Détermination de la réponse correcte (priorité aux données postées)
                $defaultCorrect = $_POST['questions'][$question['id']]['correct_answer'] ?? null;
                if ($defaultCorrect === null) {
                    foreach ($qAnswers as $idx => $ans) {
                        if ($ans['is_correct'] == 1) {
                            $defaultCorrect = $idx;
                            break;
                        }
                    }
                }
            ?>
            <section class="form-body question">
                <article class="form-items">
                    <label for="question_<?= $question['id'] ?>">Question :</label>
                    <input type="text" id="question_<?= $question['id'] ?>" name="questions[<?= $question['id'] ?>][text]" value="<?= htmlspecialchars($questionTextVal) ?>" required>
                </article>
                <article class="form-items">
                    <h3>Réponses :</h3>
                    <?php foreach ($qAnswers as $idx => $ans): ?>
                        <label for="question_<?= $question['id'] ?>_answer<?= $idx ?>">Réponse <?= $idx + 1; ?> :</label>
                        <input type="text" id="question_<?= $question['id'] ?>_answer<?= $idx ?>" name="questions[<?= $question['id'] ?>][answer<?= $idx+1 ?>]" value="<?= htmlspecialchars($ans['answer_text']) ?>" required>
                    <?php endforeach; ?>
                </article>
                <article class="form-items form-question">
                    <label for="question_<?= $question['id'] ?>_correct">Sélectionner la bonne réponse :</label>
                    <select class="button" id="question_<?= $question['id'] ?>_correct" name="questions[<?= $question['id'] ?>][correct_answer]" required>
                        <?php foreach ($qAnswers as $idx => $ans): ?>
                            <option value="<?= $idx ?>" <?= ($idx == $defaultCorrect) ? 'selected' : '' ?>>
                                Réponse <?= $idx + 1; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </article>
                <article class="form-items">
                    <label>
                        <input type="checkbox" name="delete_questions[]" value="<?= $question['id'] ?>">
                        Supprimer cette question
                    </label>
                </article>
            </section>
            <?php endforeach; ?>
            
            <!-- Affichage des nouvelles questions à ajouter -->
            <?php for ($i = 1; $i <= $newQuestionsCount; $i++):
                // Pré-remplissage éventuel si le formulaire avait déjà été soumis
                $newData = $_POST['questions']["new_$i"] ?? [];
                $newQuestionText = $newData['text'] ?? '';
                $newAnswer1 = $newData['answer1'] ?? '';
                $newAnswer2 = $newData['answer2'] ?? '';
                $newAnswer3 = $newData['answer3'] ?? '';
                $newAnswer4 = $newData['answer4'] ?? '';
                $newCorrect = isset($newData['correct_answer']) ? $newData['correct_answer'] : 0;
            ?>
            <section class="form-body question">
                <article class="form-items">
                    <label>Question (Nouvelle) :</label>
                    <input type="text" name="questions[new_<?= $i ?>][text]" value="<?= htmlspecialchars($newQuestionText) ?>" required>
                </article>
                <article class="form-items">
                    <h3>Réponses :</h3>
                    <label>Réponse 1 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer1]" value="<?= htmlspecialchars($newAnswer1) ?>" required>
                    <label>Réponse 2 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer2]" value="<?= htmlspecialchars($newAnswer2) ?>" required>
                    <label>Réponse 3 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer3]" value="<?= htmlspecialchars($newAnswer3) ?>" required>
                    <label>Réponse 4 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer4]" value="<?= htmlspecialchars($newAnswer4) ?>" required>
                </article>
                <article class="form-items form-question">
                    <label>Sélectionner la bonne réponse :</label>
                    <select class="button" name="questions[new_<?= $i ?>][correct_answer]" required>
                        <option value="0" <?= ($newCorrect == 0) ? 'selected' : '' ?>>Réponse 1</option>
                        <option value="1" <?= ($newCorrect == 1) ? 'selected' : '' ?>>Réponse 2</option>
                        <option value="2" <?= ($newCorrect == 2) ? 'selected' : '' ?>>Réponse 3</option>
                        <option value="3" <?= ($newCorrect == 3) ? 'selected' : '' ?>>Réponse 4</option>
                    </select>
                </article>
            </section>
            <?php endfor; ?>

            <!-- Boutons de soumission : l'un pour ajouter une nouvelle question, l'autre pour valider les modifications -->
            <div class="form-items">
                <button type="submit" name="add_question" class="button">Ajouter une question</button>
                <button type="submit" name="submit_quiz" class="button">Modifier le quiz</button>
            </div>
        </section>
    </form>
</main>

<?php require_once(__DIR__ . "/../views/footer.php"); ?>
