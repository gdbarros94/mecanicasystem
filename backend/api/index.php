<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Autoload controllers
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/ServiceController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/MechanicController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/TransactionController.php';
require_once __DIR__ . '/../controllers/SystemSettingsController.php';
require_once __DIR__ . '/../controllers/ReportsController.php';
require_once __DIR__ . '/../controllers/ImageUploadController.php';
require_once __DIR__ . '/../auth/Auth.php';

// Helper function to get request method and URI
function getRequestInfo() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);
    return [$method, $uri];
}

// Helper function to get request body
function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true);
}

// Helper function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    [$method, $uri] = getRequestInfo();
    $data = getRequestBody();

    // Remove empty segments and get the relevant parts
    $uri = array_filter($uri);
    $uri = array_values($uri);

    // Basic routing - find the api index
    $apiIndex = array_search('api', $uri);
    if ($apiIndex === false) {
        sendResponse(['status' => 'error', 'message' => 'Endpoint inválido'], 400);
    }

    if (count($uri) <= $apiIndex) {
        sendResponse(['status' => 'error', 'message' => 'Recurso não especificado'], 400);
    }

    $resource = isset($uri[$apiIndex + 1]) ? $uri[$apiIndex + 1] : null;
    $id = isset($uri[$apiIndex + 2]) ? $uri[$apiIndex + 2] : null;
    $action = isset($uri[$apiIndex + 3]) ? $uri[$apiIndex + 3] : null;

    if (!$resource) {
        sendResponse(['status' => 'success', 'message' => 'API da Oficina Mecânica funcionando'], 200);
    }

    // Authentication endpoint
    if ($resource === 'auth' && $method === 'POST') {
        $auth = new Auth();
        
        if ($id === 'login') {
            if (empty($data['username']) || empty($data['password'])) {
                sendResponse(['status' => 'error', 'message' => 'Username e senha são obrigatórios'], 400);
            }
            
            $result = $auth->login($data['username'], $data['password']);
            sendResponse($result, $result['status'] === 'success' ? 200 : 401);
        } else {
            sendResponse(['status' => 'error', 'message' => 'Ação de autenticação inválida'], 400);
        }
    }

    // Products endpoints
    if ($resource === 'products') {
        $controller = new ProductController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    if ($action === 'search') {
                        $keywords = $_GET['q'] ?? '';
                        $result = $controller->search($keywords);
                    } else {
                        $result = $controller->show($id);
                    }
                } else {
                    if (isset($_GET['search'])) {
                        $keywords = $_GET['search'];
                        $result = $controller->search($keywords);
                    } else {
                        $result = $controller->index();
                    }
                }
                break;
            case 'POST':
                $result = $controller->store($data);
                break;
            case 'PUT':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do produto é obrigatório'], 400);
                }
                $result = $controller->update($id, $data);
                break;
            case 'DELETE':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do produto é obrigatório'], 400);
                }
                $result = $controller->delete($id);
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // Services endpoints
    if ($resource === 'services') {
        $controller = new ServiceController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $controller->show($id);
                } else {
                    if (isset($_GET['search'])) {
                        $keywords = $_GET['search'];
                        $result = $controller->search($keywords);
                    } else {
                        $result = $controller->index();
                    }
                }
                break;
            case 'POST':
                $result = $controller->store($data);
                break;
            case 'PUT':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do serviço é obrigatório'], 400);
                }
                $result = $controller->update($id, $data);
                break;
            case 'DELETE':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do serviço é obrigatório'], 400);
                }
                $result = $controller->delete($id);
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // Inventory endpoints
    if ($resource === 'inventory') {
        $controller = new InventoryController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    if ($action === 'stock') {
                        $result = $controller->getStockByProduct($id);
                    } else {
                        $result = $controller->show($id);
                    }
                } else {
                    if (isset($_GET['low_stock'])) {
                        $limit = $_GET['limit'] ?? 10;
                        $result = $controller->getLowStock($limit);
                    } elseif (isset($_GET['movement'])) {
                        $start_date = $_GET['start_date'] ?? date('Y-m-01');
                        $end_date = $_GET['end_date'] ?? date('Y-m-t');
                        $result = $controller->getStockMovement($start_date, $end_date);
                    } else {
                        $result = $controller->index();
                    }
                }
                break;
            case 'POST':
                $result = $controller->store($data);
                break;
            case 'PUT':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID da entrada é obrigatório'], 400);
                }
                $result = $controller->update($id, $data);
                break;
            case 'DELETE':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID da entrada é obrigatório'], 400);
                }
                $result = $controller->delete($id);
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // Mechanics endpoints
    if ($resource === 'mechanics') {
        $controller = new MechanicController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $controller->show($id);
                } else {
                    if (isset($_GET['active'])) {
                        $result = $controller->getActiveMechanics();
                    } elseif (isset($_GET['search'])) {
                        $keywords = $_GET['search'];
                        $result = $controller->search($keywords);
                    } else {
                        $result = $controller->index();
                    }
                }
                break;
            case 'POST':
                $result = $controller->store($data);
                break;
            case 'PUT':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do mecânico é obrigatório'], 400);
                }
                $result = $controller->update($id, $data);
                break;
            case 'DELETE':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do mecânico é obrigatório'], 400);
                }
                $result = $controller->delete($id);
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // Users endpoints
    if ($resource === 'users') {
        $controller = new UserController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $controller->show($id);
                } else {
                    if (isset($_GET['search'])) {
                        $keywords = $_GET['search'];
                        $result = $controller->search($keywords);
                    } else {
                        $result = $controller->index();
                    }
                }
                break;
            case 'POST':
                if ($action === 'change-password') {
                    $result = $controller->changePassword($id, $data);
                } else {
                    $result = $controller->store($data);
                }
                break;
            case 'PUT':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do usuário é obrigatório'], 400);
                }
                $result = $controller->update($id, $data);
                break;
            case 'DELETE':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID do usuário é obrigatório'], 400);
                }
                $result = $controller->delete($id);
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // Transactions endpoints
    if ($resource === 'transactions') {
        $controller = new TransactionController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $controller->show($id);
                } else {
                    if (isset($_GET['search'])) {
                        $keywords = $_GET['search'];
                        $result = $controller->search($keywords);
                    } elseif (isset($_GET['report'])) {
                        $start_date = $_GET['start_date'] ?? date('Y-m-01');
                        $end_date = $_GET['end_date'] ?? date('Y-m-t');
                        $result = $controller->getSalesReport($start_date, $end_date);
                    } else {
                        $result = $controller->index();
                    }
                }
                break;
            case 'POST':
                if ($action === 'update-status') {
                    $status = $data['status'] ?? null;
                    if ($status === null) {
                        sendResponse(['status' => 'error', 'message' => 'Status é obrigatório'], 400);
                    }
                    $result = $controller->updateStatus($id, $status);
                } else {
                    $result = $controller->store($data);
                }
                break;
            case 'PUT':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID da transação é obrigatório'], 400);
                }
                $result = $controller->update($id, $data);
                break;
            case 'DELETE':
                if (!$id) {
                    sendResponse(['status' => 'error', 'message' => 'ID da transação é obrigatório'], 400);
                }
                $result = $controller->delete($id);
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // System Settings endpoints
    if ($resource === 'system-settings') {
        $controller = new SystemSettingsController();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $controller->getSetting($id);
                } else {
                    $result = $controller->getAllSettings();
                }
                break;
            case 'POST':
                $result = $controller->updateSetting($data);
                break;
            case 'PUT':
                $result = $controller->updateSetting($data);
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // Reports endpoints
    if ($resource === 'reports') {
        $controller = new ReportsController();
        
        switch ($method) {
            case 'GET':
                if ($id === 'daily-sales') {
                    $date = $_GET['date'] ?? null;
                    $result = $controller->getDailySalesReport($date);
                } elseif ($id === 'daily-service') {
                    $date = $_GET['date'] ?? null;
                    $result = $controller->getDailyServiceReport($date);
                } elseif ($id === 'sales-period') {
                    $start_date = $_GET['start_date'] ?? null;
                    $end_date = $_GET['end_date'] ?? null;
                    $result = $controller->getSalesReportByPeriod($start_date, $end_date);
                } elseif ($id === 'top-products') {
                    $limit = $_GET['limit'] ?? 10;
                    $start_date = $_GET['start_date'] ?? null;
                    $end_date = $_GET['end_date'] ?? null;
                    $result = $controller->getTopSellingProducts($limit, $start_date, $end_date);
                } elseif ($id === 'top-services') {
                    $limit = $_GET['limit'] ?? 10;
                    $start_date = $_GET['start_date'] ?? null;
                    $end_date = $_GET['end_date'] ?? null;
                    $result = $controller->getTopServices($limit, $start_date, $end_date);
                } elseif ($id === 'mechanics-performance') {
                    $start_date = $_GET['start_date'] ?? null;
                    $end_date = $_GET['end_date'] ?? null;
                    $result = $controller->getMechanicsPerformance($start_date, $end_date);
                } elseif ($id === 'low-stock') {
                    $limit_quantity = $_GET['limit_quantity'] ?? 10;
                    $result = $controller->getLowStockReport($limit_quantity);
                } elseif ($id === 'monthly-financial') {
                    $year = $_GET['year'] ?? null;
                    $month = $_GET['month'] ?? null;
                    $result = $controller->getMonthlyFinancialReport($year, $month);
                } elseif ($id === 'dashboard') {
                    $result = $controller->getDashboardStats();
                } else {
                    sendResponse(['status' => 'error', 'message' => 'Tipo de relatório não encontrado'], 404);
                }
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // Upload endpoints
    if ($resource === 'upload') {
        $controller = new ImageUploadController();
        
        switch ($method) {
            case 'POST':
                if ($id === 'product-image') {
                    $result = $controller->uploadProductImage();
                } elseif ($id === 'user-avatar') {
                    $result = $controller->uploadUserAvatar();
                } elseif ($id === 'system-banner') {
                    $result = $controller->uploadSystemBanner();
                } elseif ($id === 'system-logo') {
                    $result = $controller->uploadSystemLogo();
                } else {
                    sendResponse(['status' => 'error', 'message' => 'Tipo de upload não encontrado'], 404);
                }
                break;
            case 'DELETE':
                if ($action) {
                    $result = $controller->deleteImage($id, $action);
                } else {
                    sendResponse(['status' => 'error', 'message' => 'Nome do arquivo é obrigatório'], 400);
                }
                break;
            default:
                sendResponse(['status' => 'error', 'message' => 'Método não permitido'], 405);
        }
        
        sendResponse($result, $result['status'] === 'success' ? 200 : 400);
    }

    // If no resource matches
    sendResponse(['status' => 'error', 'message' => 'Recurso não encontrado'], 404);

} catch (Exception $e) {
    sendResponse([
        'status' => 'error',
        'message' => 'Erro interno do servidor',
        'details' => $e->getMessage()
    ], 500);
}
?>
