<?php
require_once __DIR__ . '/../config/db.php';

class Transaction {
    private $conn;
    private $table_name = "transaction_list";

    public $id;
    public $user_id;
    public $mechanic_id;
    public $code;
    public $client_name;
    public $contact;
    public $email;
    public $address;
    public $amount;
    public $status;
    public $date_created;
    public $date_updated;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Criar transação
    public function create() {
        // Gerar código da transação
        $this->code = $this->generateTransactionCode();
        
        $query = "INSERT INTO " . $this->table_name . " 
                 SET user_id=:user_id, mechanic_id=:mechanic_id, code=:code, client_name=:client_name, 
                     contact=:contact, email=:email, address=:address, amount=:amount, status=:status";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->mechanic_id = htmlspecialchars(strip_tags($this->mechanic_id));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->contact = htmlspecialchars(strip_tags($this->contact));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind valores
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":mechanic_id", $this->mechanic_id);
        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":contact", $this->contact);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar todas as transações
    public function readAll() {
        $query = "SELECT t.id, t.user_id, t.mechanic_id, t.code, t.client_name, t.contact, 
                         t.email, t.address, t.amount, t.status, t.date_created, t.date_updated,
                         u.firstname as user_firstname, u.lastname as user_lastname,
                         m.firstname as mechanic_firstname, m.lastname as mechanic_lastname
                 FROM " . $this->table_name . " t
                 LEFT JOIN users u ON t.user_id = u.id
                 LEFT JOIN mechanic_list m ON t.mechanic_id = m.id
                 ORDER BY t.date_created DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ler uma transação específica
    public function readOne() {
        $query = "SELECT t.id, t.user_id, t.mechanic_id, t.code, t.client_name, t.contact, 
                         t.email, t.address, t.amount, t.status, t.date_created, t.date_updated,
                         u.firstname as user_firstname, u.lastname as user_lastname,
                         m.firstname as mechanic_firstname, m.lastname as mechanic_lastname
                 FROM " . $this->table_name . " t
                 LEFT JOIN users u ON t.user_id = u.id
                 LEFT JOIN mechanic_list m ON t.mechanic_id = m.id
                 WHERE t.id = :id 
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->mechanic_id = $row['mechanic_id'];
            $this->code = $row['code'];
            $this->client_name = $row['client_name'];
            $this->contact = $row['contact'];
            $this->email = $row['email'];
            $this->address = $row['address'];
            $this->amount = $row['amount'];
            $this->status = $row['status'];
            $this->date_created = $row['date_created'];
            $this->date_updated = $row['date_updated'];
            return $row;
        }
        return false;
    }

    // Atualizar transação
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET user_id=:user_id, mechanic_id=:mechanic_id, client_name=:client_name, 
                     contact=:contact, email=:email, address=:address, amount=:amount, status=:status 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->mechanic_id = htmlspecialchars(strip_tags($this->mechanic_id));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->contact = htmlspecialchars(strip_tags($this->contact));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind valores
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':mechanic_id', $this->mechanic_id);
        $stmt->bindParam(':client_name', $this->client_name);
        $stmt->bindParam(':contact', $this->contact);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Deletar transação
    public function delete() {
        // Primeiro deletar produtos e serviços relacionados
        $this->deleteTransactionProducts();
        $this->deleteTransactionServices();
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Gerar código da transação
    private function generateTransactionCode() {
        $date = date('Ymd');
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE DATE(date_created) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['count'] + 1;
        return $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Adicionar produto à transação
    public function addProduct($product_id, $qty, $price) {
        $query = "INSERT INTO transaction_products SET transaction_id=:transaction_id, product_id=:product_id, qty=:qty, price=:price";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':transaction_id', $this->id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':qty', $qty);
        $stmt->bindParam(':price', $price);
        
        return $stmt->execute();
    }

    // Adicionar serviço à transação
    public function addService($service_id, $price) {
        $query = "INSERT INTO transaction_services SET transaction_id=:transaction_id, service_id=:service_id, price=:price";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':transaction_id', $this->id);
        $stmt->bindParam(':service_id', $service_id);
        $stmt->bindParam(':price', $price);
        
        return $stmt->execute();
    }

    // Obter produtos da transação
    public function getTransactionProducts() {
        $query = "SELECT tp.product_id, tp.qty, tp.price, p.name, p.description
                 FROM transaction_products tp
                 JOIN product_list p ON tp.product_id = p.id
                 WHERE tp.transaction_id = :transaction_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':transaction_id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Obter serviços da transação
    public function getTransactionServices() {
        $query = "SELECT ts.service_id, ts.price, s.name, s.description
                 FROM transaction_services ts
                 JOIN service_list s ON ts.service_id = s.id
                 WHERE ts.transaction_id = :transaction_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':transaction_id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Deletar produtos da transação
    public function deleteTransactionProducts() {
        $query = "DELETE FROM transaction_products WHERE transaction_id = :transaction_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':transaction_id', $this->id);
        return $stmt->execute();
    }

    // Deletar serviços da transação
    public function deleteTransactionServices() {
        $query = "DELETE FROM transaction_services WHERE transaction_id = :transaction_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':transaction_id', $this->id);
        return $stmt->execute();
    }

    // Buscar transações
    public function search($keywords) {
        $query = "SELECT t.id, t.user_id, t.mechanic_id, t.code, t.client_name, t.contact, 
                         t.email, t.address, t.amount, t.status, t.date_created, t.date_updated,
                         u.firstname as user_firstname, u.lastname as user_lastname,
                         m.firstname as mechanic_firstname, m.lastname as mechanic_lastname
                 FROM " . $this->table_name . " t
                 LEFT JOIN users u ON t.user_id = u.id
                 LEFT JOIN mechanic_list m ON t.mechanic_id = m.id
                 WHERE t.code LIKE :keywords OR t.client_name LIKE :keywords OR t.contact LIKE :keywords
                 ORDER BY t.date_created DESC";

        $stmt = $this->conn->prepare($query);
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();
        return $stmt;
    }

    // Obter status em texto
    public function getStatusText() {
        switch ($this->status) {
            case 0: return 'Pendente';
            case 1: return 'Em Progresso';
            case 2: return 'Concluído';
            case 3: return 'Pago';
            case 4: return 'Cancelado';
            default: return 'Desconhecido';
        }
    }

    // Relatório de vendas por período
    public function getSalesReport($start_date, $end_date) {
        $query = "SELECT t.id, t.code, t.client_name, t.amount, t.status, t.date_created,
                         u.firstname as user_firstname, u.lastname as user_lastname,
                         m.firstname as mechanic_firstname, m.lastname as mechanic_lastname
                 FROM " . $this->table_name . " t
                 LEFT JOIN users u ON t.user_id = u.id
                 LEFT JOIN mechanic_list m ON t.mechanic_id = m.id
                 WHERE DATE(t.date_created) BETWEEN :start_date AND :end_date
                 ORDER BY t.date_created DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }
}
?>
