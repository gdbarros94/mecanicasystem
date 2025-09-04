<?php
require_once __DIR__ . '/../models/SystemSettings.php';
require_once __DIR__ . '/../auth/Auth.php';

class SystemController {
    private $systemSettings;
    private $auth;

    public function __construct() {
        $this->systemSettings = new SystemSettings();
        $this->auth = new Auth();
    }

    // Obter todas as configurações do sistema
    public function getSystemInfo() {
        try {
            $settings = $this->systemSettings->getSystemInfo();
            
            return [
                'status' => 'success',
                'message' => 'Configurações obtidas com sucesso',
                'data' => $settings
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ];
        }
    }

    // Obter configuração específica
    public function getSetting($field) {
        try {
            $value = $this->systemSettings->getSetting($field);
            
            if ($value !== null) {
                return [
                    'status' => 'success',
                    'message' => 'Configuração obtida com sucesso',
                    'data' => [$field => $value]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Configuração não encontrada'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao obter configuração: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar configurações
    public function updateSettings($data) {
        try {
            // Verificar autenticação (apenas admins)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado. Apenas administradores podem alterar configurações'
                ];
            }

            if (empty($data) || !is_array($data)) {
                return [
                    'status' => 'error',
                    'message' => 'Dados de configuração inválidos'
                ];
            }

            if ($this->systemSettings->updateSettings($data)) {
                return [
                    'status' => 'success',
                    'message' => 'Configurações atualizadas com sucesso'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao atualizar configurações'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Upload de logo
    public function uploadLogo($file) {
        try {
            // Verificar autenticação (apenas admins)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado. Apenas administradores podem alterar logo'
                ];
            }

            return $this->systemSettings->uploadLogo($file);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Upload de banner/cover
    public function uploadCover($file) {
        try {
            // Verificar autenticação (apenas admins)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado. Apenas administradores podem alterar banner'
                ];
            }

            return $this->systemSettings->uploadCover($file);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Criar backup do sistema
    public function createBackup() {
        try {
            // Verificar autenticação (apenas admins)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado. Apenas administradores podem criar backups'
                ];
            }

            return $this->systemSettings->createBackup();
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Obter estatísticas do sistema
    public function getSystemStats() {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $stats = $this->systemSettings->getSystemStats();
            
            return [
                'status' => 'success',
                'message' => 'Estatísticas obtidas com sucesso',
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ];
        }
    }
}
?>
