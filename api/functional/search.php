<?php
/**
 * Functional Annotation Search API Endpoint
 * GET /api/functional/search.php
 * 
 * Parameters:
 * - query: Search term (required)
 * - sortBy: Column to sort by (default: gene_id)
 * - sortOrder: Sort direction asc/desc (default: asc)
 * - page: Page number (default: 1)
 * - limit: Records per page (default: 100)
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Get query parameters
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'gene_id';
    $sortOrder = isset($_GET['sortOrder']) && strtoupper($_GET['sortOrder']) === 'DESC' ? 'DESC' : 'ASC';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(1000, max(1, intval($_GET['limit']))) : 100;
    
    if (empty($query)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Query parameter is required',
            'message' => 'Please provide a search query'
        ]);
        exit();
    }
    
    // Validate sortBy column (whitelist to prevent SQL injection)
    $allowedSortColumns = ['gene_id', 'gene_name', 'protein_description'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'gene_id';
    }
    
    // Map to actual table columns
    $sortColumn = $sortBy === 'gene_name' ? 'g.gene_name' : 
                  ($sortBy === 'protein_description' ? 'g.protein_description' : 'fa.gene_id');
    
    // Build search conditions using FULLTEXT and LIKE
    $searchTerm = "%{$query}%";
    
    // Calculate offset for pagination
    $offset = ($page - 1) * $limit;
    
    // Use FULLTEXT for longer queries, LIKE-only for short ones
    // MySQL FULLTEXT has a default minimum word length of 3-4 chars
    $useFulltext = mb_strlen($query) >= 3;
    
    if ($useFulltext) {
        $searchCondition = "(MATCH(fa.go_terms, fa.kegg_pathway) AGAINST(:fulltext IN NATURAL LANGUAGE MODE)
                    OR g.gene_id LIKE :search1
                    OR g.gene_name LIKE :search2
                    OR g.protein_description LIKE :search3)";
    } else {
        $searchCondition = "(fa.go_terms LIKE :search1
                    OR fa.kegg_pathway LIKE :search_kegg
                    OR g.gene_id LIKE :search2
                    OR g.gene_name LIKE :search3
                    OR g.protein_description LIKE :search4)";
    }
    
    // Count total matching records
    $countSQL = "SELECT COUNT(*) as count 
                 FROM functional_annotations fa
                 JOIN genes g ON fa.gene_id = g.gene_id
                 WHERE $searchCondition";
    
    if ($useFulltext) {
        $countParams = [
            ':fulltext' => $query,
            ':search1' => $searchTerm,
            ':search2' => $searchTerm,
            ':search3' => $searchTerm
        ];
    } else {
        $countParams = [
            ':search1' => $searchTerm,
            ':search_kegg' => $searchTerm,
            ':search2' => $searchTerm,
            ':search3' => $searchTerm,
            ':search4' => $searchTerm
        ];
    }
    
    $total = executeCount($pdo, $countSQL, $countParams);
    
    // Get paginated data
    $dataSQL = "SELECT 
                    fa.id,
                    fa.gene_id,
                    g.gene_name as name,
                    g.protein_description as `desc`,
                    fa.go_terms as `go`,
                    fa.kegg_pathway as kegg
                FROM functional_annotations fa
                JOIN genes g ON fa.gene_id = g.gene_id
                WHERE $searchCondition
                ORDER BY $sortColumn $sortOrder
                LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($dataSQL);
    
    if ($useFulltext) {
        $stmt->bindValue(':fulltext', $query);
        $stmt->bindValue(':search1', $searchTerm);
        $stmt->bindValue(':search2', $searchTerm);
        $stmt->bindValue(':search3', $searchTerm);
    } else {
        $stmt->bindValue(':search1', $searchTerm);
        $stmt->bindValue(':search_kegg', $searchTerm);
        $stmt->bindValue(':search2', $searchTerm);
        $stmt->bindValue(':search3', $searchTerm);
        $stmt->bindValue(':search4', $searchTerm);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll();
    
    // Truncate long fields for display
    foreach ($data as &$row) {
        $row['desc'] = isset($row['desc']) ? substr($row['desc'], 0, 100) : '';
        $row['go'] = isset($row['go']) ? substr($row['go'], 0, 150) : '';
        $row['kegg'] = isset($row['kegg']) ? substr($row['kegg'], 0, 100) : '';
        $row['name'] = isset($row['name']) && !empty($row['name']) ? $row['name'] : substr($row['gene_id'], 0, 20);
    }
    
    // Build response
    $response = [
        'success' => true,
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'totalPages' => ceil($total / $limit)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to search functional annotations',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
