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
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        
        // Set today's streak to pending
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO streaks (user_id, streak_date, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $today]);
        
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Username or email already exists";
        } else {
            $error = "An error occurred. Please try again.";
        }
    }
}
?>

<div class="auth-container">
    <h2 class="auth-title">Sign Up</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form action="signup.php" method="POST">
        <div class="form-group">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
    </form>
    
    <p style="text-align: center; margin-top: 15px;">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>