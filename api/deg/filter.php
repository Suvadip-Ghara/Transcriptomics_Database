<?php
/**
 * DEG Filter API Endpoint
 * GET /api/deg/filter.php
 * 
 * Parameters:
 * - tissue: Filter by tissue type (optional)
 * - condition: Filter by condition comparison (optional)
 * - minLogFC: Minimum absolute log fold change (default: 0)
 * - sortBy: Column to sort by (default: p_value)
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
    $tissue = isset($_GET['tissue']) && !empty($_GET['tissue']) ? $_GET['tissue'] : null;
    $condition = isset($_GET['condition']) && !empty($_GET['condition']) ? $_GET['condition'] : null;
    $minLogFC = isset($_GET['minLogFC']) ? floatval($_GET['minLogFC']) : 0;
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'p_value';
    $sortOrder = isset($_GET['sortOrder']) && strtoupper($_GET['sortOrder']) === 'DESC' ? 'DESC' : 'ASC';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(1000, max(1, intval($_GET['limit']))) : 100;
    
    // Validate sortBy column (whitelist to prevent SQL injection)
    $allowedSortColumns = ['id', 'gene_id', 'gene_name', 'tissue', 'condition_name', 'log_fc', 'p_value', 'regulation'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'p_value';
    }
    
    // Map sortBy to fully qualified column name
    $sortColumnMap = [
        'id' => 'dr.id',
        'gene_id' => 'dr.gene_id',
        'gene_name' => 'g.gene_name',
        'tissue' => 'dr.tissue',
        'condition_name' => 'dr.condition_name',
        'log_fc' => 'dr.log_fc',
        'p_value' => 'dr.p_value',
        'regulation' => 'dr.regulation'
    ];
    $sortColumn = $sortColumnMap[$sortBy] ?? 'dr.p_value';
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if ($tissue !== null) {
        $where[] = "dr.tissue = :tissue";
        $params[':tissue'] = $tissue;
    }
    
    if ($condition !== null) {
        $where[] = "dr.condition_name = :condition";
        $params[':condition'] = $condition;
    }
    
    if ($minLogFC > 0) {
        $where[] = "ABS(dr.log_fc) >= :minLogFC";
        $params[':minLogFC'] = $minLogFC;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Calculate offset for pagination
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countSQL = "SELECT COUNT(*) as count 
                 FROM deg_results dr 
                 $whereClause";
    $total = executeCount($pdo, $countSQL, $params);
    
    // Get up-regulated count
    $upParams = $params;
    $upWhere = $where;
    $upWhere[] = "dr.regulation = 'Up'";
    $upWhereClause = !empty($upWhere) ? "WHERE " . implode(" AND ", $upWhere) : "";
    $upSQL = "SELECT COUNT(*) as count 
              FROM deg_results dr 
              $upWhereClause";
    $upCount = executeCount($pdo, $upSQL, $upParams);
    
    // Get down-regulated count
    $downParams = $params;
    $downWhere = $where;
    $downWhere[] = "dr.regulation = 'Down'";
    $downWhereClause = !empty($downWhere) ? "WHERE " . implode(" AND ", $downWhere) : "";
    $downSQL = "SELECT COUNT(*) as count 
                FROM deg_results dr 
                $downWhereClause";
    $downCount = executeCount($pdo, $downSQL, $downParams);
    
    // Get paginated data with gene information
    $dataSQL = "SELECT 
                    dr.id,
                    dr.gene_id,
                    g.gene_name,
                    dr.tissue,
                    dr.condition_name as `condition`,
                    dr.log_fc as logFC,
                    dr.p_value as pVal,
                    dr.regulation as reg,
                    g.protein_description as description
                FROM deg_results dr
                LEFT JOIN genes g ON dr.gene_id = g.gene_id
                $whereClause
                ORDER BY $sortColumn $sortOrder
                LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($dataSQL);
    
    // Bind filter parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Bind pagination parameters
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    // Convert numeric strings to proper types
    foreach ($data as &$row) {
        $row['logFC'] = isset($row['logFC']) ? floatval($row['logFC']) : null;
        $row['pVal'] = isset($row['pVal']) ? floatval($row['pVal']) : null;
    }
    
    // Build response
    $response = [
        'success' => true,
        'data' => $data,
        'total' => $total,
        'up' => $upCount,
        'down' => $downCount,
        'page' => $page,
        'limit' => $limit,
        'totalPages' => ceil($total / $limit)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch DEG data',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
}
