<?php
require_once __DIR__ . '/../auth/Auth.php';

class ImageUploadController {
    private $auth;
    private $upload_path;
    private $max_file_size;
    private $allowed_types;

    public function __construct() {
        $this->auth = new Auth();
        $this->upload_path = __DIR__ . '/../../uploads/';
        $this->max_file_size = 5 * 1024 * 1024; // 5MB
        $this->allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    }

    // Upload de imagem para produto
    public function uploadProductImage() {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                return [
                    'status' => 'error',
                    'message' => 'Erro no upload da imagem'
                ];
            }

            $file = $_FILES['image'];
            
            // Validações
            $validation = $this->validateImage($file);
            if (!$validation['valid']) {
                return [
                    'status' => 'error',
                    'message' => $validation['message']
                ];
            }

            // Criar diretório se não existir
            $product_dir = $this->upload_path . 'products/';
            if (!is_dir($product_dir)) {
                mkdir($product_dir, 0755, true);
            }

            // Gerar nome único para o arquivo
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'product_' . uniqid() . '.' . $extension;
            $filepath = $product_dir . $filename;

            // Mover arquivo
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Redimensionar imagem se necessário
                $this->resizeImage($filepath, 800, 600);

                return [
                    'status' => 'success',
                    'message' => 'Imagem enviada com sucesso',
                    'data' => [
                        'filename' => $filename,
                        'path' => 'uploads/products/' . $filename,
                        'size' => filesize($filepath)
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao salvar a imagem'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Upload de avatar do usuário
    public function uploadUserAvatar() {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                return [
                    'status' => 'error',
                    'message' => 'Erro no upload do avatar'
                ];
            }

            $file = $_FILES['avatar'];
            
            // Validações
            $validation = $this->validateImage($file);
            if (!$validation['valid']) {
                return [
                    'status' => 'error',
                    'message' => $validation['message']
                ];
            }

            // Criar diretório se não existir
            $avatar_dir = $this->upload_path . 'avatars/';
            if (!is_dir($avatar_dir)) {
                mkdir($avatar_dir, 0755, true);
            }

            // Gerar nome único para o arquivo
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'avatar_' . $userData['id'] . '_' . time() . '.' . $extension;
            $filepath = $avatar_dir . $filename;

            // Remover avatar anterior se existir
            $old_avatar = $avatar_dir . 'avatar_' . $userData['id'] . '_*';
            foreach (glob($old_avatar) as $old_file) {
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Mover arquivo
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Redimensionar imagem (avatar quadrado)
                $this->resizeImage($filepath, 200, 200, true);

                return [
                    'status' => 'success',
                    'message' => 'Avatar enviado com sucesso',
                    'data' => [
                        'filename' => $filename,
                        'path' => 'uploads/avatars/' . $filename,
                        'size' => filesize($filepath)
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao salvar o avatar'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Upload de banner do sistema
    public function uploadSystemBanner() {
        try {
            // Verificar autenticação (apenas admin)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado'
                ];
            }

            if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
                return [
                    'status' => 'error',
                    'message' => 'Erro no upload do banner'
                ];
            }

            $file = $_FILES['banner'];
            
            // Validações
            $validation = $this->validateImage($file);
            if (!$validation['valid']) {
                return [
                    'status' => 'error',
                    'message' => $validation['message']
                ];
            }

            // Criar diretório se não existir
            $banner_dir = $this->upload_path . 'banner/';
            if (!is_dir($banner_dir)) {
                mkdir($banner_dir, 0755, true);
            }

            // Nome fixo para o banner
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'banner.' . $extension;
            $filepath = $banner_dir . $filename;

            // Remover banner anterior se existir
            $old_banners = glob($banner_dir . 'banner.*');
            foreach ($old_banners as $old_banner) {
                if (file_exists($old_banner)) {
                    unlink($old_banner);
                }
            }

            // Mover arquivo
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Redimensionar para banner (proporção 16:9)
                $this->resizeImage($filepath, 1200, 675);

                return [
                    'status' => 'success',
                    'message' => 'Banner enviado com sucesso',
                    'data' => [
                        'filename' => $filename,
                        'path' => 'uploads/banner/' . $filename,
                        'size' => filesize($filepath)
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao salvar o banner'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Upload de logo do sistema
    public function uploadSystemLogo() {
        try {
            // Verificar autenticação (apenas admin)
            $userData = $this->auth->requireAuth();
            if ($userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado'
                ];
            }

            if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                return [
                    'status' => 'error',
                    'message' => 'Erro no upload do logo'
                ];
            }

            $file = $_FILES['logo'];
            
            // Validações
            $validation = $this->validateImage($file);
            if (!$validation['valid']) {
                return [
                    'status' => 'error',
                    'message' => $validation['message']
                ];
            }

            // Nome fixo para o logo
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'logo.' . $extension;
            $filepath = $this->upload_path . $filename;

            // Remover logo anterior se existir
            $old_logos = glob($this->upload_path . 'logo.*');
            foreach ($old_logos as $old_logo) {
                if (file_exists($old_logo)) {
                    unlink($old_logo);
                }
            }

            // Mover arquivo
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Redimensionar logo (quadrado)
                $this->resizeImage($filepath, 300, 300, true);

                return [
                    'status' => 'success',
                    'message' => 'Logo enviado com sucesso',
                    'data' => [
                        'filename' => $filename,
                        'path' => 'uploads/' . $filename,
                        'size' => filesize($filepath)
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao salvar o logo'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Deletar imagem
    public function deleteImage($type, $filename) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $allowed_types = ['products', 'avatars', 'banner'];
            if (!in_array($type, $allowed_types)) {
                return [
                    'status' => 'error',
                    'message' => 'Tipo de imagem inválido'
                ];
            }

            // Para banner, apenas admin pode deletar
            if ($type === 'banner' && $userData['type'] != 1) {
                return [
                    'status' => 'error',
                    'message' => 'Acesso negado'
                ];
            }

            $filepath = $this->upload_path . $type . '/' . $filename;
            
            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    return [
                        'status' => 'success',
                        'message' => 'Imagem deletada com sucesso'
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'Erro ao deletar a imagem'
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Imagem não encontrada'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Validar imagem
    private function validateImage($file) {
        // Verificar tamanho
        if ($file['size'] > $this->max_file_size) {
            return [
                'valid' => false,
                'message' => 'Arquivo muito grande. Máximo 5MB'
            ];
        }

        // Verificar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $this->allowed_types)) {
            return [
                'valid' => false,
                'message' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP'
            ];
        }

        // Verificar se é realmente uma imagem
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            return [
                'valid' => false,
                'message' => 'Arquivo não é uma imagem válida'
            ];
        }

        return ['valid' => true];
    }

    // Redimensionar imagem
    private function resizeImage($filepath, $max_width, $max_height, $square = false) {
        if (!extension_loaded('gd')) {
            return false;
        }

        $image_info = getimagesize($filepath);
        if ($image_info === false) {
            return false;
        }

        $original_width = $image_info[0];
        $original_height = $image_info[1];
        $mime_type = $image_info['mime'];

        // Criar imagem a partir do arquivo
        switch ($mime_type) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($filepath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($filepath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return false;
        }

        if (!$source) {
            return false;
        }

        // Calcular novas dimensões
        if ($square) {
            // Para avatar quadrado
            $size = min($original_width, $original_height);
            $new_width = $new_height = min($size, $max_width);
            $src_x = ($original_width - $size) / 2;
            $src_y = ($original_height - $size) / 2;
            $src_width = $src_height = $size;
        } else {
            // Manter proporção
            $ratio = min($max_width / $original_width, $max_height / $original_height);
            $new_width = intval($original_width * $ratio);
            $new_height = intval($original_height * $ratio);
            $src_x = $src_y = 0;
            $src_width = $original_width;
            $src_height = $original_height;
        }

        // Criar nova imagem
        $destination = imagecreatetruecolor($new_width, $new_height);

        // Preservar transparência para PNG
        if ($mime_type == 'image/png') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefill($destination, 0, 0, $transparent);
        }

        // Redimensionar
        imagecopyresampled(
            $destination, $source,
            0, 0, $src_x, $src_y,
            $new_width, $new_height,
            $src_width, $src_height
        );

        // Salvar imagem redimensionada
        switch ($mime_type) {
            case 'image/jpeg':
                imagejpeg($destination, $filepath, 90);
                break;
            case 'image/png':
                imagepng($destination, $filepath);
                break;
            case 'image/gif':
                imagegif($destination, $filepath);
                break;
            case 'image/webp':
                imagewebp($destination, $filepath, 90);
                break;
        }

        // Limpar memória
        imagedestroy($source);
        imagedestroy($destination);

        return true;
    }
}
?>
