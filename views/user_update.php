<?php
$pageTitle = "QuizNight ! - update";
require_once "header.php";
require_once '../models/User.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès refusé !");
}

if (isset($_SESSION['success'])) {
    echo '<p class="message success">' . htmlspecialchars($_SESSION['success']) . '</p>';
    unset($_SESSION['success']);
}

$user = new User();
$userId = $_SESSION['user_id'];

$stmt = $user->getPdo()->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("Utilisateur introuvable !");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);

    try {
        $user->setUsername($newUsername);
        if ($user->update($userId)) {
            $_SESSION['username'] = $newUsername;
            $_SESSION['success'] = "Ton username a bien été modifié !";
            header("Location: user_update.php"); 
            exit;
        } else {
            echo "Erreur lors de la mise à jour.";
        }
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

?>

<main>
    <form method="POST" action="" class="form">
    <h2>Mon compte !</h2>
        <section class="form-body">
            <article class="form-items">
            <label for="email">Adresse e-mail :</label>
            <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly>
            </article>

            <article class="form-items">
            <label for="email">Username :</label>
            <input type="text" name="username" value="<?= htmlspecialchars($currentUser['username']) ?>" readonly>
            </article>

            <article class="form-items">
            <label for="username">Nouveau username :</label>
            <input type="text" name="username" value="" required>
            </article>

            <button type="submit" class="button">Mettre à jour</button>
        </section>
    </form>
    <form method="POST" action="user_delete.php" >
        <button type="submit" class="delete"  onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ?');">Supprimer mon compte</button>
    </form>
    
</main>

<?php require_once 'footer.php'; ?>