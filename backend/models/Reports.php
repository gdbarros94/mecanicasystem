<?php
require_once __DIR__ . '/../config/db.php';

class Reports {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Relatório de vendas diário
    public function getDailySalesReport($date) {
        try {
            $query = "SELECT t.id, t.code, t.client_name, t.contact, t.amount, t.status, 
                             t.date_created, t.date_updated,
                             u.firstname as user_firstname, u.lastname as user_lastname,
                             m.firstname as mechanic_firstname, m.lastname as mechanic_lastname
                     FROM transaction_list t
                     LEFT JOIN users u ON t.user_id = u.id
                     LEFT JOIN mechanic_list m ON t.mechanic_id = m.id
                     WHERE DATE(t.date_created) = :date
                     ORDER BY t.date_created DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            $transactions = [];
            $total_amount = 0;
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $total_amount += $row['amount'];
                $transactions[] = $row;
            }
            
            return [
                'transactions' => $transactions,
                'total_amount' => $total_amount,
                'total_count' => count($transactions),
                'date' => $date
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    // Relatório de serviços diário
    public function getDailyServiceReport($date) {
        try {
            $query = "SELECT t.id, t.code, t.client_name, t.date_created,
                             s.name as service_name, s.description as service_description,
                             ts.price
                     FROM transaction_list t
                     JOIN transaction_services ts ON t.id = ts.transaction_id
                     JOIN service_list s ON ts.service_id = s.id
                     WHERE DATE(t.date_created) = :date
                     ORDER BY t.date_created DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            $services = [];
            $total_amount = 0;
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $total_amount += $row['price'];
                $services[] = $row;
            }
            
            return [
                'services' => $services,
                'total_amount' => $total_amount,
                'total_count' => count($services),
                'date' => $date
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    // Relatório de vendas por período
    public function getSalesReportByPeriod($start_date, $end_date) {
        try {
            $query = "SELECT t.id, t.code, t.client_name, t.contact, t.amount, t.status, 
                             t.date_created, t.date_updated,
                             u.firstname as user_firstname, u.lastname as user_lastname,
                             m.firstname as mechanic_firstname, m.lastname as mechanic_lastname
                     FROM transaction_list t
                     LEFT JOIN users u ON t.user_id = u.id
                     LEFT JOIN mechanic_list m ON t.mechanic_id = m.id
                     WHERE DATE(t.date_created) BETWEEN :start_date AND :end_date
                     ORDER BY t.date_created DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            $transactions = [];
            $total_amount = 0;
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $total_amount += $row['amount'];
                $transactions[] = $row;
            }
            
            return [
                'transactions' => $transactions,
                'total_amount' => $total_amount,
                'total_count' => count($transactions),
                'start_date' => $start_date,
                'end_date' => $end_date
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    // Relatório de produtos mais vendidos
    public function getTopSellingProducts($limit = 10, $start_date = null, $end_date = null) {
        try {
            $query = "SELECT p.id, p.name, p.description, p.price,
                             SUM(tp.qty) as total_qty,
                             SUM(tp.qty * tp.price) as total_revenue,
                             COUNT(DISTINCT tp.transaction_id) as transaction_count
                     FROM product_list p
                     JOIN transaction_products tp ON p.id = tp.product_id
                     JOIN transaction_list t ON tp.transaction_id = t.id";
            
            if ($start_date && $end_date) {
                $query .= " WHERE DATE(t.date_created) BETWEEN :start_date AND :end_date";
            }
            
            $query .= " GROUP BY p.id, p.name, p.description, p.price
                       ORDER BY total_qty DESC
                       LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            
            if ($start_date && $end_date) {
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':end_date', $end_date);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    // Relatório de serviços mais solicitados
    public function getTopServices($limit = 10, $start_date = null, $end_date = null) {
        try {
            $query = "SELECT s.id, s.name, s.description, s.price,
                             COUNT(ts.service_id) as service_count,
                             SUM(ts.price) as total_revenue,
                             COUNT(DISTINCT ts.transaction_id) as transaction_count
                     FROM service_list s
                     JOIN transaction_services ts ON s.id = ts.service_id
                     JOIN transaction_list t ON ts.transaction_id = t.id";
            
            if ($start_date && $end_date) {
                $query .= " WHERE DATE(t.date_created) BETWEEN :start_date AND :end_date";
            }
            
            $query .= " GROUP BY s.id, s.name, s.description, s.price
                       ORDER BY service_count DESC
                       LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            
            if ($start_date && $end_date) {
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':end_date', $end_date);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    // Relatório de performance de mecânicos
    public function getMechanicsPerformance($start_date = null, $end_date = null) {
        try {
            $query = "SELECT m.id, m.firstname, m.middlename, m.lastname,
                             COUNT(t.id) as total_jobs,
                             SUM(t.amount) as total_revenue,
                             AVG(t.amount) as avg_job_value
                     FROM mechanic_list m
                     LEFT JOIN transaction_list t ON m.id = t.mechanic_id";
            
            if ($start_date && $end_date) {
                $query .= " WHERE DATE(t.date_created) BETWEEN :start_date AND :end_date";
            }
            
            $query .= " GROUP BY m.id, m.firstname, m.middlename, m.lastname
                       ORDER BY total_revenue DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($start_date && $end_date) {
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':end_date', $end_date);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    // Relatório de estoque baixo
    public function getLowStockReport($limit_quantity = 10) {
        try {
            $query = "SELECT p.id, p.name, p.description, p.price,
                             COALESCE(SUM(i.quantity), 0) as current_stock
                     FROM product_list p
                     LEFT JOIN inventory_list i ON p.id = i.product_id
                     WHERE p.delete_flag = 0
                     GROUP BY p.id, p.name, p.description, p.price
                     HAVING current_stock < :limit_quantity
                     ORDER BY current_stock ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit_quantity', $limit_quantity, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    // Relatório financeiro mensal
    public function getMonthlyFinancialReport($year, $month) {
        try {
            $query = "SELECT 
                        DATE(date_created) as date,
                        COUNT(*) as transaction_count,
                        SUM(amount) as daily_revenue
                     FROM transaction_list
                     WHERE YEAR(date_created) = :year AND MONTH(date_created) = :month
                     GROUP BY DATE(date_created)
                     ORDER BY DATE(date_created)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->execute();
            
            $daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Totais do mês
            $query_total = "SELECT 
                              COUNT(*) as total_transactions,
                              SUM(amount) as total_revenue,
                              AVG(amount) as avg_transaction_value
                           FROM transaction_list
                           WHERE YEAR(date_created) = :year AND MONTH(date_created) = :month";
            
            $stmt_total = $this->conn->prepare($query_total);
            $stmt_total->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt_total->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt_total->execute();
            
            $totals = $stmt_total->fetch(PDO::FETCH_ASSOC);
            
            return [
                'daily_data' => $daily_data,
                'totals' => $totals,
                'year' => $year,
                'month' => $month
            ];
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
