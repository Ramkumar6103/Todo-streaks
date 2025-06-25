<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';
requireAuth(); // This replaces the manual check
include 'includes/header.php';

// Get today's date for streak tracking
$today = date('Y-m-d');
?>

<div class="todo-container">
    <div class="todo-header">
        <h2>TODAY'S WORKS</h2>
    </div>
    
    <form action="process.php" method="POST">
        <div class="form-group">
            <input type="text" name="task" class="form-control" placeholder="Add new task..." required>
            <input type="hidden" name="action" value="add_task">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Add</button>
    </form>
    
    <ul class="todo-list">
        <?php
        // Fetch user's tasks
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $tasks = $stmt->fetchAll();
        
        foreach ($tasks as $task):
        ?>
        <li class="todo-item">
            <input type="checkbox" 
                   class="task-checkbox" 
                   data-task-id="<?= $task['id'] ?>" 
                   <?= $task['completed'] ? 'checked' : '' ?>>
            <span class="todo-text <?= $task['completed'] ? 'completed' : '' ?>">
                <?= htmlspecialchars($task['task_text']) ?>
            </span>
            <button class="delete-btn" data-task-id="<?= $task['id'] ?>">Delete</button>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="streaks-container">
    <h3 class="streak-title">Streaks</h3>
    <div class="streak-days">
        <?php
        // Get the last 4 days including today
        $dates = [];
        for ($i = 3; $i >= 0; $i--) {
            $dates[] = date('Y-m-d', strtotime("-$i days"));
        }
        
        // Fetch streak data for these dates
        $streaks = [];
        $stmt = $pdo->prepare("SELECT streak_date, status FROM streaks 
                              WHERE user_id = ? AND streak_date BETWEEN ? AND ?");
        $stmt->execute([$_SESSION['user_id'], $dates[0], end($dates)]);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($dates as $date) {
            $status = $results[$date] ?? 'pending';
            $icon = '';
            
            if ($status === 'completed') {
                $icon = '☑';
                $class = 'streak-completed';
            } elseif ($status === 'missed') {
                $icon = '☒';
                $class = 'streak-missed';
            } else {
                $icon = '☐';
                $class = 'streak-pending';
            }
            
            echo "<div class='streak-day $class'>$icon</div>";
        }
        ?>
    </div>
</div>

<script src="assets/js/script.js"></script>
<?php include 'includes/footer.php'; ?>