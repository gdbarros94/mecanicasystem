<?php
require_once __DIR__ . '/../config/db.php';

class Inventory {
    private $conn;
    private $table_name = "inventory_list";

    public $id;
    public $product_id;
    public $quantity;
    public $stock_date;
    public $date_created;
    public $date_updated;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Criar entrada de estoque
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET product_id=:product_id, quantity=:quantity, stock_date=:stock_date";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->stock_date = htmlspecialchars(strip_tags($this->stock_date));

        // Bind valores
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":stock_date", $this->stock_date);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar todo o estoque com informações dos produtos
    public function readAll() {
        $query = "SELECT i.id, i.product_id, i.quantity, i.stock_date, i.date_created, i.date_updated,
                         p.name as product_name, p.description as product_description, p.price as product_price
                 FROM " . $this->table_name . " i
                 JOIN product_list p ON i.product_id = p.id
                 WHERE p.delete_flag = 0
                 ORDER BY i.date_created DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ler entrada de estoque específica
    public function readOne() {
        $query = "SELECT i.id, i.product_id, i.quantity, i.stock_date, i.date_created, i.date_updated,
                         p.name as product_name, p.description as product_description, p.price as product_price
                 FROM " . $this->table_name . " i
                 JOIN product_list p ON i.product_id = p.id
                 WHERE i.id = :id AND p.delete_flag = 0
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->product_id = $row['product_id'];
            $this->quantity = $row['quantity'];
            $this->stock_date = $row['stock_date'];
            $this->date_created = $row['date_created'];
            $this->date_updated = $row['date_updated'];
            return $row;
        }
        return false;
    }

    // Atualizar entrada de estoque
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET product_id=:product_id, quantity=:quantity, stock_date=:stock_date 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->stock_date = htmlspecialchars(strip_tags($this->stock_date));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind valores
        $stmt->bindParam(':product_id', $this->product_id);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':stock_date', $this->stock_date);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Deletar entrada de estoque
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obter estoque total por produto
    public function getStockByProduct($product_id) {
        $query = "SELECT SUM(quantity) as total_stock FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_stock'] ? $row['total_stock'] : 0;
    }

    // Listar produtos com baixo estoque
    public function getLowStock($limit = 10) {
        $query = "SELECT p.id, p.name, p.description, p.price, 
                         COALESCE(SUM(i.quantity), 0) as total_stock
                 FROM product_list p
                 LEFT JOIN " . $this->table_name . " i ON p.id = i.product_id
                 WHERE p.delete_flag = 0
                 GROUP BY p.id, p.name, p.description, p.price
                 HAVING total_stock < :limit
                 ORDER BY total_stock ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit);
        $stmt->execute();
        return $stmt;
    }

    // Relatório de movimentação de estoque por período
    public function getStockMovement($start_date, $end_date) {
        $query = "SELECT i.id, i.product_id, i.quantity, i.stock_date, i.date_created,
                         p.name as product_name, p.description as product_description
                 FROM " . $this->table_name . " i
                 JOIN product_list p ON i.product_id = p.id
                 WHERE i.stock_date BETWEEN :start_date AND :end_date
                 AND p.delete_flag = 0
                 ORDER BY i.stock_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }
}
?>
