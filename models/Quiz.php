<?php

require_once 'DatabaseConnection.php';

class Quiz extends DatabaseConnection {
    private $id;
    private $name;
    private $image;
    private $numQuestions;
    private $createdBy;

    public function __construct() {
        parent::__construct();
    }

    // GETTERS
    public function getId() { 
        return $this->id; 
    }
    public function getName() { 
        return $this->name; 
    }
    public function getImage() { 
        return $this->image; 
    }
    public function getNumQuestions() { 
        return $this->numQuestions; 
    }
    public function getCreatedBy() { 
        return $this->createdBy; 
    }

    // SETTERS
    public function setName($name) { 
        $this->name = htmlspecialchars(trim($name)); 
    }
    public function setImage($image) { 
        $this->image = $image; 
    }
    public function setNumQuestions($numQuestions) { 
        $this->numQuestions = (int) $numQuestions; 
    }
    public function setCreatedBy($createdBy) { 
        $this->createdBy = (int) $createdBy; 
    }


    // check if it exists
    public function isNameTaken() {
        $stmt = $this->getPdo()->prepare("SELECT id FROM quizzes WHERE name = ?");
        $stmt->execute([$this->name]);
        return $stmt->rowCount() > 0;
    }

    // save the quiz in database
    public function save() {
        if ($this->isNameTaken()) {
            throw new Exception("Ce nom de quiz est déjà pris.");
        }

        $stmt = $this->getPdo()->prepare("INSERT INTO quizzes (name, image, num_questions, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$this->name, $this->image, $this->numQuestions, $this->createdBy])) {
            $this->id = $this->getPdo()->lastInsertId();
            return $this->id;
        } else {
            throw new Exception("Erreur lors de l'enregistrement du quiz.");
        }
    }

    // Upload images with verification and security
    public static function uploadImage($file) {
        $uploadDir = __DIR__ . '/../uploads/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 2 * 1024 * 1024;

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $imageInfo = getimagesize($file['tmp_name']);

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Format d'image non autorisé.");
        }

        if ($file['size'] > $maxFileSize) {
            throw new Exception("Fichier trop volumineux (max 2 Mo).");
        }

        if ($imageInfo === false) {
            throw new Exception("Le fichier n'est pas une image valide.");
        }

        $newFileName = uniqid('quiz_img_') . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception("Erreur lors de l'upload de l'image.");
        }

        return $newFileName;
    }

    // Get all the quizzes
    public static function getAllQuizzes() {
        $db = new DatabaseConnection();
        $pdo = $db->getPdo();
        
        $stmt = $pdo->query("SELECT id, name, image FROM quizzes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
