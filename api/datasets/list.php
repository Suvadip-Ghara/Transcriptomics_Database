<?php
/**
 * Datasets List API Endpoint
 * GET /api/datasets/list.php
 * 
 * Returns all dataset metadata
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get all datasets
    $dataSQL = "SELECT 
                    dataset_id as id,
                    title,
                    type,
                    size,
                    upload_date as date,
                    description,
                    samples,
                    format
                FROM datasets
                ORDER BY upload_date DESC";
    
    $data = executeQuery($pdo, $dataSQL, []);
    
    // Build response
    $response = [
        'success' => true,
        'data' => $data
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch datasets',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
