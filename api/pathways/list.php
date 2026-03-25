<?php
/**
 * Pathways List API Endpoint
 * GET /api/pathways/list.php
 * 
 * Parameters:
 * - search: Search term for pathway name or ID (optional)
 * - sortBy: Column to sort by (default: gene_count)
 * - sortOrder: Sort direction asc/desc (default: desc)
 * - limit: Maximum records to return (default: all)
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get query parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'gene_count';
    $sortOrder = isset($_GET['sortOrder']) && strtoupper($_GET['sortOrder']) === 'DESC' ? 'DESC' : 'ASC';
    $limit = isset($_GET['limit']) ? min(1000, max(1, intval($_GET['limit']))) : null;
    
    // Validate sortBy column
    $allowedSortColumns = ['pathway_id', 'pathway_name', 'pathway_class', 'gene_count'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'gene_count';
    }
    
    // Build WHERE clause
    $where = "";
    $params = [];
    
    if ($search !== null && !empty($search)) {
        $searchTerm = "%{$search}%";
        $where = "WHERE pathway_name LIKE :search OR pathway_id LIKE :search";
        $params[':search'] = $searchTerm;
    }
    
    // Count total
    $countSQL = "SELECT COUNT(*) as count FROM pathways $where";
    $total = executeCount($pdo, $countSQL, $params);
    
    // Get data
    $limitClause = $limit !== null ? "LIMIT :limit" : "";
    $dataSQL = "SELECT 
                    pathway_id as id,
                    pathway_name as name,
                    pathway_class as class,
                    gene_count as genes
                FROM pathways
                $where
                ORDER BY $sortBy $sortOrder
                $limitClause";
    
    $stmt = $pdo->prepare($dataSQL);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($limit !== null) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    // Convert gene counts to integers
    foreach ($data as &$row) {
        $row['genes'] = isset($row['genes']) ? intval($row['genes']) : 0;
    }
    
    // Build response
    $response = [
        'success' => true,
        'data' => $data,
        'total' => $total
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch pathways',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
