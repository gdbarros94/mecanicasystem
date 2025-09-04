<?php
require_once __DIR__ . '/../models/Reports.php';
require_once __DIR__ . '/../auth/Auth.php';

class ReportsController {
    private $reports;
    private $auth;

    public function __construct() {
        $this->reports = new Reports();
        $this->auth = new Auth();
    }

    // Relatório de vendas diário
    public function getDailySalesReport($date) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            if (empty($date)) {
                $date = date('Y-m-d');
            }

            $result = $this->reports->getDailySalesReport($date);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório de vendas diário gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Relatório de serviços diário
    public function getDailyServiceReport($date) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            if (empty($date)) {
                $date = date('Y-m-d');
            }

            $result = $this->reports->getDailyServiceReport($date);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório de serviços diário gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Relatório de vendas por período
    public function getSalesReportByPeriod($start_date, $end_date) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            if (empty($start_date) || empty($end_date)) {
                return [
                    'status' => 'error',
                    'message' => 'Data de início e fim são obrigatórias'
                ];
            }

            $result = $this->reports->getSalesReportByPeriod($start_date, $end_date);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório de vendas por período gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Produtos mais vendidos
    public function getTopSellingProducts($limit = 10, $start_date = null, $end_date = null) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $result = $this->reports->getTopSellingProducts($limit, $start_date, $end_date);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório de produtos mais vendidos gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Serviços mais solicitados
    public function getTopServices($limit = 10, $start_date = null, $end_date = null) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $result = $this->reports->getTopServices($limit, $start_date, $end_date);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório de serviços mais solicitados gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Performance dos mecânicos
    public function getMechanicsPerformance($start_date = null, $end_date = null) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $result = $this->reports->getMechanicsPerformance($start_date, $end_date);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório de performance dos mecânicos gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Relatório de estoque baixo
    public function getLowStockReport($limit_quantity = 10) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $result = $this->reports->getLowStockReport($limit_quantity);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório de estoque baixo gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Relatório financeiro mensal
    public function getMonthlyFinancialReport($year = null, $month = null) {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            if (empty($year)) {
                $year = date('Y');
            }
            if (empty($month)) {
                $month = date('m');
            }

            $result = $this->reports->getMonthlyFinancialReport($year, $month);

            if ($result !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Relatório financeiro mensal gerado com sucesso',
                    'data' => $result
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao gerar relatório'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    // Dashboard com estatísticas gerais
    public function getDashboardStats() {
        try {
            // Verificar autenticação
            $userData = $this->auth->requireAuth();

            $stats = [];

            // Vendas de hoje
            $today_sales = $this->reports->getDailySalesReport(date('Y-m-d'));
            $stats['today_sales'] = $today_sales;

            // Produtos mais vendidos (últimos 30 dias)
            $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
            $today = date('Y-m-d');
            $top_products = $this->reports->getTopSellingProducts(5, $thirty_days_ago, $today);
            $stats['top_products'] = $top_products;

            // Serviços mais solicitados (últimos 30 dias)
            $top_services = $this->reports->getTopServices(5, $thirty_days_ago, $today);
            $stats['top_services'] = $top_services;

            // Estoque baixo
            $low_stock = $this->reports->getLowStockReport(10);
            $stats['low_stock'] = $low_stock;

            // Performance dos mecânicos (últimos 30 dias)
            $mechanics_performance = $this->reports->getMechanicsPerformance($thirty_days_ago, $today);
            $stats['mechanics_performance'] = $mechanics_performance;

            return [
                'status' => 'success',
                'message' => 'Estatísticas do dashboard obtidas com sucesso',
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }
}
?>
