<?php
$pageTitle = "QuizNight ! - Accueil";
require_once 'views/header.php';
require_once 'models/User.php';

if (!isset($_SESSION['user_id'])) {
    die("Erreur : Aucun utilisateur connecté.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['quiz_name'], $_FILES['quiz_image'], $_POST['num_questions']) 
        && $_FILES['quiz_image']['error'] === UPLOAD_ERR_OK
    ) {
        $quizName = trim(htmlspecialchars($_POST['quiz_name']));
        $numQuestions = (int) $_POST['num_questions'];

        // Vérification du nombre de questions
        if ($numQuestions < 1) {
            die("Erreur : Le nombre de questions doit être d'au moins 1.");
        }

        // Vérification que le nom du quiz est unique
        $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE name = ?");
        $stmt->execute([$quizName]);
        if ($stmt->rowCount() > 0) {
            die("Erreur : Ce nom de quiz est déjà pris, veuillez en choisir un autre.");
        }

        // Gestion de l'upload d'image
        $uploadDir = 'uploads/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2 Mo

        $fileExtension = strtolower(pathinfo($_FILES['quiz_image']['name'], PATHINFO_EXTENSION));
        $imageInfo = getimagesize($_FILES['quiz_image']['tmp_name']);

        // Vérification du format
        if (!in_array($fileExtension, $allowedExtensions)) {
            die("Format d'image non autorisé. Veuillez choisir un fichier JPG, JPEG, PNG ou GIF.");
        }

        // Vérification de la taille
        if ($_FILES['quiz_image']['size'] > $maxFileSize) {
            die("Fichier trop volumineux. Taille maximale autorisée : 2 Mo.");
        }

        // Vérification MIME
        if ($imageInfo === false) {
            die("Le fichier n'est pas une image valide.");
        }

        // Renommage sécurisé
        $newFileName = uniqid('quiz_img_') . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        // Enregistrement du fichier
        if (!move_uploaded_file($_FILES['quiz_image']['tmp_name'], $uploadPath)) {
            die("Erreur lors de l'upload de l'image.");
        }

        $createdBy = $_SESSION['user_id'];

        // Vérifie que l'utilisateur existe dans la base
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$createdBy]);
        if ($stmt->rowCount() == 0) {
            die("Erreur : L'utilisateur n'existe pas.");
        }

        // Insertion du quiz
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

<form action="" method="POST" class="form" enctype="multipart/form-data">
    <div class="form-items">
        <label>Nom du quiz :</label>
        <input type="text" name="quiz_name" required>
    </div>
    <div class="form-items">
        <label>Image du quiz :</label>
        <input type="file" name="quiz_image" accept="image/*" required>
    </div>
    <div class="form-items">
        <label>Nombre de questions :</label>
        <input type="number" name="num_questions" min="1" required>
    </div>
    <div class="form-items">
        <button type="submit">Créer le quiz</button>
    </div>
</form>

<?php require_once(__DIR__ . "/views/footer.php"); ?>
