<?php
// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

// 2. Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /index.php");
    exit;
}

// Get the data
$post_id = $_POST['post_id'] ?? null;
$content = trim($_POST['content'] ?? '');

// Validation
if (!$post_id) {
    header("Location: /index.php");
    exit;
}

if (empty($content)) {
    $_SESSION['error_message'] = "Comment cannot be empty.";
    header("Location: /article.php?id=" . urlencode($post_id));
    exit;
}

if (mb_strlen($content) > 1000) {
    $_SESSION['error_message'] = "Comment is too long (maximum 1000 characters).";
    header("Location: /article.php?id=" . urlencode($post_id));
    exit;
}

// Include database
require_once 'config/database.php';

try {
    // Check if the blog post actually exists
    $check_stmt = $pdo->prepare("SELECT id FROM blogPost WHERE id = :id");
    $check_stmt->execute(['id' => $post_id]);
    if (!$check_stmt->fetch()) {
        header("Location: /index.php");
        exit;
    }

    // Insert the comment
    $stmt = $pdo->prepare("INSERT INTO comment (user_id, blog_post_id, content) VALUES (:user_id, :post_id, :content)");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'post_id' => $post_id,
        'content' => $content
    ]);
    
    $_SESSION['success_message'] = "Your comment was posted successfully!";
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "A database error occurred while posting your comment.";
}

// Redirect back to the article
header("Location: /article.php?id=" . urlencode($post_id) . "#comments");
exit;
