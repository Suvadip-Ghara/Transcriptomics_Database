<?php
/**
 * Dataset Detail API Endpoint
 * GET /api/datasets/detail.php?id={dataset_id}
 * 
 * Returns detailed information about a specific dataset
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get dataset ID parameter
    $datasetId = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (empty($datasetId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Dataset ID is required',
            'message' => 'Please provide a dataset ID parameter'
        ]);
        exit();
    }
    
    // Get dataset information
    $datasetSQL = "SELECT 
                      dataset_id as id,
                      title,
                      type,
                      size,
                      upload_date as date,
                      description,
                      samples,
                      format
                   FROM datasets 
                   WHERE dataset_id = :dataset_id";
    
    $dataset = executeQuerySingle($pdo, $datasetSQL, [':dataset_id' => $datasetId]);
    
    if (!$dataset) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Dataset not found',
            'message' => 'No dataset found with the specified ID'
        ]);
        exit();
    }
    
    // Build response
    $response = [
        'success' => true,
        'dataset' => $dataset
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch dataset details',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
