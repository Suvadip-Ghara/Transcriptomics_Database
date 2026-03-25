<?php
/**
 * Pathway Detail API Endpoint
 * GET /api/pathways/detail.php?id={pathway_id}
 * 
 * Returns detailed information about a specific pathway
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get pathway ID parameter
    $pathwayId = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (empty($pathwayId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Pathway ID is required',
            'message' => 'Please provide a pathway ID parameter'
        ]);
        exit();
    }
    
    // Get pathway information
    $pathwaySQL = "SELECT 
                      pathway_id as id,
                      pathway_name as name,
                      pathway_class as class,
                      gene_count as genes
                   FROM pathways 
                   WHERE pathway_id = :pathway_id";
    $pathway = executeQuerySingle($pdo, $pathwaySQL, [':pathway_id' => $pathwayId]);
    
    if (!$pathway) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Pathway not found',
            'message' => 'No pathway found with the specified ID'
        ]);
        exit();
    }
    
    // Get genes associated with this pathway
    // Search in kegg_pathway field for the pathway ID
    $genesSQL = "SELECT 
                    g.gene_id,
                    g.gene_name,
                    g.protein_description,
                    fa.kegg_pathway
                 FROM functional_annotations fa
                 JOIN genes g ON fa.gene_id = g.gene_id
                 WHERE fa.kegg_pathway LIKE :pathway_search
                 LIMIT 100";
    
    $pathwaySearch = "%[{$pathwayId}]%";
    $genes = executeQuery($pdo, $genesSQL, [':pathway_search' => $pathwaySearch]);
    
    // Build response
    $response = [
        'success' => true,
        'pathway' => $pathway,
        'genes' => $genes,
        'geneCount' => count($genes)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch pathway details',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
