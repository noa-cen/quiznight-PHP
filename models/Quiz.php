<?php
require_once 'DatabaseConnection.php';

class Quiz extends DatabaseConnection {
    private $id;
    private $name;
    private $image;
    private $numQuestions;
    private $description;
    private $createdBy;

    public function __construct($id = null, $name = null, $image = null, $numQuestions = null, $description = null, $createdBy = null) {
        parent::__construct(); 
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->numQuestions = $numQuestions;
        $this->description = $description;
        $this->createdBy = $createdBy;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getImage() { return $this->image; }
    public function getNumQuestions() { return $this->numQuestions; }
    public function getDescription() { return $this->description; }
    public function getCreatedBy() { return $this->createdBy; }

    // Setters
    public function setId($id) { $this->id = (int)$id; }
    public function setName($name) { $this->name = htmlspecialchars(trim($name)); }
    public function setImage($image) { $this->image = $image; }
    public function setNumQuestions($numQuestions) { $this->numQuestions = (int)$numQuestions; }
    public function setDescription($description) { $this->description = htmlspecialchars(trim($description)); }
    public function setCreatedBy($createdBy) { $this->createdBy = (int)$createdBy; }

    public function isNameTaken() {
        $stmt = $this->getPdo()->prepare("SELECT id FROM quizzes WHERE name = ?");
        $stmt->execute([$this->name]);
        return $stmt->rowCount() > 0;
    }

    public function save() {
        if ($this->isNameTaken()) {
            throw new Exception("Ce nom de quiz est déjà pris.");
        }
        $stmt = $this->getPdo()->prepare("INSERT INTO quizzes (name, image, num_questions, description, created_by) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$this->name, $this->image, $this->numQuestions, $this->description, $this->createdBy])) {
            $this->id = $this->getPdo()->lastInsertId();
            return $this->id;
        } else {
            throw new Exception("Erreur lors de l'enregistrement du quiz.");
        }
    }

    public function update() {
        $stmt = $this->getPdo()->prepare("UPDATE quizzes SET name = ?, image = ?, num_questions = ?, description = ? WHERE id = ?");
        return $stmt->execute([$this->name, $this->image, $this->numQuestions, $this->description, $this->id]);
    }

    public function getAllQuizzes() {
        $stmt = $this->getPdo()->query("SELECT id, name, image, num_questions, description, created_by, created_at FROM quizzes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuizDetails() {
        $stmt = $this->getPdo()->prepare("SELECT id, name, image, num_questions, description, created_by, created_at FROM quizzes WHERE id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function uploadImage($file) {
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

    public function delete() {
        $stmt = $this->getPdo()->prepare("DELETE FROM quizzes WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
?>
