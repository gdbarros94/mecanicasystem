<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../auth/Auth.php';

class TransactionController {
    private $transaction;
    private $auth;

    public function __construct() {
        $this->transaction = new Transaction();
        $this->auth = new Auth();
    }

    // Listar todas as transações
    public function index() {
        try {
            $stmt = $this->transaction->readAll();
            $transactions = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['status_text'] = $this->getStatusText($row['status']);
                array_push($transactions, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Transações listadas com sucesso',
                'data' => $transactions
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar transações: ' . $e->getMessage()
            ];
        }
    }

    // Obter transação específica
    public function show($id) {
        try {
            $this->transaction->id = $id;
            $result = $this->transaction->readOne();

            if ($result) {
                $result['status_text'] = $this->getStatusText($result['status']);
                
                // Obter produtos e serviços da transação
                $products_stmt = $this->transaction->getTransactionProducts();
                $services_stmt = $this->transaction->getTransactionServices();
                
                $products = array();
                $services = array();
                
                while ($product = $products_stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($products, $product);
                }
                
                while ($service = $services_stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($services, $service);
                }
                
                $result['products'] = $products;
                $result['services'] = $services;

                return [
                    'status' => 'success',
                    'message' => 'Transação encontrada',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Transação não encontrada'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao buscar transação: ' . $e->getMessage()
            ];
        }
    }

    // Criar transação
    public function store($data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['client_name']) || empty($data['contact'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome do cliente e contato são obrigatórios'
                ];
            }

            // Definir propriedades
            $this->transaction->user_id = $userData['user_id'];
            $this->transaction->mechanic_id = isset($data['mechanic_id']) ? $data['mechanic_id'] : null;
            $this->transaction->client_name = $data['client_name'];
            $this->transaction->contact = $data['contact'];
            $this->transaction->email = isset($data['email']) ? $data['email'] : '';
            $this->transaction->address = isset($data['address']) ? $data['address'] : '';
            $this->transaction->amount = isset($data['amount']) ? $data['amount'] : 0;
            $this->transaction->status = isset($data['status']) ? $data['status'] : 0;

            if ($this->transaction->create()) {
                // Adicionar produtos se fornecidos
                if (isset($data['products']) && is_array($data['products'])) {
                    foreach ($data['products'] as $product) {
                        if (isset($product['product_id'], $product['qty'], $product['price'])) {
                            $this->transaction->addProduct($product['product_id'], $product['qty'], $product['price']);
                        }
                    }
                }

                // Adicionar serviços se fornecidos
                if (isset($data['services']) && is_array($data['services'])) {
                    foreach ($data['services'] as $service) {
                        if (isset($service['service_id'], $service['price'])) {
                            $this->transaction->addService($service['service_id'], $service['price']);
                        }
                    }
                }

                return [
                    'status' => 'success',
                    'message' => 'Transação criada com sucesso',
                    'data' => [
                        'id' => $this->transaction->id,
                        'code' => $this->transaction->code
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao criar transação'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar transação
    public function update($id, $data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['client_name']) || empty($data['contact'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome do cliente e contato são obrigatórios'
                ];
            }

            $this->transaction->id = $id;

            // Verificar se transação existe
            if (!$this->transaction->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Transação não encontrada'
                ];
            }

            // Definir propriedades
            $this->transaction->user_id = $userData['user_id'];
            $this->transaction->mechanic_id = isset($data['mechanic_id']) ? $data['mechanic_id'] : $this->transaction->mechanic_id;
            $this->transaction->client_name = $data['client_name'];
            $this->transaction->contact = $data['contact'];
            $this->transaction->email = isset($data['email']) ? $data['email'] : $this->transaction->email;
            $this->transaction->address = isset($data['address']) ? $data['address'] : $this->transaction->address;
            $this->transaction->amount = isset($data['amount']) ? $data['amount'] : $this->transaction->amount;
            $this->transaction->status = isset($data['status']) ? $data['status'] : $this->transaction->status;

            if ($this->transaction->update()) {
                // Atualizar produtos se fornecidos
                if (isset($data['products']) && is_array($data['products'])) {
                    $this->transaction->deleteTransactionProducts();
                    foreach ($data['products'] as $product) {
                        if (isset($product['product_id'], $product['qty'], $product['price'])) {
                            $this->transaction->addProduct($product['product_id'], $product['qty'], $product['price']);
                        }
                    }
                }

                // Atualizar serviços se fornecidos
                if (isset($data['services']) && is_array($data['services'])) {
                    $this->transaction->deleteTransactionServices();
                    foreach ($data['services'] as $service) {
                        if (isset($service['service_id'], $service['price'])) {
                            $this->transaction->addService($service['service_id'], $service['price']);
                        }
                    }
                }

                return [
                    'status' => 'success',
                    'message' => 'Transação atualizada com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar transação'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Deletar transação
    public function delete($id) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->transaction->id = $id;

            // Verificar se transação existe
            if (!$this->transaction->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Transação não encontrada'
                ];
            }

            if ($this->transaction->delete()) {
                return [
                    'status' => 'success',
                    'message' => 'Transação deletada com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao deletar transação'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Buscar transações
    public function search($keywords) {
        try {
            $stmt = $this->transaction->search($keywords);
            $transactions = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['status_text'] = $this->getStatusText($row['status']);
                array_push($transactions, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Busca realizada com sucesso',
                'data' => $transactions
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro na busca: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar status da transação
    public function updateStatus($id, $status) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->transaction->id = $id;

            // Verificar se transação existe
            if (!$this->transaction->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Transação não encontrada'
                ];
            }

            // Validar status
            if (!in_array($status, [0, 1, 2, 3, 4])) {
                return [
                    'status' => 'error',
                    'message' => 'Status inválido'
                ];
            }

            $this->transaction->status = $status;

            if ($this->transaction->update()) {
                return [
                    'status' => 'success',
                    'message' => 'Status atualizado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar status'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Relatório de vendas
    public function getSalesReport($start_date, $end_date) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $stmt = $this->transaction->getSalesReport($start_date, $end_date);
            $sales = array();
            $total_amount = 0;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['status_text'] = $this->getStatusText($row['status']);
                $total_amount += $row['amount'];
                array_push($sales, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Relatório de vendas gerado com sucesso',
                'data' => [
                    'sales' => $sales,
                    'total_amount' => $total_amount,
                    'total_transactions' => count($sales),
                    'period' => [
                        'start_date' => $start_date,
                        'end_date' => $end_date
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ];
        }
    }

    // Função auxiliar para obter texto do status
    private function getStatusText($status) {
        switch ($status) {
            case 0: return 'Pendente';
            case 1: return 'Em Progresso';
            case 2: return 'Concluído';
            case 3: return 'Pago';
            case 4: return 'Cancelado';
            default: return 'Desconhecido';
        }
    }
}
?>
