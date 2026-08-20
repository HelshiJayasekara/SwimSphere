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
$comment_id = $_POST['comment_id'] ?? null;
$post_id = $_POST['post_id'] ?? null;
$content = trim($_POST['content'] ?? '');

// Validation
if (!$comment_id || !$post_id) {
    header("Location: /index.php");
    exit;
}

if (empty($content)) {
    $_SESSION['error_message'] = "Comment cannot be empty.";
    header("Location: /article.php?id=" . urlencode($post_id) . "#comment-" . $comment_id);
    exit;
}

if (mb_strlen($content) > 1000) {
    $_SESSION['error_message'] = "Comment is too long (maximum 1000 characters).";
    header("Location: /article.php?id=" . urlencode($post_id) . "#comment-" . $comment_id);
    exit;
}

// Include database
require_once 'config/database.php';

try {
    // Attempt to update the comment. The WHERE clause ensures they can only edit their OWN comment.
    $stmt = $pdo->prepare("UPDATE comment SET content = :content WHERE id = :comment_id AND user_id = :user_id");
    $stmt->execute([
        'content' => $content,
        'comment_id' => $comment_id,
        'user_id' => $_SESSION['user_id']
    ]);
    
    // Check if a row was actually updated
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Your comment was updated successfully!";
    } else {
        // If rowCount is 0, it means either the comment didn't change, or they don't own it.
        // We do a quick check to see if they own it, just to give a clear error message.
        $check_stmt = $pdo->prepare("SELECT id FROM comment WHERE id = :comment_id AND user_id = :user_id");
        $check_stmt->execute(['comment_id' => $comment_id, 'user_id' => $_SESSION['user_id']]);
        if (!$check_stmt->fetch()) {
            $_SESSION['error_message'] = "You do not have permission to edit this comment.";
        }
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "A database error occurred while updating your comment.";
}

// Redirect back to the article, optionally anchoring to the comment section
header("Location: /article.php?id=" . urlencode($post_id) . "#comments");
exit;
