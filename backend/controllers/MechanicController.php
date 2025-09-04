<?php
require_once __DIR__ . '/../models/Mechanic.php';
require_once __DIR__ . '/../auth/Auth.php';

class MechanicController {
    private $mechanic;
    private $auth;

    public function __construct() {
        $this->mechanic = new Mechanic();
        $this->auth = new Auth();
    }

    // Listar todos os mecânicos
    public function index() {
        try {
            $stmt = $this->mechanic->readAll();
            $mechanics = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['full_name'] = $row['firstname'];
                if (!empty($row['middlename'])) {
                    $row['full_name'] .= ' ' . $row['middlename'];
                }
                $row['full_name'] .= ' ' . $row['lastname'];
                array_push($mechanics, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Mecânicos listados com sucesso',
                'data' => $mechanics
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar mecânicos: ' . $e->getMessage()
            ];
        }
    }

    // Obter mecânico específico
    public function show($id) {
        try {
            $this->mechanic->id = $id;

            if ($this->mechanic->readOne()) {
                $mechanic = array(
                    'id' => $this->mechanic->id,
                    'firstname' => $this->mechanic->firstname,
                    'middlename' => $this->mechanic->middlename,
                    'lastname' => $this->mechanic->lastname,
                    'full_name' => $this->mechanic->getFullName(),
                    'status' => $this->mechanic->status,
                    'date_added' => $this->mechanic->date_added,
                    'date_updated' => $this->mechanic->date_updated
                );

                return [
                    'status' => 'success',
                    'message' => 'Mecânico encontrado',
                    'data' => $mechanic
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Mecânico não encontrado'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao buscar mecânico: ' . $e->getMessage()
            ];
        }
    }

    // Criar mecânico
    public function store($data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['firstname']) || empty($data['lastname'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome e sobrenome são obrigatórios'
                ];
            }

            // Verificar se mecânico já existe
            $this->mechanic->firstname = $data['firstname'];
            $this->mechanic->lastname = $data['lastname'];
            if ($this->mechanic->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'Mecânico com este nome já existe'
                ];
            }

            // Definir propriedades
            $this->mechanic->firstname = $data['firstname'];
            $this->mechanic->middlename = isset($data['middlename']) ? $data['middlename'] : '';
            $this->mechanic->lastname = $data['lastname'];
            $this->mechanic->status = isset($data['status']) ? $data['status'] : 1;

            if ($this->mechanic->create()) {
                return [
                    'status' => 'success',
                    'message' => 'Mecânico criado com sucesso',
                    'data' => ['id' => $this->mechanic->id]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao criar mecânico'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar mecânico
    public function update($id, $data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Validar dados obrigatórios
            if (empty($data['firstname']) || empty($data['lastname'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome e sobrenome são obrigatórios'
                ];
            }

            $this->mechanic->id = $id;

            // Verificar se mecânico existe
            if (!$this->mechanic->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Mecânico não encontrado'
                ];
            }

            // Verificar se nome já existe (exceto este mecânico)
            $tempMechanic = new Mechanic();
            $tempMechanic->firstname = $data['firstname'];
            $tempMechanic->lastname = $data['lastname'];
            $tempMechanic->id = $id;
            if ($tempMechanic->exists()) {
                return [
                    'status' => 'error',
                    'message' => 'Mecânico com este nome já existe'
                ];
            }

            // Definir propriedades
            $this->mechanic->firstname = $data['firstname'];
            $this->mechanic->middlename = isset($data['middlename']) ? $data['middlename'] : $this->mechanic->middlename;
            $this->mechanic->lastname = $data['lastname'];
            $this->mechanic->status = isset($data['status']) ? $data['status'] : $this->mechanic->status;

            if ($this->mechanic->update()) {
                return [
                    'status' => 'success',
                    'message' => 'Mecânico atualizado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar mecânico'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Deletar mecânico
    public function delete($id) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->mechanic->id = $id;

            // Verificar se mecânico existe
            if (!$this->mechanic->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Mecânico não encontrado'
                ];
            }

            if ($this->mechanic->delete()) {
                return [
                    'status' => 'success',
                    'message' => 'Mecânico deletado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao deletar mecânico'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Buscar mecânicos
    public function search($keywords) {
        try {
            $stmt = $this->mechanic->search($keywords);
            $mechanics = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['full_name'] = $row['firstname'];
                if (!empty($row['middlename'])) {
                    $row['full_name'] .= ' ' . $row['middlename'];
                }
                $row['full_name'] .= ' ' . $row['lastname'];
                array_push($mechanics, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Busca realizada com sucesso',
                'data' => $mechanics
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro na busca: ' . $e->getMessage()
            ];
        }
    }

    // Listar mecânicos ativos
    public function getActiveMechanics() {
        try {
            $stmt = $this->mechanic->getActiveMechanics();
            $mechanics = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['full_name'] = $row['firstname'];
                if (!empty($row['middlename'])) {
                    $row['full_name'] .= ' ' . $row['middlename'];
                }
                $row['full_name'] .= ' ' . $row['lastname'];
                array_push($mechanics, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Mecânicos ativos listados com sucesso',
                'data' => $mechanics
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar mecânicos ativos: ' . $e->getMessage()
            ];
        }
    }
}
?>
