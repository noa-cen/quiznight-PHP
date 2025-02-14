<?php

require_once 'DatabaseConnection.php';

class Question extends DatabaseConnection {
    private $quiz_id;
    private $question_text;

    public function __construct($quizId = null, $questionText = null) {
        parent::__construct(); 
        $this->quiz_id = $quizId;
        $this->question_text = $questionText;
    }

    // GETTERS
    public function getQuizId() {
        return $this->quiz_id;
    }

    public function getQuestionText() {
        return $this->question_text;
    }

    // SETTERS
    public function setQuizId($quizId) {
        $this->quiz_id = $quizId;
    }

    public function setQuestionText($questionText) {
        $this->question_text = $questionText;
    }

    //CRUD

    //CREATE
    public function addQuestion($quizId, $questionText, $answers, $correctAnswer) {
        $stmt = $this->getPdo()->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)");
        $stmt->execute([$quizId, $questionText]);

        $questionId = $this->getPdo()->lastInsertId();

        // Connect the answers to the questions
        foreach ($answers as $index => $answer) {
            $isCorrect = ($index === $correctAnswer) ? 1 : 0;
            $stmt = $this->getPdo()->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->execute([$questionId, $answer, $isCorrect]);
        }
    }
}
