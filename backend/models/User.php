<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $firstname;
    public $lastname;
    public $username;
    public $password;
    public $avatar;
    public $last_login;
    public $type;
    public $date_added;
    public $date_updated;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Criar usuário
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET firstname=:firstname, lastname=:lastname, username=:username, 
                     password=:password, avatar=:avatar, type=:type";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = md5($this->password); // Mantendo MD5 para compatibilidade
        $this->avatar = htmlspecialchars(strip_tags($this->avatar));
        $this->type = htmlspecialchars(strip_tags($this->type));

        // Bind valores
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":avatar", $this->avatar);
        $stmt->bindParam(":type", $this->type);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar todos os usuários
    public function readAll() {
        $query = "SELECT id, firstname, lastname, username, avatar, last_login, type, date_added, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE type > 0 
                 ORDER BY firstname ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ler um usuário específico
    public function readOne() {
        $query = "SELECT id, firstname, lastname, username, avatar, last_login, type, date_added, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE id = :id AND type > 0 
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->username = $row['username'];
            $this->avatar = $row['avatar'];
            $this->last_login = $row['last_login'];
            $this->type = $row['type'];
            $this->date_added = $row['date_added'];
            $this->date_updated = $row['date_updated'];
            return true;
        }
        return false;
    }

    // Atualizar usuário
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET firstname=:firstname, lastname=:lastname, username=:username, 
                     avatar=:avatar, type=:type";
        
        // Se senha foi fornecida, incluir na atualização
        if (!empty($this->password)) {
            $query .= ", password=:password";
        }
        
        $query .= " WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->avatar = htmlspecialchars(strip_tags($this->avatar));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind valores
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':avatar', $this->avatar);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':id', $this->id);

        if (!empty($this->password)) {
            $this->password = md5($this->password);
            $stmt->bindParam(':password', $this->password);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Deletar usuário (definir type como 0)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET type = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar usuários
    public function search($keywords) {
        $query = "SELECT id, firstname, lastname, username, avatar, last_login, type, date_added, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE type > 0 AND (firstname LIKE :keywords OR lastname LIKE :keywords OR username LIKE :keywords)
                 ORDER BY firstname ASC";

        $stmt = $this->conn->prepare($query);
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();
        return $stmt;
    }

    // Verificar se username existe
    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username AND type > 0";
        if (!empty($this->id)) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        if (!empty($this->id)) {
            $stmt->bindParam(':id', $this->id);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Atualizar último login
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Alterar senha
    public function changePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = md5($new_password);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Obter nome completo
    public function getFullName() {
        return $this->firstname . " " . $this->lastname;
    }

    // Verificar se é admin
    public function isAdmin() {
        return $this->type == 1;
    }
}
?>
