<?php
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../auth/Auth.php';

class InventoryController {
    private $inventory;
    private $auth;

    public function __construct() {
        $this->inventory = new Inventory();
        $this->auth = new Auth();
    }

    // Listar todo o estoque
    public function index() {
        try {
            $stmt = $this->inventory->readAll();
            $inventory = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($inventory, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Estoque listado com sucesso',
                'data' => $inventory
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar estoque: ' . $e->getMessage()
            ];
        }
    }

    // Obter entrada de estoque específica
    public function show($id) {
        try {
            $this->inventory->id = $id;
            $result = $this->inventory->readOne();

            if ($result) {
                return [
                    'status' => 'success',
                    'message' => 'Entrada de estoque encontrada',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Entrada de estoque não encontrada'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao buscar entrada de estoque: ' . $e->getMessage()
            ];
        }
    }

    // Criar entrada de estoque
    public function store($data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['product_id']) || empty($data['quantity']) || empty($data['stock_date'])) {
                return [
                    'status' => 'error',
                    'message' => 'Produto, quantidade e data do estoque são obrigatórios'
                ];
            }

            // Validar se quantidade é positiva
            if ($data['quantity'] <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Quantidade deve ser maior que zero'
                ];
            }

            // Definir propriedades
            $this->inventory->product_id = $data['product_id'];
            $this->inventory->quantity = $data['quantity'];
            $this->inventory->stock_date = $data['stock_date'];

            if ($this->inventory->create()) {
                return [
                    'status' => 'success',
                    'message' => 'Entrada de estoque criada com sucesso',
                    'data' => ['id' => $this->inventory->id]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao criar entrada de estoque'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar entrada de estoque
    public function update($id, $data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['product_id']) || empty($data['quantity']) || empty($data['stock_date'])) {
                return [
                    'status' => 'error',
                    'message' => 'Produto, quantidade e data do estoque são obrigatórios'
                ];
            }

            // Validar se quantidade é positiva
            if ($data['quantity'] <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Quantidade deve ser maior que zero'
                ];
            }

            $this->inventory->id = $id;

            // Verificar se entrada existe
            if (!$this->inventory->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Entrada de estoque não encontrada'
                ];
            }

            // Definir propriedades
            $this->inventory->product_id = $data['product_id'];
            $this->inventory->quantity = $data['quantity'];
            $this->inventory->stock_date = $data['stock_date'];

            if ($this->inventory->update()) {
                return [
                    'status' => 'success',
                    'message' => 'Entrada de estoque atualizada com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar entrada de estoque'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Deletar entrada de estoque
    public function delete($id) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->inventory->id = $id;

            // Verificar se entrada existe
            if (!$this->inventory->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Entrada de estoque não encontrada'
                ];
            }

            if ($this->inventory->delete()) {
                return [
                    'status' => 'success',
                    'message' => 'Entrada de estoque deletada com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao deletar entrada de estoque'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Obter estoque total por produto
    public function getStockByProduct($product_id) {
        try {
            $stock = $this->inventory->getStockByProduct($product_id);

            return [
                'status' => 'success',
                'message' => 'Estoque obtido com sucesso',
                'data' => ['product_id' => $product_id, 'total_stock' => $stock]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao obter estoque: ' . $e->getMessage()
            ];
        }
    }

    // Obter produtos com baixo estoque
    public function getLowStock($limit = 10) {
        try {
            $stmt = $this->inventory->getLowStock($limit);
            $lowStock = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($lowStock, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Produtos com baixo estoque listados com sucesso',
                'data' => $lowStock
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar produtos com baixo estoque: ' . $e->getMessage()
            ];
        }
    }

    // Relatório de movimentação de estoque
    public function getStockMovement($start_date, $end_date) {
        try {
            $stmt = $this->inventory->getStockMovement($start_date, $end_date);
            $movements = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($movements, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Relatório de movimentação obtido com sucesso',
                'data' => $movements
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ];
        }
    }
}
?>
