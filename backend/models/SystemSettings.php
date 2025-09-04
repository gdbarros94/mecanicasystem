<?php
require_once __DIR__ . '/../config/db.php';

class SystemSettings {
    private $conn;
    private $table_name = "system_info";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obter todas as configurações do sistema
    public function getSystemInfo() {
        try {
            $query = "SELECT meta_field, meta_value FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $settings = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['meta_field']] = $row['meta_value'];
            }

            return $settings;
        } catch (Exception $e) {
            return false;
        }
    }

    // Obter configuração específica
    public function getSetting($field) {
        try {
            $query = "SELECT meta_value FROM " . $this->table_name . " WHERE meta_field = :field";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':field', $field);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['meta_value'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Atualizar ou inserir configuração
    public function setSetting($field, $value) {
        try {
            // Verificar se a configuração já existe
            $check_query = "SELECT meta_field FROM " . $this->table_name . " WHERE meta_field = :field";
            $stmt = $this->conn->prepare($check_query);
            $stmt->bindParam(':field', $field);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Atualizar
                $query = "UPDATE " . $this->table_name . " SET meta_value = :value WHERE meta_field = :field";
            } else {
                // Inserir
                $query = "INSERT INTO " . $this->table_name . " (meta_field, meta_value) VALUES (:field, :value)";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':field', $field);
            $stmt->bindParam(':value', $value);

            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Atualizar múltiplas configurações
    public function updateSettings($settings) {
        try {
            $this->conn->beginTransaction();

            foreach ($settings as $field => $value) {
                if (!$this->setSetting($field, $value)) {
                    $this->conn->rollback();
                    return false;
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Upload de logo
    public function uploadLogo($file) {
        try {
            $upload_dir = __DIR__ . '/../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                return ['status' => 'error', 'message' => 'Tipo de arquivo não permitido'];
            }

            $filename = 'logo.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $logo_path = 'uploads/' . $filename . '?v=' . time();
                
                if ($this->setSetting('logo', $logo_path)) {
                    return ['status' => 'success', 'message' => 'Logo enviado com sucesso', 'path' => $logo_path];
                }
            }

            return ['status' => 'error', 'message' => 'Erro ao enviar logo'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    // Upload de cover/banner
    public function uploadCover($file) {
        try {
            $upload_dir = __DIR__ . '/../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                return ['status' => 'error', 'message' => 'Tipo de arquivo não permitido'];
            }

            $filename = 'cover.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $cover_path = 'uploads/' . $filename . '?v=' . time();
                
                if ($this->setSetting('cover', $cover_path)) {
                    return ['status' => 'success', 'message' => 'Banner enviado com sucesso', 'path' => $cover_path];
                }
            }

            return ['status' => 'error', 'message' => 'Erro ao enviar banner'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    // Backup do sistema
    public function createBackup() {
        try {
            $backup_data = [];
            
            // Backup das configurações
            $backup_data['system_info'] = $this->getSystemInfo();
            
            // Backup de outras tabelas importantes
            $tables = ['product_list', 'service_list', 'mechanic_list', 'users', 'inventory_list'];
            
            foreach ($tables as $table) {
                $query = "SELECT * FROM " . $table;
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                
                $backup_data[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $backup_filename = 'backup_' . date('Y-m-d_H-i-s') . '.json';
            $backup_path = __DIR__ . '/../../uploads/backups/';
            
            if (!is_dir($backup_path)) {
                mkdir($backup_path, 0755, true);
            }

            $backup_file = $backup_path . $backup_filename;
            
            if (file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT))) {
                return [
                    'status' => 'success', 
                    'message' => 'Backup criado com sucesso',
                    'filename' => $backup_filename,
                    'path' => 'uploads/backups/' . $backup_filename
                ];
            }

            return ['status' => 'error', 'message' => 'Erro ao criar backup'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    // Obter estatísticas do sistema
    public function getSystemStats() {
        try {
            $stats = [];

            // Total de produtos
            $query = "SELECT COUNT(*) as total FROM product_list WHERE delete_flag = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de serviços
            $query = "SELECT COUNT(*) as total FROM service_list WHERE delete_flag = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de mecânicos
            $query = "SELECT COUNT(*) as total FROM mechanic_list WHERE delete_flag = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_mechanics'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de usuários
            $query = "SELECT COUNT(*) as total FROM users WHERE type > 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de transações
            $query = "SELECT COUNT(*) as total FROM transaction_list";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Transações do mês atual
            $query = "SELECT COUNT(*) as total, SUM(amount) as total_amount FROM transaction_list WHERE MONTH(date_created) = MONTH(CURRENT_DATE()) AND YEAR(date_created) = YEAR(CURRENT_DATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $monthly = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['monthly_transactions'] = $monthly['total'];
            $stats['monthly_revenue'] = $monthly['total_amount'] ? $monthly['total_amount'] : 0;

            // Transações de hoje
            $query = "SELECT COUNT(*) as total, SUM(amount) as total_amount FROM transaction_list WHERE DATE(date_created) = CURRENT_DATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $daily = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['daily_transactions'] = $daily['total'];
            $stats['daily_revenue'] = $daily['total_amount'] ? $daily['total_amount'] : 0;

            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
