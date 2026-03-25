<?php
/**
 * Pathway Statistics API Endpoint
 * GET /api/pathways/stats.php
 * 
 * Returns aggregated statistics about pathways
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get total pathways
    $totalSQL = "SELECT COUNT(*) as count FROM pathways";
    $total = executeCount($pdo, $totalSQL, []);
    
    // Get pathways by class
    $byClassSQL = "SELECT 
                      pathway_class,
                      COUNT(*) as count
                   FROM pathways
                   GROUP BY pathway_class
                   ORDER BY count DESC";
    $byClassResult = executeQuery($pdo, $byClassSQL, []);
    
    $byClass = [];
    foreach ($byClassResult as $row) {
        $byClass[$row['pathway_class']] = intval($row['count']);
    }
    
    // Get top 15 pathways by gene count
    $topPathwaysSQL = "SELECT 
                          pathway_id as id,
                          pathway_name as name,
                          pathway_class as class,
                          gene_count as genes
                       FROM pathways
                       ORDER BY gene_count DESC
                       LIMIT 15";
    $topPathways = executeQuery($pdo, $topPathwaysSQL, []);
    
    // Convert gene counts to integers
    foreach ($topPathways as &$row) {
        $row['genes'] = isset($row['genes']) ? intval($row['genes']) : 0;
    }
    
    // Build response
    $response = [
        'success' => true,
        'total' => $total,
        'byClass' => $byClass,
        'topPathways' => $topPathways
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch pathway statistics',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
