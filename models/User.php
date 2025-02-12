<?php

require_once "DatabaseConnection.php";

class User {
    private $pdo;
    private string $username;
    private string $email;
    private string $password;

    public function __construct(){
        $db = new DatabaseConnection();
        $this->pdo = $db->getPdo();
    }

    //SETTERS
    public function setUsername(string $username): void {
        $this->username = htmlspecialchars(trim($username));
    }

    public function setEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide !");
        }
        $this->email = $email;
    }

    public function setPassword(string $password): void {
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new Exception("Mot de passe invalide (8 caractères min, 1 majuscule, 1 chiffre)");
        }
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    //GETTERS
    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    //CRUD
    

    //Verification exists

    public function emailExists(string $email): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ? true : false;
    }

    public function userExists(string $username): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ? true : false;
    }

    //CREATE
    public function register():bool{
        if ($this->emailExists($this->email)){
            throw new Exception("Cet email est déjà utilisé !");
            }
        if ($this->userExists($this->username)){
                throw new Exception("Ce username est déjà utilisé !");
            }
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        return $stmt->execute([
            ':username' => $this->username,
            ':email' => $this->email,
            ':password' => $this->password
        ]);
        }

    // LOGIN 
    public function login(string $email, string $password): ?array {
        $stmt = $this->pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user; 
        } else {
            return null; 
        }
    }

    //UPDATE
    public function update(int $id):bool{
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':username' => $this->username,
            ':email' => $this->email
        ]);
    }

    //DELETE
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}