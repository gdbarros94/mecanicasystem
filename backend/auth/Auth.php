<?php
require_once __DIR__ . '/../config/db.php';

class Auth {
    private $conn;
    private $secret_key = "your_secret_key_here_change_this_in_production";
    private $algorithm = "HS256";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Gerar JWT token
    public function generateJWT($user_id, $username, $type) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode([
            'user_id' => $user_id,
            'username' => $username,
            'type' => $type,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // Token válido por 24 horas
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    // Verificar JWT token
    public function verifyJWT($token) {
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) {
            return false;
        }

        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64Signature === $signatureProvided) {
            $payloadData = json_decode($payload, true);
            if ($payloadData['exp'] > time()) {
                return $payloadData;
            }
        }
        return false;
    }

    // Login do usuário
    public function login($username, $password) {
        try {
            $query = "SELECT id, firstname, lastname, username, password, type FROM users WHERE username = :username AND type > 0";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar senha (assumindo MD5 como no sistema original)
                if ($user['password'] === md5($password)) {
                    $token = $this->generateJWT($user['id'], $user['username'], $user['type']);
                    
                    return [
                        'status' => 'success',
                        'message' => 'Login realizado com sucesso',
                        'data' => [
                            'token' => $token,
                            'user' => [
                                'id' => $user['id'],
                                'firstname' => $user['firstname'],
                                'lastname' => $user['lastname'],
                                'username' => $user['username'],
                                'type' => $user['type']
                            ]
                        ]
                    ];
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'Credenciais inválidas'
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ];
        }
    }

    // Middleware para verificar autenticação
    public function requireAuth() {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : 
                     (isset($headers['authorization']) ? $headers['authorization'] : null);

        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token de autenticação requerido']);
            exit;
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $userData = $this->verifyJWT($token);

        if (!$userData) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token inválido ou expirado']);
            exit;
        }

        return $userData;
    }
}
?>
