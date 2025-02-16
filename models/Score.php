<?php

require_once 'DatabaseConnection.php';

class Score extends DatabaseConnection {
    private $userId;

    public function __construct($userId) {
        parent::__construct(); 
        $this->userId = $userId;
    }

    public function getLastScore() {
        $stmt = $this->getPdo()->prepare("SELECT score FROM user_scores WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveScore($score) {
        $stmt = $this->getPdo()->prepare("INSERT INTO user_scores (user_id, score) VALUES (?, ?)");
        return $stmt->execute([$this->userId, $score]);
    }
}
