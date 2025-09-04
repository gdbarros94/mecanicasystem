<?php
require_once __DIR__ . '/../config/db.php';

class Service {
    private $conn;
    private $table_name = "service_list";

    public $id;
    public $name;
    public $description;
    public $price;
    public $status;
    public $delete_flag;
    public $date_created;
    public $date_updated;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Criar serviço
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET name=:name, description=:description, price=:price, status=:status";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind valores
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar todos os serviços
    public function readAll() {
        $query = "SELECT id, name, description, price, status, date_created, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE delete_flag = 0 
                 ORDER BY date_created DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ler um serviço específico
    public function readOne() {
        $query = "SELECT id, name, description, price, status, date_created, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE id = :id AND delete_flag = 0 
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->status = $row['status'];
            $this->date_created = $row['date_created'];
            $this->date_updated = $row['date_updated'];
            return true;
        }
        return false;
    }

    // Atualizar serviço
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name=:name, description=:description, price=:price, status=:status 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind valores
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Deletar serviço (soft delete)
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

    // Buscar serviços
    public function search($keywords) {
        $query = "SELECT id, name, description, price, status, date_created, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE delete_flag = 0 AND (name LIKE :keywords OR description LIKE :keywords)
                 ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();
        return $stmt;
    }

    // Verificar se serviço existe
    public function exists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = :name AND delete_flag = 0";
        if (!empty($this->id)) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        if (!empty($this->id)) {
            $stmt->bindParam(':id', $this->id);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>
