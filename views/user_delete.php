<?php
$pageTitle = "QuizNight ! - delate";
require_once "header.php";
require_once '../models/User.php';


if (!isset($_SESSION['user_id'])) {
    die("Accès refusé !");
}

require_once '../models/User.php';
$user = new User();

$userId = $_SESSION['user_id'];

try {
    $stmt = $user->getPdo()->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    session_destroy();

    header("Location: ../index.php");
    exit;
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
