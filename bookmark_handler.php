<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /index.php");
    exit;
}
require_once 'config/database.php';

$post_id = $_POST['post_id'] ?? null;
$action = $_POST['action'] ?? null; // 'add' or 'remove'
// Allow redirecting back to the dashboard if unbookmarked from there
$redirect_to = $_POST['redirect_to'] ?? '/article.php?id=' . urlencode($post_id);

// Basic validation for redirect destination to prevent open redirect vulnerabilities
if (strpos($redirect_to, '/') !== 0) {
    $redirect_to = '/index.php';
}

if ($post_id && $action) {
    try {
        if ($action === 'add') {
            // INSERT IGNORE safely handles the unique database constraint
            $stmt = $pdo->prepare("INSERT IGNORE INTO bookmark (user_id, blog_post_id) VALUES (:user_id, :post_id)");
        } else if ($action === 'remove') {
            // Remove exactly this user's bookmark
            $stmt = $pdo->prepare("DELETE FROM bookmark WHERE user_id = :user_id AND blog_post_id = :post_id");
        }
        
        if (isset($stmt)) {
            $stmt->execute([
                'user_id' => $_SESSION['user_id'], 
                'post_id' => $post_id
            ]);
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "An error occurred while updating your bookmarks.";
    }
}

header("Location: " . $redirect_to);
exit;
