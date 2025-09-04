<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../auth/Auth.php';

class ProductController {
    private $product;
    private $auth;

    public function __construct() {
        $this->product = new Product();
        $this->auth = new Auth();
    }

    // Listar todos os produtos
    public function index() {
        try {
            $stmt = $this->product->readAll();
            $products = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Produtos listados com sucesso',
                'data' => $products
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar produtos: ' . $e->getMessage()
            ];
        }
    }

    // Obter produto específico
    public function show($id) {
        try {
            $this->product->id = $id;

            if ($this->product->readOne()) {
                $product = array(
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'description' => $this->product->description,
                    'price' => $this->product->price,
                    'image_path' => $this->product->image_path,
                    'status' => $this->product->status,
                    'date_created' => $this->product->date_created,
                    'date_updated' => $this->product->date_updated
                );

                return [
                    'status' => 'success',
                    'message' => 'Produto encontrado',
                    'data' => $product
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Produto não encontrado'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao buscar produto: ' . $e->getMessage()
            ];
        }
    }

    // Criar produto
    public function store($data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['name']) || empty($data['description']) || empty($data['price'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome, descrição e preço são obrigatórios'
                ];
            }

            // Verificar se produto já existe
            $this->product->name = $data['name'];
            if ($this->product->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'Produto com este nome já existe'
                ];
            }

            // Definir propriedades
            $this->product->name = $data['name'];
            $this->product->description = $data['description'];
            $this->product->price = $data['price'];
            $this->product->image_path = isset($data['image_path']) ? $data['image_path'] : '';
            $this->product->status = isset($data['status']) ? $data['status'] : 1;

            if ($this->product->create()) {
                return [
                    'status' => 'success',
                    'message' => 'Produto criado com sucesso',
                    'data' => ['id' => $this->product->id]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao criar produto'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar produto
    public function update($id, $data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['name']) || empty($data['description']) || empty($data['price'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome, descrição e preço são obrigatórios'
                ];
            }

            $this->product->id = $id;

            // Verificar se produto existe
            if (!$this->product->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Produto não encontrado'
                ];
            }

            // Verificar se nome já existe (exceto este produto)
            $tempProduct = new Product();
            $tempProduct->name = $data['name'];
            $tempProduct->id = $id;
            if ($tempProduct->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'Produto com este nome já existe'
                ];
            }

            // Definir propriedades
            $this->product->name = $data['name'];
            $this->product->description = $data['description'];
            $this->product->price = $data['price'];
            $this->product->image_path = isset($data['image_path']) ? $data['image_path'] : $this->product->image_path;
            $this->product->status = isset($data['status']) ? $data['status'] : $this->product->status;

            if ($this->product->update()) {
                return [
                    'status' => 'success',
                    'message' => 'Produto atualizado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar produto'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Deletar produto
    public function delete($id) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->product->id = $id;

            // Verificar se produto existe
            if (!$this->product->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Produto não encontrado'
                ];
            }

            if ($this->product->delete()) {
                return [
                    'status' => 'success',
                    'message' => 'Produto deletado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao deletar produto'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Upload de imagem do produto
    public function uploadImage($id, $file) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->product->id = $id;

            // Verificar se produto existe
            if (!$this->product->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Produto não encontrado'
                ];
            }

            return $this->product->uploadImage($file, $id);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Buscar produtos
    public function search($keywords) {
        try {
            $stmt = $this->product->search($keywords);
            $products = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Busca realizada com sucesso',
                'data' => $products
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro na busca: ' . $e->getMessage()
            ];
        }
    }
}
?>
