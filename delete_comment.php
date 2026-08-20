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

// 2. Only allow POST requests for deletion (security requirement)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /index.php");
    exit;
}

// Get the IDs from POST data
$comment_id = $_POST['comment_id'] ?? null;
$post_id = $_POST['post_id'] ?? null;

if (!$comment_id || !$post_id) {
    header("Location: /index.php");
    exit;
}

// Include database
require_once 'config/database.php';

try {
    // 3. Delete the correct comment record.
    // 4. Make sure the logged-in user can delete ONLY their own comments.
    // By enforcing user_id = :user_id, we prevent deleting other users' comments.
    $stmt = $pdo->prepare("DELETE FROM comment WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        'id' => $comment_id,
        'user_id' => $_SESSION['user_id']
    ]);
    
    // Check if a row was actually deleted
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Your comment was deleted.";
    } else {
        // Either the comment didn't exist or didn't belong to the user
        $_SESSION['error_message'] = "Failed to delete comment. You may not have permission.";
    }
} catch (PDOException $e) {
    // Do not expose raw database errors to the user
    $_SESSION['error_message'] = "A database error occurred while trying to delete the comment.";
}

// Redirect back to the article page
header("Location: /article.php?id=" . urlencode($post_id) . "#comments");
exit;
