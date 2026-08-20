<?php
// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// AUTHENTICATION CHECK
// ==========================================
// 1. Only logged-in users can access /create.php
// If the user is not logged in, redirect them to /auth/login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

// Include database connection
require_once 'config/database.php';

// Initialize variables for form preservation
$title = '';
$content = '';
$errors = [];

// ==========================================
// HANDLE FORM SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim unnecessary whitespace
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // 3. Validation: Ensure title and content are not empty
    if (empty($title)) {
        $errors[] = "Post title is required.";
    }
    
    if (empty($content)) {
        $errors[] = "Post content is required.";
    }

    // 4. Database insertion if no validation errors exist
    if (empty($errors)) {
        try {
            // Use a prepared SQL statement to prevent SQL injection
            $stmt = $pdo->prepare("INSERT INTO blogPost (title, content, user_id) VALUES (:title, :content, :user_id)");
            
            // 8. Ownership: The author/user ID must ALWAYS come from the authenticated session
            // We do not trust any user_id supplied through POST/GET/hidden fields.
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'user_id' => $_SESSION['user_id']
            ]);

            // 6. Successful creation: POST/Redirect/GET pattern
            // Set a success message and redirect to the dashboard
            $_SESSION['success_message'] = "Your post was published successfully!";
            header("Location: /dashboard.php");
            exit;
            
        } catch (PDOException $e) {
            // 11. Error handling: Do not expose database errors or raw SQL to the user
            $errors[] = "An error occurred while saving your post. Please try again later.";
        }
    }
}

// Include the standard SwimSphere header
require_once 'includes/header.php';
?>

<main class="create-post-page section-padding bg-light">
    <div class="container">
        <!-- Reusing the auth-card styling structure to maintain SwimSphere visual design consistency -->
        <div class="auth-card" style="max-width: 800px; margin: 0 auto; background: var(--clr-card); padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
            
            <div class="section-header" style="margin-bottom: 30px; text-align: left;">
                <h2 style="font-size: 2rem; color: var(--clr-navy);">Create New Post</h2>
                <p style="color: var(--clr-text-light);">Share your swimming experiences, techniques, and routines.</p>
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

            <!-- 2. Create Post Form -->
            <form action="/create.php" method="POST" class="post-form">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="title" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Post Title</label>
                    <!-- Using htmlspecialchars to prevent XSS when preserving previously entered values -->
                    <input type="text" id="title" name="title" class="form-control" style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1.1rem;" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 35px;">
                    <label for="content" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Post Content</label>
                    <!-- Using htmlspecialchars to prevent XSS. The textarea is used for multiline content. -->
                    <textarea id="content" name="content" class="form-control" style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; min-height: 250px; resize: vertical;" required><?php echo htmlspecialchars($content); ?></textarea>
                </div>

                <div class="form-actions" style="display: flex; gap: 15px; align-items: center;">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-large">Publish Post</button>
                    <!-- Cancel Button returning to dashboard -->
                    <a href="/dashboard.php" class="btn btn-outline btn-large">Cancel</a>
                </div>
            </form>
            
        </div>
    </div>
</main>

<?php 
// Include the standard SwimSphere footer
require_once 'includes/footer.php'; 
?>
