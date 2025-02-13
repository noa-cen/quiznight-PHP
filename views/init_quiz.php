<?php
$pageTitle = "QuizNight ! - Accueil";
require_once 'header.php';
require_once '../models/User.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: views/user_login.php"); 
    exit(); 
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['quiz_name'], $_FILES['quiz_image'], $_POST['num_questions']) 
        && $_FILES['quiz_image']['error'] === UPLOAD_ERR_OK
    ) {
        $quizName = trim(htmlspecialchars($_POST['quiz_name']));
        $numQuestions = (int) $_POST['num_questions'];

        if ($numQuestions < 1) {
            die("Erreur : Le nombre de questions doit être d'au moins 1.");
        }

        $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE name = ?");
        $stmt->execute([$quizName]);
        if ($stmt->rowCount() > 0) {
            die("Erreur : Ce nom de quiz est déjà pris, veuillez en choisir un autre.");
        }

        $uploadDir = 'uploads/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 2 * 1024 * 1024; 

        $fileExtension = strtolower(pathinfo($_FILES['quiz_image']['name'], PATHINFO_EXTENSION));
        $imageInfo = getimagesize($_FILES['quiz_image']['tmp_name']);

        if (!in_array($fileExtension, $allowedExtensions)) {
            die("Format d'image non autorisé. Veuillez choisir un fichier JPG, JPEG, PNG ou GIF.");
        }

        if ($_FILES['quiz_image']['size'] > $maxFileSize) {
            die("Fichier trop volumineux. Taille maximale autorisée : 2 Mo.");
        }

        if ($imageInfo === false) {
            die("Le fichier n'est pas une image valide.");
        }

        $newFileName = uniqid('quiz_img_') . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($_FILES['quiz_image']['tmp_name'], $uploadPath)) {
            die("Erreur lors de l'upload de l'image.");
        }

        $createdBy = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$createdBy]);
        if ($stmt->rowCount() == 0) {
            die("Erreur : L'utilisateur n'existe pas.");
        }

        $stmt = $pdo->prepare("INSERT INTO quizzes (name, image, num_questions, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$quizName, $newFileName, $numQuestions, $createdBy])) {
            $quizId = $pdo->lastInsertId();
            $_SESSION['quiz_id'] = $quizId;
            $_SESSION['num_questions'] = $numQuestions;
            $_SESSION['current_question'] = 1;

            header("Location: create_questions.php");
            exit();
        } else {
            die("Erreur lors de l'insertion du quiz.");
        }
    } else {
        die("Erreur : Tous les champs sont requis.");
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
