<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_task':
            $task = trim($_POST['task']);
            if (!empty($task)) {
                $stmt = $pdo->prepare("INSERT INTO tasks (user_id, task_text) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $task]);
            }
            break;
            
        case 'update_task_status':
            $taskId = $_POST['task_id'];
            $completed = $_POST['completed'] ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$completed, $taskId, $_SESSION['user_id']]);
            
            if ($completed) {
                $today = date('Y-m-d');
                // Update daily streak
                $stmt = $pdo->prepare("INSERT INTO streaks (user_id, streak_date, status, is_completed_day) 
                                      VALUES (?, ?, 'completed', 0)
                                      ON DUPLICATE KEY UPDATE 
                                      status = 'completed'");
                $stmt->execute([$_SESSION['user_id'], $today]);
                
                // Update monthly streak
                updateMonthlyStreak($pdo, $_SESSION['user_id']);
            }
            break;
            
        case 'delete_task':
            $taskId = $_POST['task_id'];
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$taskId, $_SESSION['user_id']]);
            break;
    }
}

header("Location: index.php");
exit;
?>