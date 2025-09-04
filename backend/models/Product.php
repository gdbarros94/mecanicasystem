<?php
require_once __DIR__ . '/../config/db.php';

class Product {
    private $conn;
    private $table_name = "product_list";

    public $id;
    public $name;
    public $description;
    public $price;
    public $image_path;
    public $status;
    public $delete_flag;
    public $date_created;
    public $date_updated;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Criar produto
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET name=:name, description=:description, price=:price, image_path=:image_path, status=:status";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image_path = htmlspecialchars(strip_tags($this->image_path));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind valores
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar todos os produtos
    public function readAll() {
        $query = "SELECT id, name, description, price, image_path, status, date_created, date_updated 
                 FROM " . $this->table_name . " 
                 WHERE delete_flag = 0 
                 ORDER BY date_created DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ler um produto específico
    public function readOne() {
        $query = "SELECT id, name, description, price, image_path, status, date_created, date_updated 
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
            $this->image_path = $row['image_path'];
            $this->status = $row['status'];
            $this->date_created = $row['date_created'];
            $this->date_updated = $row['date_updated'];
            return true;
        }
        return false;
    }

    // Atualizar produto
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name=:name, description=:description, price=:price, image_path=:image_path, status=:status 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image_path = htmlspecialchars(strip_tags($this->image_path));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind valores
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image_path', $this->image_path);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Deletar produto (soft delete)
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

    // Buscar produtos
    public function search($keywords) {
        $query = "SELECT id, name, description, price, image_path, status, date_created, date_updated 
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

    // Upload de imagem do produto
    public function uploadImage($file, $product_id) {
        try {
            $upload_dir = __DIR__ . '/../../uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                return ['status' => 'error', 'message' => 'Tipo de arquivo não permitido'];
            }

            // Validar tamanho (máximo 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                return ['status' => 'error', 'message' => 'Arquivo muito grande. Máximo 5MB'];
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $product_id . '.png';
            $filepath = $upload_dir . $filename;

            // Processar e redimensionar imagem
            if ($file['type'] == 'image/jpeg') {
                $source = imagecreatefromjpeg($file['tmp_name']);
            } elseif ($file['type'] == 'image/png') {
                $source = imagecreatefrompng($file['tmp_name']);
            } elseif ($file['type'] == 'image/gif') {
                $source = imagecreatefromgif($file['tmp_name']);
            }

            if (!$source) {
                return ['status' => 'error', 'message' => 'Imagem inválida'];
            }

            // Obter dimensões originais
            list($width, $height) = getimagesize($file['tmp_name']);

            // Redimensionar se necessário (máximo 640x480)
            if ($width > 640 || $height > 480) {
                if ($width > $height) {
                    $new_width = 640;
                    $new_height = ($height * 640) / $width;
                } else {
                    $new_height = 480;
                    $new_width = ($width * 480) / $height;
                }
                
                $resized = imagescale($source, $new_width, $new_height);
            } else {
                $resized = $source;
            }

            // Remover arquivo anterior se existir
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Salvar imagem
            if (imagepng($resized, $filepath, 6)) {
                imagedestroy($resized);
                if ($source !== $resized) {
                    imagedestroy($source);
                }

                $image_path = 'uploads/products/' . $filename . '?v=' . time();
                
                // Atualizar caminho no banco
                $query = "UPDATE " . $this->table_name . " SET image_path = :image_path WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':image_path', $image_path);
                $stmt->bindParam(':id', $product_id);
                $stmt->execute();

                return ['status' => 'success', 'message' => 'Imagem enviada com sucesso', 'path' => $image_path];
            }

            return ['status' => 'error', 'message' => 'Erro ao salvar imagem'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    // Verificar se produto existe
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
