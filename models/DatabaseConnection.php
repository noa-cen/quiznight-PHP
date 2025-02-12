<?php

class DatabaseConnection {
    private $pdo;
    private $host;
    private $dbname;
    private $user;
    private $password;

    public function __construct($host = "localhost", $dbname = "quiz_night", $user = "root", $password = "")
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->password = $password;

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getPdo() {
        return $this->pdo;
    }
}