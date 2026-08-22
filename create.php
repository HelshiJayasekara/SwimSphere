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

    // 4. Handle Image Upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading image.";
        } else {
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = "Image size must be less than 5MB.";
            } else {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (!in_array($ext, $allowed_exts) || !in_array($mime, $allowed_mimes)) {
                    $errors[] = "Invalid image format. Only JPG, PNG, and WEBP are allowed.";
                } else {
                    $new_filename = uniqid('post_', true) . '.' . $ext;
                    $upload_dir = __DIR__ . '/uploads/articles/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                        $image_path = '/uploads/articles/' . $new_filename;
                    } else {
                        $errors[] = "Failed to save uploaded image.";
                    }
                }
            }
        }
    }

    // 5. Database insertion if no validation errors exist
    if (empty($errors)) {
        try {
            // Use a prepared SQL statement to prevent SQL injection
            $stmt = $pdo->prepare("INSERT INTO blogPost (title, content, user_id, image_path) VALUES (:title, :content, :user_id, :image_path)");
            
            // 8. Ownership: The author/user ID must ALWAYS come from the authenticated session
            // We do not trust any user_id supplied through POST/GET/hidden fields.
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'user_id' => $_SESSION['user_id'],
                'image_path' => $image_path
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
            <form action="/create.php" method="POST" class="post-form" enctype="multipart/form-data">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="title" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Post Title</label>
                    <!-- Using htmlspecialchars to prevent XSS when preserving previously entered values -->
                    <input type="text" id="title" name="title" class="form-control" style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1.1rem;" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="image" style="display: block; font-weight: 500; margin-bottom: 8px; color: #F5F7FA;">Post Image</label>
                    <div style="position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 15px; background: #071A2A; padding: 20px; border-radius: 12px; border: 1px solid rgba(8, 200, 245, 0.2);">
                        <label for="image" style="display: inline-block; padding: 12px 24px; background: transparent; color: #F5F7FA; border: 1px solid #08C8F5; border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600; box-shadow: 0 0 10px rgba(8, 200, 245, 0.15); transition: all 0.3s ease; max-width: 200px;" onmouseover="this.style.boxShadow='0 0 15px rgba(8, 200, 245, 0.4)'" onmouseout="this.style.boxShadow='0 0 10px rgba(8, 200, 245, 0.15)'">
                            Choose Image
                        </label>
                        <input type="file" id="image" name="image" accept=".jpg, .jpeg, .png, .webp" style="display: none;" onchange="previewImage(this)">
                        <span id="file-name" style="color: #A8B8C8; font-size: 0.9rem;">No file chosen</span>
                        <div id="image-preview-container" style="display: none; position: relative; margin-top: 10px;">
                            <img id="image-preview" src="#" alt="Image Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; object-fit: cover; border: 1px solid #08C8F5;">
                            <button type="button" onclick="removeImage()" style="position: absolute; top: 10px; right: 10px; background: rgba(7, 26, 42, 0.85); color: #F5F7FA; border: 1px solid #08C8F5; border-radius: 4px; padding: 5px 12px; cursor: pointer; font-size: 0.85rem;">Remove</button>
                        </div>
                    </div>
                </div>

                <script>
                function previewImage(input) {
                    const previewContainer = document.getElementById('image-preview-container');
                    const previewImage = document.getElementById('image-preview');
                    const fileName = document.getElementById('file-name');
                    
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewContainer.style.display = 'inline-block';
                            fileName.textContent = input.files[0].name;
                        }
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        removeImage();
                    }
                }
                function removeImage() {
                    document.getElementById('image').value = '';
                    document.getElementById('image-preview-container').style.display = 'none';
                    document.getElementById('image-preview').src = '#';
                    document.getElementById('file-name').textContent = 'No file chosen';
                }
                </script>

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
