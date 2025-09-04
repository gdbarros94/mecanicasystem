<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../auth/Auth.php';

class UserController {
    private $user;
    private $auth;

    public function __construct() {
        $this->user = new User();
        $this->auth = new Auth();
    }

    // Listar todos os usuários
    public function index() {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $stmt = $this->user->readAll();
            $users = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['full_name'] = $row['firstname'] . ' ' . $row['lastname'];
                $row['type_text'] = $row['type'] == 1 ? 'Administrador' : 'Usuário';
                array_push($users, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Usuários listados com sucesso',
                'data' => $users
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao listar usuários: ' . $e->getMessage()
            ];
        }
    }

    // Obter usuário específico
    public function show($id) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $this->user->id = $id;

            if ($this->user->readOne()) {
                $user = array(
                    'id' => $this->user->id,
                    'firstname' => $this->user->firstname,
                    'lastname' => $this->user->lastname,
                    'username' => $this->user->username,
                    'avatar' => $this->user->avatar,
                    'last_login' => $this->user->last_login,
                    'type' => $this->user->type,
                    'type_text' => $this->user->type == 1 ? 'Administrador' : 'Usuário',
                    'full_name' => $this->user->getFullName(),
                    'date_added' => $this->user->date_added,
                    'date_updated' => $this->user->date_updated
                );

                return [
                    'status' => 'success',
                    'message' => 'Usuário encontrado',
                    'data' => $user
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Usuário não encontrado'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao buscar usuário: ' . $e->getMessage()
            ];
        }
    }

    // Criar usuário
    public function store($data) {
        try {
            // Verificar autenticação (apenas admins podem criar usuários)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado. Apenas administradores podem criar usuários'
                ];
            }

            // Validar dados obrigatórios
            if (empty($data['firstname']) || empty($data['lastname']) || empty($data['username']) || empty($data['password'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome, sobrenome, username e senha são obrigatórios'
                ];
            }

            // Verificar se username já existe
            $this->user->username = $data['username'];
            if ($this->user->usernameExists()) {
                return [
                    'status' => 'error',
                    'message' => 'Username já existe'
                ];
            }

            // Definir propriedades
            $this->user->firstname = $data['firstname'];
            $this->user->lastname = $data['lastname'];
            $this->user->username = $data['username'];
            $this->user->password = $data['password'];
            $this->user->avatar = isset($data['avatar']) ? $data['avatar'] : '';
            $this->user->type = isset($data['type']) ? $data['type'] : 2;

            if ($this->user->create()) {
                return [
                    'status' => 'success',
                    'message' => 'Usuário criado com sucesso',
                    'data' => ['id' => $this->user->id]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao criar usuário'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar usuário
    public function update($id, $data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Apenas admins podem editar outros usuários, ou o próprio usuário pode editar seus dados
            if ($userData['type'] != 1 && $userData['user_id'] != $id) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado'
                ];
            }

            // Validar dados obrigatórios
            if (empty($data['firstname']) || empty($data['lastname']) || empty($data['username'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nome, sobrenome e username são obrigatórios'
                ];
            }

            $this->user->id = $id;

            // Verificar se usuário existe
            if (!$this->user->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Usuário não encontrado'
                ];
            }

            // Verificar se username já existe (exceto este usuário)
            $tempUser = new User();
            $tempUser->username = $data['username'];
            $tempUser->id = $id;
            if ($tempUser->usernameExists()) {
                return [
                    'status' => 'error',
                    'message' => 'Username já existe'
                ];
            }

            // Definir propriedades
            $this->user->firstname = $data['firstname'];
            $this->user->lastname = $data['lastname'];
            $this->user->username = $data['username'];
            $this->user->avatar = isset($data['avatar']) ? $data['avatar'] : $this->user->avatar;
            
            // Apenas admins podem alterar o tipo de usuário
            if ($userData['type'] == 1) {
                $this->user->type = isset($data['type']) ? $data['type'] : $this->user->type;
            }

            // Se senha foi fornecida
            if (!empty($data['password'])) {
                $this->user->password = $data['password'];
            }

            if ($this->user->update()) {
                return [
                    'status' => 'success',
                    'message' => 'Usuário atualizado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar usuário'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Deletar usuário
    public function delete($id) {
        try {
            // Verificar autenticação (apenas admins podem deletar usuários)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado. Apenas administradores podem deletar usuários'
                ];
            }

            $this->user->id = $id;

            // Verificar se usuário existe
            if (!$this->user->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Usuário não encontrado'
                ];
            }

            // Não permitir deletar o próprio usuário
            if ($userData['user_id'] == $id) {
                return [
                    'status' => 'error',
                    'message' => 'Não é possível deletar seu próprio usuário'
                ];
            }

            if ($this->user->delete()) {
                return [
                    'status' => 'success',
                    'message' => 'Usuário deletado com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao deletar usuário'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Buscar usuários
    public function search($keywords) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $stmt = $this->user->search($keywords);
            $users = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['full_name'] = $row['firstname'] . ' ' . $row['lastname'];
                $row['type_text'] = $row['type'] == 1 ? 'Administrador' : 'Usuário';
                array_push($users, $row);
            }

            return [
                'status' => 'success',
                'message' => 'Busca realizada com sucesso',
                'data' => $users
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro na busca: ' . $e->getMessage()
            ];
        }
    }

    // Alterar senha
    public function changePassword($id, $data) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            // Apenas o próprio usuário ou admin pode alterar senha
            if ($userData['type'] != 1 && $userData['user_id'] != $id) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado'
                ];
            }

            if (empty($data['new_password'])) {
                return [
                    'status' => 'error',
                    'message' => 'Nova senha é obrigatória'
                ];
            }

            $this->user->id = $id;

            // Verificar se usuário existe
            if (!$this->user->readOne()) {
                return [
                    'status' => 'error',
                    'message' => 'Usuário não encontrado'
                ];
            }

            if ($this->user->changePassword($data['new_password'])) {
                return [
                    'status' => 'success',
                    'message' => 'Senha alterada com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao alterar senha'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }
}
?>
