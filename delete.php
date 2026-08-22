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
    header("Location: /dashboard.php");
    exit;
}

// Get the post ID from POST data
$post_id = $_POST['id'] ?? null;

if (!$post_id) {
    header("Location: /dashboard.php");
    exit;
}

// Include database
require_once 'config/database.php';

try {
    // Fetch image path before deletion
    $stmtImg = $pdo->prepare("SELECT image_path FROM blogPost WHERE id = :id AND user_id = :user_id");
    $stmtImg->execute(['id' => $post_id, 'user_id' => $_SESSION['user_id']]);
    $image_path = $stmtImg->fetchColumn();

    // 3. Delete the correct blogPost record.
    // 4. Make sure the logged-in user can delete ONLY their own posts.
    // By enforcing user_id = :user_id, we prevent deleting other users' posts.
    $stmt = $pdo->prepare("DELETE FROM blogPost WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        'id' => $post_id,
        'user_id' => $_SESSION['user_id']
    ]);
    
    // Check if a row was actually deleted
    if ($stmt->rowCount() > 0) {
        // Delete the image file if it exists
        if (!empty($image_path)) {
            $file_to_delete = __DIR__ . $image_path;
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }
        $_SESSION['success_message'] = "Your post was successfully deleted.";
    } else {
        // Either the post didn't exist or didn't belong to the user
        $_SESSION['error_message'] = "Failed to delete post. You may not have permission.";
    }
} catch (PDOException $e) {
    // Do not expose raw database errors to the user
    $_SESSION['error_message'] = "A database error occurred while trying to delete the post.";
}

// 6. Redirect back to dashboard.php
header("Location: /dashboard.php");
exit;
