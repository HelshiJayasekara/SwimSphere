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

// Include database connection
require_once 'config/database.php';

// Get the post ID (from GET initially, then POST on save)
$post_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$post_id) {
    header("Location: /dashboard.php");
    exit;
}

// Initialize variables
$title = '';
$content = '';
$errors = [];

// Fetch existing post to load into form (and verify ownership)
try {
    $stmt = $pdo->prepare("SELECT id, title, content FROM blogPost WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        'id' => $post_id,
        'user_id' => $_SESSION['user_id'] // SECURITY: Enforce ownership
    ]);
    
    $post = $stmt->fetch();
    
    if (!$post) {
        // Post not found or doesn't belong to this user
        $_SESSION['error_message'] = "Post not found or you don't have permission to edit it.";
        header("Location: /dashboard.php");
        exit;
    }
    
    // Set initial values if this is a fresh page load
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $title = $post['title'];
        $content = $post['content'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "A database error occurred while fetching the post.";
    header("Location: /dashboard.php");
    exit;
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // Validation
    if (empty($title)) {
        $errors[] = "Post title is required.";
    }
    
    if (empty($content)) {
        $errors[] = "Post content is required.";
    }

    // Database update if no errors
    if (empty($errors)) {
        try {
            // Update the post ensuring ownership
            $stmt = $pdo->prepare("UPDATE blogPost SET title = :title, content = :content WHERE id = :id AND user_id = :user_id");
            
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'id' => $post_id,
                'user_id' => $_SESSION['user_id']
            ]);

            // Redirect back to dashboard on success
            $_SESSION['success_message'] = "Your post was updated successfully!";
            header("Location: /dashboard.php");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "An error occurred while updating your post. Please try again.";
        }
    }
}

// Include the header
require_once 'includes/header.php';
?>

<main class="edit-post-page section-padding bg-light">
    <div class="container">
        <!-- Reusing auth-card styling to maintain SwimSphere design -->
        <div class="auth-card" style="max-width: 800px; margin: 0 auto; background: var(--clr-card); padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
            
            <div class="section-header" style="margin-bottom: 30px; text-align: left;">
                <h2 style="font-size: 2rem; color: var(--clr-navy);">Edit Post</h2>
                <p style="color: var(--clr-text-light);">Update your swimming post.</p>
            </div>

            <!-- Display Validation Errors -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #f5c6cb;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($errors as $error): ?>
                            <li style="margin-bottom: 5px;">⚠️ <?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Edit Post Form -->
            <form action="/edit.php" method="POST" class="post-form">
                <!-- Hidden input to pass the ID via POST securely -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($post_id); ?>">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="title" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Post Title</label>
                    <input type="text" id="title" name="title" class="form-control" style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1.1rem;" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 35px;">
                    <label for="content" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Post Content</label>
                    <textarea id="content" name="content" class="form-control" style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; min-height: 250px; resize: vertical;" required><?php echo htmlspecialchars($content); ?></textarea>
                </div>

                <div class="form-actions" style="display: flex; gap: 15px; align-items: center;">
                    <button type="submit" class="btn btn-primary btn-large">Save Changes</button>
                    <a href="/dashboard.php" class="btn btn-outline btn-large">Cancel</a>
                </div>
            </form>
            
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
