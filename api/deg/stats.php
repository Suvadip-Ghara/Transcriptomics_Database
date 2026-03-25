<?php
/**
 * DEG Statistics API Endpoint
 * GET /api/deg/stats.php
 * 
 * Returns aggregated statistics about DEG data
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get total genes
    $totalSQL = "SELECT COUNT(*) as count FROM deg_results";
    $totalGenes = executeCount($pdo, $totalSQL, []);
    
    // Get up-regulated count
    $upSQL = "SELECT COUNT(*) as count FROM deg_results WHERE regulation = 'Up'";
    $upRegulated = executeCount($pdo, $upSQL, []);
    
    // Get down-regulated count
    $downSQL = "SELECT COUNT(*) as count FROM deg_results WHERE regulation = 'Down'";
    $downRegulated = executeCount($pdo, $downSQL, []);
    
    // Get unique tissues
    $tissuesSQL = "SELECT DISTINCT tissue FROM deg_results ORDER BY tissue";
    $tissuesResult = executeQuery($pdo, $tissuesSQL, []);
    $tissues = array_column($tissuesResult, 'tissue');
    
    // Get unique conditions
    $conditionsSQL = "SELECT DISTINCT condition_name FROM deg_results ORDER BY condition_name";
    $conditionsResult = executeQuery($pdo, $conditionsSQL, []);
    $conditions = array_column($conditionsResult, 'condition_name');
    
    // Build response
    $response = [
        'success' => true,
        'totalGenes' => $totalGenes,
        'upRegulated' => $upRegulated,
        'downRegulated' => $downRegulated,
        'tissues' => $tissues,
        'conditions' => $conditions
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch DEG statistics',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
