<?php
/**
 * Read Statistics API Endpoint
 * GET /api/stats/read_stats.php
 * 
 * Parameters:
 * - dataset_source: Filter by dataset source (optional)
 * 
 * Returns per-sample read QC statistics
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDBConnection();

    $datasetSource = isset($_GET['dataset_source']) && !empty($_GET['dataset_source']) ? $_GET['dataset_source'] : null;

    $where = "";
    $params = [];

    if ($datasetSource !== null) {
        $where = "WHERE dataset_source = :dataset_source";
        $params[':dataset_source'] = $datasetSource;
    }

    $sql = "SELECT sample_name, raw_reads, processed_reads, high_quality_ratio, dataset_source
            FROM read_statistics
            $where
            ORDER BY sample_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $rows,
        'count' => count($rows)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch read statistics',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
