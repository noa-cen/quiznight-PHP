<?php 
$pageTitle = "Modifier le Quiz";
require_once(__DIR__ . "/../views/header.php");
require_once(__DIR__ . "/../models/Quiz.php");
require_once(__DIR__ . "/../models/Question.php");


$quizId = $_GET['quiz_id'] ?? null;
if (!$quizId) {
    header("Location: dashboard.php");
    exit();
}

$quiz = new Quiz($quizId);
$quizDetails = $quiz->getQuizDetails();
if (!$quizDetails) {
    die("Quiz introuvable.");
}

$questionObj = new Question();
$questions = $questionObj->getQuestionsByQuizId($quizId);

$newQuestionsCount = isset($_POST['new_questions_count']) ? (int)$_POST['new_questions_count'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $newQuestionsCount++;
}

$nameVal = $_POST['name'] ?? $quizDetails['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete_quiz']) && $_POST['delete_quiz'] === 'on') {
        if ($quiz->delete()) {
            header("Location: dashboard.php");
            exit();
        } else {
            $errorMessage = "Erreur lors de la suppression du quiz.";
        }
    }

    if (isset($_POST['submit_quiz'])) {

        
        $quiz->setName($_POST['name']);
        
        $quiz->setDescription($quizDetails['description'] ?? '');
        
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
        
        if (!$quiz->update()) {
            $errorMessage = "Erreur lors de la mise à jour du quiz.";
        }
        
        $deleteIds = isset($_POST['delete_questions']) && is_array($_POST['delete_questions'])
                     ? $_POST['delete_questions'] : [];
        if (!empty($deleteIds)) {
            foreach ($deleteIds as $deleteId) {
                $questionObj->delete($deleteId);
            }
        }
        
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $qid => $data) {
                if (in_array($qid, $deleteIds)) {
                    continue;
                }
                
                $qText = isset($data['text']) ? htmlspecialchars($data['text'], ENT_QUOTES, 'UTF-8') : '';
                $answers = [
                    isset($data['answer1']) ? htmlspecialchars($data['answer1'], ENT_QUOTES, 'UTF-8') : '',
                    isset($data['answer2']) ? htmlspecialchars($data['answer2'], ENT_QUOTES, 'UTF-8') : '',
                    isset($data['answer3']) ? htmlspecialchars($data['answer3'], ENT_QUOTES, 'UTF-8') : '',
                    isset($data['answer4']) ? htmlspecialchars($data['answer4'], ENT_QUOTES, 'UTF-8') : '',
                ];
                $correct = isset($data['correct_answer']) ? (int)$data['correct_answer'] : 0;
                
                if (strpos($qid, 'new_') === 0) {
                    $questionObj->create($quizId, $qText, $answers, $correct);
                } else {
                    $questionObj->update($qid, $qText, $answers, $correct);
                }
            }
        }
        
        $allQuestions = $questionObj->getQuestionsByQuizId($quizId);
        $newTotal = count($allQuestions);
        $quiz->setNumQuestions($newTotal);
        
        if (!$quiz->update()) {
            $errorMessage = "Erreur lors de la mise à jour finale du quiz.";
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
    
    <form action="edit_quiz.php?quiz_id=<?= $quizId ?>" method="POST" class="form" enctype="multipart/form-data">
        <input type="hidden" name="new_questions_count" value="<?= $newQuestionsCount ?>">
        
        <section class="form-body">
            <article class="form-items">
                <label for="name">Nom du quiz :</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($nameVal, ENT_QUOTES, 'UTF-8') ?>" required>
            </article>
            <article class="form-items">
                <label for="quiz_image">Image du quiz :</label>
                <input type="file" id="quiz_image" name="quiz_image">
            </article>
            
            <article class="form-items">
                <label>
                    <input type="checkbox" name="delete_quiz" value="on">
                    Supprimer ce quiz ????
                </label>
            </article>
            
            <h2>Questions du quiz</h2>
        
            <?php foreach ($questions as $question): 
                $qId = $question['id'];
                
                $qTextVal = $_POST['questions'][$qId]['text'] ?? $question['question_text'];
                $qAnswers = $questionObj->getAnswersByQuestionId($qId);
                for ($i = 0; $i < count($qAnswers); $i++) {
                    $qAnswers[$i]['answer_text'] = $_POST['questions'][$qId]["answer".($i+1)] ?? $qAnswers[$i]['answer_text'];
                }
                $defaultCorrect = $_POST['questions'][$qId]['correct_answer'] ?? null;
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
                    <label for="question_<?= $qId ?>">Question :</label>
                    <input type="text" id="question_<?= $qId ?>" name="questions[<?= $qId ?>][text]" value="<?= htmlspecialchars($qTextVal, ENT_QUOTES, 'UTF-8') ?>" required>
                </article>
                <article class="form-items">
                    <h3>Réponses :</h3>
                    <?php foreach ($qAnswers as $idx => $ans): ?>
                        <label for="question_<?= $qId ?>_answer<?= $idx ?>">Réponse <?= $idx + 1; ?> :</label>
                        <input type="text" id="question_<?= $qId ?>_answer<?= $idx ?>" name="questions[<?= $qId ?>][answer<?= $idx+1 ?>]" value="<?= htmlspecialchars($ans['answer_text'], ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php endforeach; ?>
                </article>
                <article class="form-items form-question">
                    <label for="question_<?= $qId ?>_correct">Sélectionner la bonne réponse :</label>
                    <select class="button" id="question_<?= $qId ?>_correct" name="questions[<?= $qId ?>][correct_answer]" required>
                        <?php foreach ($qAnswers as $idx => $ans): ?>
                            <option value="<?= $idx ?>" <?= ($idx == $defaultCorrect) ? 'selected' : '' ?>>
                                Réponse <?= $idx + 1; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </article>
                <article class="form-items">
                    <label>
                        <input type="checkbox" name="delete_questions[]" value="<?= $qId ?>">
                        Supprimer cette question
                    </label>
                </article>
            </section>
            <?php endforeach; ?>
            
            <?php for ($i = 1; $i <= $newQuestionsCount; $i++):
                $newData = $_POST['questions']["new_$i"] ?? [];
                $newQText = $newData['text'] ?? '';
                $newAns1 = $newData['answer1'] ?? '';
                $newAns2 = $newData['answer2'] ?? '';
                $newAns3 = $newData['answer3'] ?? '';
                $newAns4 = $newData['answer4'] ?? '';
                $newCorrect = isset($newData['correct_answer']) ? $newData['correct_answer'] : 0;
            ?>
            <section class="form-body question">
                <article class="form-items">
                    <label>Question (Nouvelle) :</label>
                    <input type="text" name="questions[new_<?= $i ?>][text]" value="<?= htmlspecialchars($newQText, ENT_QUOTES, 'UTF-8') ?>" required>
                </article>
                <article class="form-items">
                    <h3>Réponses :</h3>
                    <label>Réponse 1 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer1]" value="<?= htmlspecialchars($newAns1, ENT_QUOTES, 'UTF-8') ?>" required>
                    <label>Réponse 2 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer2]" value="<?= htmlspecialchars($newAns2, ENT_QUOTES, 'UTF-8') ?>" required>
                    <label>Réponse 3 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer3]" value="<?= htmlspecialchars($newAns3, ENT_QUOTES, 'UTF-8') ?>" required>
                    <label>Réponse 4 :</label>
                    <input type="text" name="questions[new_<?= $i ?>][answer4]" value="<?= htmlspecialchars($newAns4, ENT_QUOTES, 'UTF-8') ?>" required>
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
            
            <div class="form-items">
                <button type="submit" name="add_question" class="button">Ajouter une question</button>
                <button type="submit" name="submit_quiz" class="button">Modifier le quiz</button>
            </div>
        </section>
    </form>
</main>

<?php require_once(__DIR__ . "/../views/footer.php"); ?>
