<?php
/**
 * Gene Expression Data API Endpoint
 * GET /api/expression/gene.php
 * 
 * Parameters:
 * - id: Gene ID (required)
 * - dataset: Dataset name filter (default: 'CvsW')
 * 
 * Returns per-sample expression counts for a specific gene
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDBConnection();

    // Get required gene ID parameter
    $geneId = isset($_GET['id']) && !empty($_GET['id']) ? $_GET['id'] : null;
    $dataset = isset($_GET['dataset']) && !empty($_GET['dataset']) ? $_GET['dataset'] : 'CvsW';

    if ($geneId === null) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required parameter: id'
        ]);
        exit();
    }

    // Get expression data for this gene
    $sql = "SELECT ec.sample_name, ec.raw_count, ec.tissue, ec.sex, ec.condition_group
            FROM expression_counts ec
            WHERE ec.gene_id = :gene_id AND ec.dataset_name = :dataset
            ORDER BY ec.tissue, ec.sex, ec.condition_group, ec.sample_name";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':gene_id', $geneId);
    $stmt->bindValue(':dataset', $dataset);
    $stmt->execute();
    $results = $stmt->fetchAll();

    if (empty($results)) {
        echo json_encode([
            'success' => true,
            'gene_id' => $geneId,
            'data' => [],
            'message' => 'No expression data found for this gene'
        ]);
        exit();
    }

    // Get gene info
    $geneSQL = "SELECT gene_id, gene_name, protein_description FROM genes WHERE gene_id = :gene_id";
    $gene = executeQuerySingle($pdo, $geneSQL, [':gene_id' => $geneId]);

    // Format response
    $samples = [];
    $counts = [];
    $groups = [];

    foreach ($results as $row) {
        $samples[] = $row['sample_name'];
        $counts[] = intval($row['raw_count']);
        $groups[] = [
            'tissue' => $row['tissue'],
            'sex' => $row['sex'],
            'condition' => $row['condition_group']
        ];
    }

    echo json_encode([
        'success' => true,
        'gene_id' => $geneId,
        'gene_name' => $gene ? $gene['gene_name'] : null,
        'description' => $gene ? $gene['protein_description'] : null,
        'samples' => $samples,
        'counts' => $counts,
        'groups' => $groups
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch gene expression data',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
