<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Set today's streak to pending if not set
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("INSERT IGNORE INTO streaks (user_id, streak_date, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$user['id'], $today]);
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<div class="auth-container">
    <h2 class="auth-title">Login</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <div class="form-group">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 15px;">
        Don't have an account? <a href="signup.php">Sign up</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>