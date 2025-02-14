<?php
$pageTitle = "QuizNight ! - Accueil";
require_once 'header.php';
require_once '../models/User.php';
require_once '../models/Quiz.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: user_login.php"); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['quiz_name'], $_FILES['quiz_image'], $_POST['num_questions'])) {
            throw new Exception("Tous les champs sont requis.");
        }

        $quiz = new Quiz();
        $quiz->setName($_POST['quiz_name']);
        $quiz->setNumQuestions($_POST['num_questions']);
        $quiz->setCreatedBy($_SESSION['user_id']);

        $quizImage = Quiz::uploadImage($_FILES['quiz_image']);
        $quiz->setImage($quizImage);

        $quizId = $quiz->save();
        $_SESSION['quiz_id'] = $quizId;
        $_SESSION['num_questions'] = $_POST['num_questions'];
        $_SESSION['current_question'] = 1;

        header("Location: create_questions.php");
        exit();
    } catch (Exception $e) {
        die("Erreur : " . $e->getMessage());
    }
}
?>

<main>
    <form action="" method="POST" class="form" enctype="multipart/form-data">
        <h2>Créé ton quizz !</h2>
        <section class="form-body">
            <article class="form-items">
                <label>Nom du quiz :</label>
                <input type="text" name="quiz_name" required>
            </article>
            <article class="form-items">
                <label>Image du quiz :</label>
                <input type="file" name="quiz_image" accept="image/*" required>
            </article>
            <article class="form-items">
                <label>Nombre de questions :</label>
                <input type="number" name="num_questions" min="1" required>
            </article>
            <article class="form-items">
                <button type="submit" class="button">Créer le quiz</button>
            </article>
        </section>
    </form>
</main>

<?php require_once 'footer.php'; ?>
