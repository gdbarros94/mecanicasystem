<?php
require_once __DIR__ . '/../config/db.php';

class Mechanic {
    private $conn;
    private $table_name = "mechanic_list";

    public $id;
    public $firstname;
    public $middlename;
    public $lastname;
    public $status;
    public $delete_flag;
    public $date_added;
    public $date_updated;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Criar mecânico
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET firstname=:firstname, middlename=:middlename, lastname=:lastname, status=:status";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->middlename = htmlspecialchars(strip_tags($this->middlename));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind valores
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":middlename", $this->middlename);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar todos os mecânicos
    public function readAll() {
        $query = "SELECT id, firstname, middlename, lastname, status, date_added, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE delete_flag = 0 
                 ORDER BY firstname ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ler um mecânico específico
    public function readOne() {
        $query = "SELECT id, firstname, middlename, lastname, status, date_added, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE id = :id AND delete_flag = 0 
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->firstname = $row['firstname'];
            $this->middlename = $row['middlename'];
            $this->lastname = $row['lastname'];
            $this->status = $row['status'];
            $this->date_added = $row['date_added'];
            $this->date_updated = $row['date_updated'];
            return true;
        }
        return false;
    }

    // Atualizar mecânico
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET firstname=:firstname, middlename=:middlename, lastname=:lastname, status=:status 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->middlename = htmlspecialchars(strip_tags($this->middlename));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind valores
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':middlename', $this->middlename);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Deletar mecânico (soft delete)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET delete_flag = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar mecânicos
    public function search($keywords) {
        $query = "SELECT id, firstname, middlename, lastname, status, date_added, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE delete_flag = 0 AND (firstname LIKE :keywords OR lastname LIKE :keywords OR middlename LIKE :keywords)
                 ORDER BY firstname ASC";

        $stmt = $this->conn->prepare($query);
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();
        return $stmt;
    }

    // Listar mecânicos ativos
    public function getActiveMechanics() {
        $query = "SELECT id, firstname, middlename, lastname, status, date_added, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE delete_flag = 0 AND status = 1 
                 ORDER BY firstname ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obter nome completo
    public function getFullName() {
        $fullName = $this->firstname;
        if (!empty($this->middlename)) {
            $fullName .= " " . $this->middlename;
        }
        $fullName .= " " . $this->lastname;
        return $fullName;
    }

    // Verificar se mecânico existe
    public function exists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                 WHERE firstname = :firstname AND lastname = :lastname AND delete_flag = 0";
        if (!empty($this->id)) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        if (!empty($this->id)) {
            $stmt->bindParam(':id', $this->id);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>
