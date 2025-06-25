<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
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
            
            // Update streak if this is today's task
            if ($completed) {
                $today = date('Y-m-d');
                $stmt = $pdo->prepare("UPDATE streaks SET status = 'completed' 
                                      WHERE user_id = ? AND streak_date = ?");
                $stmt->execute([$_SESSION['user_id'], $today]);
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