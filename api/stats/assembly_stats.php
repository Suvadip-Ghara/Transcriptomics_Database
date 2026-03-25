<?php
/**
 * Assembly Statistics API Endpoint
 * GET /api/stats/assembly_stats.php
 * 
 * Returns transcriptome assembly metrics (Liver, Muscle, Master assemblies)
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDBConnection();

    $sql = "SELECT metric_name,
                   liver_assembled, liver_assembled_300, liver_clustered, liver_cds,
                   muscle_assembled, muscle_assembled_300, muscle_clustered, muscle_cds,
                   master_assembled_cds, master_clustered_cds
            FROM assembly_statistics
            ORDER BY id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
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
        'error' => 'Failed to fetch assembly statistics',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
