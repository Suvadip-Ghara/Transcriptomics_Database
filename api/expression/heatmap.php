<?php
/**
 * Expression Heatmap Data API Endpoint
 * GET /api/expression/heatmap.php
 * 
 * Parameters:
 * - dataset: Dataset name filter (default: 'CvsW')
 * - limit: Number of top variable genes (default: 50, max: 200)
 * - tissue: Filter by tissue (optional)
 * - condition_group: Filter by condition group (optional)
 * 
 * Returns gene x sample matrix for heatmap visualization
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDBConnection();

    // Get query parameters
    $dataset = isset($_GET['dataset']) && !empty($_GET['dataset']) ? $_GET['dataset'] : 'CvsW';
    $limit = isset($_GET['limit']) ? min(200, max(10, intval($_GET['limit']))) : 50;
    $tissue = isset($_GET['tissue']) && !empty($_GET['tissue']) ? $_GET['tissue'] : null;
    $conditionGroup = isset($_GET['condition_group']) && !empty($_GET['condition_group']) ? $_GET['condition_group'] : null;

    // Build WHERE clause for filtering
    $where = ["ec.dataset_name = :dataset"];
    $params = [':dataset' => $dataset];

    if ($tissue !== null) {
        $where[] = "ec.tissue = :tissue";
        $params[':tissue'] = $tissue;
    }

    if ($conditionGroup !== null) {
        $where[] = "ec.condition_group = :condition_group";
        $params[':condition_group'] = $conditionGroup;
    }

    $whereClause = "WHERE " . implode(" AND ", $where);

    // Step 1: Find top N most variable genes (by variance across samples)
    $varianceSQL = "SELECT ec.gene_id, VARIANCE(ec.raw_count) as var_count
                    FROM expression_counts ec
                    $whereClause
                    GROUP BY ec.gene_id
                    HAVING var_count > 0
                    ORDER BY var_count DESC
                    LIMIT :gene_limit";

    $stmt = $pdo->prepare($varianceSQL);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':gene_limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $topGenes = $stmt->fetchAll();

    if (empty($topGenes)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'genes' => [],
            'samples' => [],
            'groups' => [],
            'message' => 'No expression data found'
        ]);
        exit();
    }

    $geneIds = array_column($topGenes, 'gene_id');

    // Step 2: Get all expression data for these genes (positional params throughout)
    $placeholders = implode(',', array_fill(0, count($geneIds), '?'));

    // Rebuild WHERE clause with positional params
    $dataWhere = ["ec.dataset_name = ?"];
    $dataParams = [$dataset];

    if ($tissue !== null) {
        $dataWhere[] = "ec.tissue = ?";
        $dataParams[] = $tissue;
    }

    if ($conditionGroup !== null) {
        $dataWhere[] = "ec.condition_group = ?";
        $dataParams[] = $conditionGroup;
    }

    $dataWhereClause = "WHERE " . implode(" AND ", $dataWhere);

    $dataSQL = "SELECT ec.gene_id, ec.sample_name, ec.raw_count, ec.tissue, ec.sex, ec.condition_group
                FROM expression_counts ec
                $dataWhereClause
                AND ec.gene_id IN ($placeholders)
                ORDER BY ec.gene_id, ec.sample_name";

    foreach ($geneIds as $gid) {
        $dataParams[] = $gid;
    }

    $stmt = $pdo->prepare($dataSQL);
    $stmt->execute($dataParams);
    $rows = $stmt->fetchAll();

    // Step 3: Build matrix structure
    $genes = [];
    $samples = [];
    $sampleGroups = [];
    $matrix = [];

    foreach ($rows as $row) {
        $geneId = $row['gene_id'];
        $sampleName = $row['sample_name'];

        if (!in_array($geneId, $genes)) {
            $genes[] = $geneId;
        }
        if (!in_array($sampleName, $samples)) {
            $samples[] = $sampleName;
            $sampleGroups[$sampleName] = [
                'tissue' => $row['tissue'],
                'sex' => $row['sex'],
                'condition' => $row['condition_group']
            ];
        }

        if (!isset($matrix[$geneId])) {
            $matrix[$geneId] = [];
        }
        $matrix[$geneId][$sampleName] = intval($row['raw_count']);
    }

    // Step 4: Convert to 2D array (log2 transformed for heatmap)
    $values = [];
    foreach ($genes as $gene) {
        $row = [];
        foreach ($samples as $sample) {
            $count = isset($matrix[$gene][$sample]) ? $matrix[$gene][$sample] : 0;
            // Log2 transform: log2(count + 1) to handle zeros
            $row[] = round(log($count + 1, 2), 4);
        }
        $values[] = $row;
    }

    echo json_encode([
        'success' => true,
        'genes' => $genes,
        'samples' => $samples,
        'values' => $values,
        'groups' => $sampleGroups,
        'count' => count($genes)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch heatmap data',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
