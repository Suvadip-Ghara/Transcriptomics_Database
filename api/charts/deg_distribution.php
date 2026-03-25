<?php
/**
 * DEG Distribution Chart Data API Endpoint
 * GET /api/charts/deg_distribution.php
 * 
 * Parameters:
 * - tissue: Filter by tissue (optional)
 * - condition: Filter by condition (optional)
 * 
 * Returns up/down regulated gene counts for pie chart
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get query parameters
    $tissue = isset($_GET['tissue']) && !empty($_GET['tissue']) ? $_GET['tissue'] : null;
    $condition = isset($_GET['condition']) && !empty($_GET['condition']) ? $_GET['condition'] : null;
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if ($tissue !== null) {
        $where[] = "tissue = :tissue";
        $params[':tissue'] = $tissue;
    }
    
    if ($condition !== null) {
        $where[] = "condition_name = :condition";
        $params[':condition'] = $condition;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Get distribution by regulation
    $dataSQL = "SELECT 
                    regulation,
                    COUNT(*) as count
                FROM deg_results
                $whereClause
                GROUP BY regulation";
    
    $results = executeQuery($pdo, $dataSQL, $params);
    
    $up = 0;
    $down = 0;
    
    foreach ($results as $row) {
        if ($row['regulation'] === 'Up') {
            $up = intval($row['count']);
        } elseif ($row['regulation'] === 'Down') {
            $down = intval($row['count']);
        }
    }
    
    // Build response
    $response = [
        'success' => true,
        'up' => $up,
        'down' => $down,
        'total' => $up + $down
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch DEG distribution',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
