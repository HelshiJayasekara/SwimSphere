<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwimSphere | Dive Into Better Swimming</title>
    <meta name="description" content="Explore swimming techniques, training advice, fitness tips and everything you need to become a better swimmer.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-container">
        <a href="/" class="logo">
            <span class="logo-icon">🌊</span> SwimSphere
        </a>
        
        <nav class="main-nav" id="main-nav">
            <ul class="nav-list">
                <li><a href="/" class="nav-link active">Home</a></li>
                <li><a href="/#categories" class="nav-link">Techniques</a></li>
                <li><a href="/#articles" class="nav-link">Training</a></li>
                <li><a href="/#articles" class="nav-link">Fitness</a></li>
                <li><a href="/#articles" class="nav-link">Safety</a></li>
                <li><a href="/#about" class="nav-link">About</a></li>
            </ul>
            
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="margin-right: 15px; color: var(--clr-navy); font-weight: 500;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="/auth/logout.php" class="btn btn-outline">Logout</a>
                <?php else: ?>
                    <a href="/auth/login.php" class="btn btn-outline">Login</a>
                    <a href="/auth/register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </nav>
        
        <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle navigation">
            <span class="hamburger"></span>
        </button>
    </div>
</header>
