<?php
/**
 * Top Genes Chart Data API Endpoint
 * GET /api/charts/top_genes.php
 * 
 * Parameters:
 * - tissue: Filter by tissue (optional)
 * - condition: Filter by condition (optional)
 * - limit: Number of top genes (default: 10)
 * 
 * Returns top N most significant genes for bar chart
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get query parameters
    $tissue = isset($_GET['tissue']) && !empty($_GET['tissue']) ? $_GET['tissue'] : null;
    $condition = isset($_GET['condition']) && !empty($_GET['condition']) ? $_GET['condition'] : null;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 10;
    
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
    
    // Get top genes by lowest p-value, then highest absolute logFC
    $dataSQL = "SELECT 
                    dr.gene_id,
                    g.gene_name,
                    dr.p_value,
                    dr.log_fc,
                    dr.regulation
                FROM deg_results dr
                LEFT JOIN genes g ON dr.gene_id = g.gene_id
                $whereClause
                ORDER BY dr.p_value ASC, ABS(dr.log_fc) DESC
                LIMIT :limit";
    
    $stmt = $pdo->prepare($dataSQL);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $results = $stmt->fetchAll();
    
    // Format data for chart
    $chartData = [];
    foreach ($results as $row) {
        $geneName = $row['gene_name'] ?? substr($row['gene_id'], 0, 15);
        $pVal = floatval($row['p_value']);
        $negLog10P = $pVal > 0 ? -log10($pVal) : 0;
        
        $chartData[] = [
            'gene' => $geneName,
            'gene_id' => $row['gene_id'],
            'pValue' => $pVal,
            'negLog10PValue' => round($negLog10P, 4),
            'logFC' => round(floatval($row['log_fc']), 4),
            'absLogFC' => round(abs(floatval($row['log_fc'])), 4),
            'regulation' => $row['regulation']
        ];
    }
    
    // Build response
    $response = [
        'success' => true,
        'data' => $chartData
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch top genes',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
