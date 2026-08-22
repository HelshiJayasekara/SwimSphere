<?php
require_once __DIR__ . '/session.php';
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-container">
        <a href="/" class="logo" style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 1.5rem; text-decoration: none;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--clr-aqua)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12h4l3-9 5 14 3-9h5"></path>
            </svg>
            <span><span style="color: var(--clr-white);">Swim</span><span style="color: var(--clr-aqua);">Sphere</span></span>
        </a>
        
        <nav class="main-nav" id="main-nav">
            <ul class="nav-list">
                <li><a href="/" class="nav-link active">Home</a></li>
                <li><a href="/#about" class="nav-link">About</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/dashboard.php" class="nav-link">My Posts</a></li>
                    <li><a href="/create.php" class="nav-link">Create Blog</a></li>
                    <li><a href="/dashboard.php#bookmarks" class="nav-link">Bookmarks</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="margin-right: 15px; color: var(--clr-white); font-weight: 500;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="/auth/logout.php" class="btn btn-logout" style="background: transparent; border: 1px solid var(--clr-aqua); color: var(--clr-white); border-radius: 8px; padding: 6px 16px; transition: all 0.3s ease;">Logout</a>
                <?php else: ?>
                    <a href="/auth/login.php" class="btn btn-outline" style="margin-right: 10px;">Login</a>
                    <a href="/auth/register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </nav>
        
        <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle navigation">
            <span class="hamburger"></span>
        </button>
    </div>
</header>
