<?php
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../auth/Auth.php';

class ServiceController {
    private $service;
    private $auth;

    public function __construct() {
        $this->service = new Service();
        $this->auth = new Auth();
    }

    // Listar todos os serviços
    public function index() {
        try {
            $stmt = $this->service->readAll();
            $services = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($services, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Serviços listados com sucesso',
                'data' => $services
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar serviços: ' . $e->getMessage()
            ];
        }
    }

    // Obter serviço específico
    public function show($id) {
        try {
            $this->service->id = $id;

            if ($this->service->readOne()) {
                $service = array(
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'description' => $this->service->description,
                    'price' => $this->service->price,
                    'status' => $this->service->status,
                    'date_created' => $this->service->date_created,
                    'date_updated' => $this->service->date_updated
                );

                return [
                    'status' => 'success',
                    'message' => 'Serviço encontrado',
                    'data' => $service
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Serviço não encontrado'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao buscar serviço: ' . $e->getMessage()
            ];
        }
    }

    // Criar serviço
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

            // Verificar se serviço já existe
            $this->service->name = $data['name'];
            if ($this->service->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'Serviço com este nome já existe'
                ];
            }

            // Definir propriedades
            $this->service->name = $data['name'];
            $this->service->description = $data['description'];
            $this->service->price = $data['price'];
            $this->service->status = isset($data['status']) ? $data['status'] : 1;

            if ($this->service->create()) {
                return [
                    'status' => 'success',
                    'message' => 'Serviço criado com sucesso',
                    'data' => ['id' => $this->service->id]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao criar serviço'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar serviço
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

            $this->service->id = $id;

            // Verificar se serviço existe
            if (!$this->service->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Serviço não encontrado'
                ];
            }

            // Verificar se nome já existe (exceto este serviço)
            $tempService = new Service();
            $tempService->name = $data['name'];
            $tempService->id = $id;
            if ($tempService->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'Serviço com este nome já existe'
                ];
            }

            // Definir propriedades
            $this->service->name = $data['name'];
            $this->service->description = $data['description'];
            $this->service->price = $data['price'];
            $this->service->status = isset($data['status']) ? $data['status'] : $this->service->status;

            if ($this->service->update()) {
                return [
                    'status' => 'success',
                    'message' => 'Serviço atualizado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar serviço'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Deletar serviço
    public function delete($id) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->service->id = $id;

            // Verificar se serviço existe
            if (!$this->service->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Serviço não encontrado'
                ];
            }

            if ($this->service->delete()) {
                return [
                    'status' => 'success',
                    'message' => 'Serviço deletado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao deletar serviço'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Buscar serviços
    public function search($keywords) {
        try {
            $stmt = $this->service->search($keywords);
            $services = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($services, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Busca realizada com sucesso',
                'data' => $services
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
