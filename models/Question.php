<?php
require_once 'DatabaseConnection.php';

class Question extends DatabaseConnection {
    private $id;
    private $quiz_id;
    private $question_text;

    // Constructeur : initialisation des attributs
    public function __construct($id = null, $quizId = null, $questionText = null) {
        parent::__construct(); // Appel au constructeur de DatabaseConnection
        $this->id = $id;
        $this->quiz_id = $quizId;
        $this->question_text = $questionText;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getQuizId() { return $this->quiz_id; }
    public function getQuestionText() { return $this->question_text; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setQuizId($quizId) { $this->quiz_id = $quizId; }
    public function setQuestionText($questionText) { $this->question_text = $questionText; }

    public function create($quizId, $questionText, $answers, $correctAnswer) {
        $stmt = $this->getPdo()->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)");
        $stmt->execute([$quizId, $questionText]);
        $questionId = $this->getPdo()->lastInsertId();
        foreach ($answers as $index => $answer) {
            $isCorrect = ($index === $correctAnswer) ? 1 : 0;
            $stmt = $this->getPdo()->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->execute([$questionId, $answer, $isCorrect]);
        }
    }

    public function update($questionId, $questionText, $answers, $correctAnswer) {
        // Mise à jour du texte de la question
        $stmt = $this->getPdo()->prepare("UPDATE questions SET question_text = ? WHERE id = ?");
        $stmt->execute([$questionText, $questionId]);
        // Suppression des réponses existantes
        $stmt = $this->getPdo()->prepare("DELETE FROM answers WHERE question_id = ?");
        $stmt->execute([$questionId]);
        // Insertion des réponses mises à jour
        foreach ($answers as $index => $answer) {
            $isCorrect = ($index === $correctAnswer) ? 1 : 0;
            $stmt = $this->getPdo()->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->execute([$questionId, $answer, $isCorrect]);
        }
    }

    
    public function delete($questionId) {
        // Suppression des réponses associées
        $stmt = $this->getPdo()->prepare("DELETE FROM answers WHERE question_id = ?");
        $stmt->execute([$questionId]);
        // Suppression de la question
        $stmt = $this->getPdo()->prepare("DELETE FROM questions WHERE id = ?");
        return $stmt->execute([$questionId]);
    }

   
    public function getQuestionsByQuizId($quizId) {
        $stmt = $this->getPdo()->prepare("SELECT * FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAnswersByQuestionId($questionId) {
        $stmt = $this->getPdo()->prepare("SELECT * FROM answers WHERE question_id = ?");
        $stmt->execute([$questionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
