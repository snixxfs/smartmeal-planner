<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    // Get total users count
    $total_users = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc()['count'];
    
    // Get total meals count
    $total_meals = $conn->query("SELECT COUNT(*) as count FROM meals")->fetch_assoc()['count'];
    
    // Get active users (users who have created meals in the last 30 days)
    $active_users = $conn->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM meals 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetch_assoc()['count'];

    // Get average meals per user
    $avg_meals = $conn->query("
        SELECT AVG(meal_count) as avg_meals
        FROM (
            SELECT user_id, COUNT(*) as meal_count 
            FROM meals 
            GROUP BY user_id
        ) as meal_counts
    ")->fetch_assoc()['avg_meals'];

    // Get user details with their meal counts and latest activity
    $users = [];
    $result = $conn->query("
        SELECT 
            u.id,
            u.username,
            u.created_at,
            MAX(GREATEST(COALESCE(m.created_at, '1970-01-01 00:00:00'), COALESCE(sl.created_at, '1970-01-01 00:00:00'))) as last_activity
        FROM user u
        LEFT JOIN meals m ON u.id = m.user_id
        LEFT JOIN shopping_list sl ON u.id = sl.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    
    while ($row = $result->fetch_assoc()) {
        // User status will be determined on the frontend based on last_activity
        // Format dates
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        // last_activity is already formatted by the SQL query, but ensure it's not the default '1970-01-01 00:00:00'
        if ($row['last_activity'] === '1970-01-01 00:00:00') {
            $row['last_activity'] = null; // Use null for users with no activity
        }
            
        $users[] = $row;
    }
    
    // Get recent activity
    $recent_activity = [];
    $result = $conn->query("
        (SELECT 
            'meal' as type,
            u.username,
            m.name as detail,
            m.created_at
        FROM meals m
        JOIN user u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT 5)
        UNION ALL
        (SELECT 
            'shopping' as type,
            u.username,
            sl.item_name as detail,
            sl.created_at
        FROM shopping_list sl
        JOIN user u ON sl.user_id = u.id
        ORDER BY sl.created_at DESC
        LIMIT 5)
        ORDER BY created_at DESC
        LIMIT 10
    ");
    
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $recent_activity[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "stats" => [
            "totalUsers" => (int)$total_users,
            "totalMeals" => (int)$total_meals,
            "activeUsers" => (int)$active_users,
            "avgMealsPerUser" => round($avg_meals, 1)
        ],
        "users" => $users,
        "recentActivity" => $recent_activity
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?> 