<?php

require_once 'DatabaseConnection.php';

class Question extends DatabaseConnection {
    private $quiz_id;
    private $question_text;

    // Constructeur : initialisation des attributs
    public function __construct($quizId = null, $questionText = null) {
        parent::__construct(); // Appel au constructeur de DatabaseConnection
        $this->quiz_id = $quizId;
        $this->question_text = $questionText;
    }

    // Getters
    public function getQuizId() {
        return $this->quiz_id;
    }

    public function getQuestionText() {
        return $this->question_text;
    }

    // Setters
    public function setQuizId($quizId) {
        $this->quiz_id = $quizId;
    }

    public function setQuestionText($questionText) {
        $this->question_text = $questionText;
    }

    // Ajouter une question
    public function addQuestion($quizId, $questionText, $answers, $correctAnswer) {
        // Préparer la requête pour ajouter la question dans la base de données
        $stmt = $this->getPdo()->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)");
        $stmt->execute([$quizId, $questionText]);

        // Récupérer l'ID de la dernière question insérée
        $questionId = $this->getPdo()->lastInsertId();

        // Ajouter les réponses de la question
        foreach ($answers as $index => $answer) {
            $isCorrect = ($index === $correctAnswer) ? 1 : 0;
            $stmt = $this->getPdo()->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->execute([$questionId, $answer, $isCorrect]);
        }
    }
}
