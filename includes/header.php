<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo Streaks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>TODO STREAKS</h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php endif; ?>
        </header>