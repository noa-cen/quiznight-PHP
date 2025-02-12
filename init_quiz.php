<?php

$pageTitle = "QuizNight ! - Accueil";
require_once(__DIR__ . "/views/header.php");
?>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['quiz_name'], $_FILES['quiz_image'], $_POST['num_questions'])) {
            $quizName = htmlspecialchars($_POST['quiz_name']);
            $numQuestions = (int) $_POST['num_questions'];

            // Gestion de l'upload d'image
            $uploadDir = 'uploads/';
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $maxFileSize = 2 * 1024 * 1024; // 2 Mo

            $fileExtension = strtolower(pathinfo($_FILES['quiz_image']['name'], PATHINFO_EXTENSION));
            $imageInfo = getimagesize($_FILES['quiz_image']['tmp_name']);

            // Vérification du format
            if (!in_array($fileExtension, $allowedExtensions)) {
                echo "Format d’image non autorisé. Veuillez choisir un fichier JPG, JPEG, PNG ou GIF.";
                exit();
            }

            // Vérification de la taille
            if ($_FILES['quiz_image']['size'] > $maxFileSize) {
                echo "Fichier trop volumineux. Taille maximale autorisée : 2 Mo.";
                exit();
            }

            // Vérification MIME
            if ($imageInfo === false) {
                echo "Le fichier n’est pas une image valide.";
                exit();
            }

            // Renommage sécurisé
            $newFileName = uniqid('quiz_') . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFileName;

            // Enregistrement du fichier
            if (!move_uploaded_file($_FILES['quiz_image']['tmp_name'], $uploadPath)) {
                echo "Erreur lors de l’upload de l’image.";
                exit();
            }

            // Insérer dans la base de données
            $stmt = $pdo->prepare("INSERT INTO quizzes (name, image, num_questions) VALUES (?, ?, ?)");
            $stmt->execute([$quizName, $newFileName, $numQuestions]);

            // Récupérer l'ID du quiz
            $quizId = $pdo->lastInsertId();
            $_SESSION['quiz_id'] = $quizId;
            $_SESSION['num_questions'] = $numQuestions;
            $_SESSION['current_question'] = 1;

            header("Location: create_questions.php");
            exit();
            } else {
                echo "Erreur lors de l'upload de l'image.";
            }
        }

    ?>

        <form action="" method="POST" class="form" enctype="multipart/form-data">
            <div class="form-items" >
                <label>Nom du quiz :</label>
                <input type="text" name="quiz_name" required>
            </div>
            <div class="form-items" >
                <label>Image du quiz :</label>
                <input type="file" name="quiz_image" accept="image/*" required>
            </div>
            <div class="form-items" >
                <label>Nombre de questions :</label>
                <input type="number" name="num_questions" min="1" required>
            </div>
            <div class="form-items" >
                <button type="submit">Créer le quiz</button>
            </div>
        </form>
    
    </main>
    <?php require_once(__DIR__ . "/views/footer.php"); ?>
</body>

</html>