<?php
/**
 * DEG Gene Detail API Endpoint
 * GET /api/deg/gene.php?id={gene_id}
 * 
 * Returns detailed information about a specific gene
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get gene ID parameter
    $geneId = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (empty($geneId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Gene ID is required',
            'message' => 'Please provide a gene ID parameter'
        ]);
        exit();
    }
    
    // Get gene basic information
    $geneSQL = "SELECT * FROM genes WHERE gene_id = :gene_id";
    $gene = executeQuerySingle($pdo, $geneSQL, [':gene_id' => $geneId]);
    
    if (!$gene) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Gene not found',
            'message' => 'No gene found with the specified ID'
        ]);
        exit();
    }
    
    // Get DEG results for this gene
    $degSQL = "SELECT * FROM deg_results WHERE gene_id = :gene_id";
    $degResults = executeQuery($pdo, $degSQL, [':gene_id' => $geneId]);
    
    // Convert numeric strings to proper types
    foreach ($degResults as &$row) {
        $row['log_fc'] = isset($row['log_fc']) ? floatval($row['log_fc']) : null;
        $row['p_value'] = isset($row['p_value']) ? floatval($row['p_value']) : null;
    }
    
    // Get functional annotations
    $funcSQL = "SELECT * FROM functional_annotations WHERE gene_id = :gene_id";
    $functional = executeQuerySingle($pdo, $funcSQL, [':gene_id' => $geneId]);
    
    // Build response
    $response = [
        'success' => true,
        'gene' => $gene,
        'degResults' => $degResults,
        'functional' => $functional
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch gene details',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
