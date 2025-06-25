<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireAuth();
require_once 'includes/header.php';

$monthlyStreaks = getMonthlyStreaks($pdo, $_SESSION['user_id']);
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

<div class="monthly-progress">
    <h3>Monthly Progress</h3>
    <div class="month-grid">
        <?php
        $months = ['Feb', 'Mar', 'Apr', 'May', 'Jun']; // Example months
        foreach ($months as $month): 
        ?>
        <div class="month-card">
            <div class="month-name"><?= $month ?></div>
            <div class="month-stats">0/<?= 
                $month == 'Feb' ? '28' : 
                ($month == 'Apr' || $month == 'Jun' ? '30' : '31') 
            ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
    
    <div class="daily-streaks">
    <h3>Daily Streak</h3>
    <div class="streak-days">
        <?php
        // Show empty circles for new users
        for ($i = 0; $i < 5; $i++): 
            $isToday = $i === 4; // Last circle represents today
        ?>
        <div class="streak-day <?= $isToday ? 'current-day' : '' ?>">
            <?= $isToday ? '☐' : '○' ?>
        </div>
        <?php endfor; ?>
    </div>
</div>

<script src="assets/js/script.js"></script>
</div>
</body>
</html>