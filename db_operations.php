<?php
/**
 * Database Operations
 * 
 * This file contains functions for interacting with the highway toll database.
 */

require_once 'db_config.php';

/**
 * Get total count of vehicles by vehicle type
 * 
 * @param int|null $vehicleTypeId Optional vehicle type ID to filter by
 * @return array Count of vehicles by type
 */
function getVehicleTypeCounts() {
    try {
        $pdo = getDbConnection();
        
        $query = "SELECT v.id, v.name, COUNT(htd.id) as count 
                 FROM vehicle v 
                 LEFT JOIN highway_toll_data htd ON v.id = htd.vehicle_id 
                 GROUP BY v.id, v.name 
                 ORDER BY v.name";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        logDbOperation('getVehicleTypeCounts', $query, [], $result);
        return $result;
    } catch (PDOException $e) {
        logDbOperation('getVehicleTypeCounts ERROR', $query, [], $e->getMessage());
        return [];
    }
}

/**
 * Get highway toll data with pagination and filters
 * 
 * @param array $filters Array of filter parameters
 * @param int $page Current page number
 * @param int $perPage Records per page
 * @return array Toll data records and pagination info
 */
function getHighwayTollData($filters = [], $page = 1, $perPage = 20) {
    try {
        $pdo = getDbConnection();
        
        // Base query
        $baseQuery = "SELECT htd.id, htd.lane_number, htd.vehicle_number, 
                     v.name as vehicle_type, vst.name as vehicle_subtype, 
                     htd.entry_time, htd.image_path, htd.video_path, htd.toll_tax
                     FROM highway_toll_data htd
                     LEFT JOIN vehicle v ON htd.vehicle_id = v.id
                     LEFT JOIN vehicle_sub_type vst ON htd.vehicle_sub_type_id = vst.id";
        
        // Initialize WHERE clause and parameters
        $whereClause = [];
        $params = [];
        
        // Vehicle type filter
        if (!empty($filters['vehicle_type'])) {
            $whereClause[] = "v.id = :vehicle_type";
            $params[':vehicle_type'] = $filters['vehicle_type'];
        }
        
        // Date range filters
        if (!empty($filters['start_date'])) {
            $whereClause[] = "htd.entry_time >= :start_date";
            $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        
        if (!empty($filters['end_date'])) {
            $whereClause[] = "htd.entry_time <= :end_date";
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        
        // Build the complete query
        $query = $baseQuery;
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        // Add order by
        $query .= " ORDER BY htd.entry_time DESC";
        
        // Count total records query
        $countQuery = "SELECT COUNT(*) FROM (" . $query . ") as filtered_count";
        $stmt = $pdo->prepare($countQuery);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $totalRecords = $stmt->fetchColumn();
        
        // Pagination
        $totalPages = ceil($totalRecords / $perPage);
        $page = max(1, min($page, $totalPages)); // Ensure page is within valid range
        $offset = ($page - 1) * $perPage;
        
        // Add pagination to main query
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;
        
        // Execute the main query
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        // Log the operation
        logDbOperation('getHighwayTollData', $query, $params, [
            'count' => count($data),
            'total' => $totalRecords,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
        
        // Return both data and pagination info
        return [
            'data' => $data,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalRecords)
            ]
        ];
    } catch (PDOException $e) {
        logDbOperation('getHighwayTollData ERROR', isset($query) ? $query : 'Query building error', $params ?? [], $e->getMessage());
        return [
            'data' => [],
            'pagination' => [
                'total' => 0,
                'perPage' => $perPage,
                'currentPage' => 1,
                'totalPages' => 0,
                'from' => 0,
                'to' => 0
            ],
            'error' => 'Database error occurred'
        ];
    }
}

/**
 * Get all vehicle types for filter dropdown
 * 
 * @return array List of vehicle types
 */
function getVehicleTypes() {
    try {
        $pdo = getDbConnection();
        
        $query = "SELECT id, name FROM vehicle ORDER BY name";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        logDbOperation('getVehicleTypes', $query, [], $result);
        return $result;
    } catch (PDOException $e) {
        logDbOperation('getVehicleTypes ERROR', $query, [], $e->getMessage());
        return [];
    }
}

/**
 * Format timestamp for display
 *
 * @param string $timestamp Timestamp string from database
 * @return string Formatted date and time
 */
function formatTimestamp($timestamp) {
    $date = new DateTime($timestamp);
    return $date->format('M j, Y g:i A');
}
?>