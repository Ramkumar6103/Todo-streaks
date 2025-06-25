<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Add this after the database connection in process.php
function initializeStreaks($pdo, $userId) {
    $today = date('Y-m-d');
    
    // Initialize today's streak if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO streaks (user_id, streak_date, status) 
                          VALUES (?, ?, 'pending')");
    $stmt->execute([$userId, $today]);
    
    // Initialize current month if not exists
    $currentYear = date('Y');
    $currentMonth = date('n');
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO monthly_streaks 
                          (user_id, year, month, completed_days, total_days) 
                          VALUES (?, ?, ?, 0, ?)");
    $stmt->execute([$userId, $currentYear, $currentMonth, $daysInMonth]);
}

// Call this when a user logs in or creates an account
initializeStreaks($pdo, $_SESSION['user_id']);

function getMonthlyStreaks($pdo, $userId, $monthCount = 5) {
    $currentYear = date('Y');
    $currentMonth = date('n');
    $streaks = [];
    
    for ($i = 0; $i < $monthCount; $i++) {
        $year = $currentYear;
        $month = $currentMonth - $i;
        
        if ($month < 1) {
            $month += 12;
            $year--;
        }
        
        $stmt = $pdo->prepare("SELECT completed_days, total_days 
                              FROM monthly_streaks 
                              WHERE user_id = ? AND year = ? AND month = ?");
        $stmt->execute([$userId, $year, $month]);
        $data = $stmt->fetch();
        
        $streaks[] = [
            'month' => date('M', mktime(0, 0, 0, $month, 1)),
            'year' => $year,
            'completed' => $data['completed_days'] ?? 0,
            'total' => $data['total_days'] ?? cal_days_in_month(CAL_GREGORIAN, $month, $year),
            'month_num' => $month
        ];
    }
    
    return array_reverse($streaks);
}

function updateMonthlyStreak($pdo, $userId) {
    $today = date('Y-m-d');
    $currentYear = date('Y');
    $currentMonth = date('n');
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
    
    // Check if today's task is already counted
    $stmt = $pdo->prepare("SELECT 1 FROM streaks 
                          WHERE user_id = ? AND streak_date = ? AND is_completed_day = 1");
    $stmt->execute([$userId, $today]);
    
    if (!$stmt->fetch()) {
        // Update monthly streak
        $stmt = $pdo->prepare("INSERT INTO monthly_streaks 
                              (user_id, year, month, completed_days, total_days, last_updated)
                              VALUES (?, ?, ?, 1, ?, ?)
                              ON DUPLICATE KEY UPDATE 
                              completed_days = IF(last_updated = CURDATE(), 
                                                completed_days, 
                                                completed_days + 1),
                              total_days = ?,
                              last_updated = CURDATE()");
        $stmt->execute([
            $userId, 
            $currentYear, 
            $currentMonth, 
            $daysInMonth,
            $today,
            $daysInMonth
        ]);
        
        // Mark today as counted
        $stmt = $pdo->prepare("UPDATE streaks 
                              SET is_completed_day = 1 
                              WHERE user_id = ? AND streak_date = ?");
        $stmt->execute([$userId, $today]);
    }
}