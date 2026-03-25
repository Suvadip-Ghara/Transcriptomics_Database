<?php
/**
 * Volcano Plot Chart Data API Endpoint
 * GET /api/charts/volcano.php
 * 
 * Parameters:
 * - tissue: Filter by tissue (optional)
 * - condition: Filter by condition (optional)
 * - limit: Maximum records (default: all DEG records)
 * 
 * Returns data formatted for Chart.js volcano plot
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get query parameters
    $tissue = isset($_GET['tissue']) && !empty($_GET['tissue']) ? $_GET['tissue'] : null;
    $condition = isset($_GET['condition']) && !empty($_GET['condition']) ? $_GET['condition'] : null;
    $limit = isset($_GET['limit']) ? min(10000, max(1, intval($_GET['limit']))) : null;
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if ($tissue !== null) {
        $where[] = "dr.tissue = :tissue";
        $params[':tissue'] = $tissue;
    }
    
    if ($condition !== null) {
        $where[] = "dr.condition_name = :condition";
        $params[':condition'] = $condition;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Get volcano plot data
    $limitClause = $limit !== null ? "LIMIT :limit" : "";
    $dataSQL = "SELECT 
                    dr.gene_id,
                    g.gene_name,
                    dr.log_fc,
                    dr.p_value,
                    dr.regulation
                FROM deg_results dr
                LEFT JOIN genes g ON dr.gene_id = g.gene_id
                $whereClause
                ORDER BY dr.p_value ASC
                $limitClause";
    
    $stmt = $pdo->prepare($dataSQL);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($limit !== null) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    // Format data for volcano plot: x = logFC, y = -log10(pValue)
    $chartData = [];
    foreach ($results as $row) {
        $logFC = floatval($row['log_fc']);
        $pVal = floatval($row['p_value']);
        $negLog10P = $pVal > 0 ? -log10($pVal) : 0;
        
        $chartData[] = [
            'x' => round($logFC, 4),
            'y' => round($negLog10P, 4),
            'label' => $row['gene_name'] ?? $row['gene_id'],
            'gene_id' => $row['gene_id'],
            'regulation' => $row['regulation']
        ];
    }
    
    // Build response
    $response = [
        'success' => true,
        'data' => $chartData,
        'count' => count($chartData)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch volcano plot data',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
