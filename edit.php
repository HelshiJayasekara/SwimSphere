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
$image_path = '';
$errors = [];

// Fetch existing post to load into form (and verify ownership)
try {
    $stmt = $pdo->prepare("SELECT id, title, content, image_path FROM blogPost WHERE id = :id AND user_id = :user_id");
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
        $image_path = $post['image_path'];
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
        // Fetch current image path again to handle deletion safely
        $stmt = $pdo->prepare("SELECT image_path FROM blogPost WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $post_id, 'user_id' => $_SESSION['user_id']]);
        $current_image_path = $stmt->fetchColumn();
        
        $new_image_path = $current_image_path;
        $should_delete_old = false;
        
        // Handle removing image
        if (isset($_POST['remove_image']) && $_POST['remove_image'] === 'yes') {
            $new_image_path = null;
            $should_delete_old = true;
        }

        // Handle Image Upload
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
                            $new_image_path = '/uploads/articles/' . $new_filename;
                            $should_delete_old = true;
                        } else {
                            $errors[] = "Failed to save uploaded image.";
                        }
                    }
                }
            }
        }

        if (empty($errors)) {
            try {
                // Update the post ensuring ownership
                $stmt = $pdo->prepare("UPDATE blogPost SET title = :title, content = :content, image_path = :image_path WHERE id = :id AND user_id = :user_id");
                
                $stmt->execute([
                    'title' => $title,
                    'content' => $content,
                    'image_path' => $new_image_path,
                    'id' => $post_id,
                    'user_id' => $_SESSION['user_id']
                ]);

                // Delete old image if it was replaced or removed
                if ($should_delete_old && $current_image_path) {
                    $old_file = __DIR__ . $current_image_path;
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }

                // Redirect back to dashboard on success
                $_SESSION['success_message'] = "Your post was updated successfully!";
                header("Location: /dashboard.php");
                exit;
                
            } catch (PDOException $e) {
                $errors[] = "An error occurred while updating your post. Please try again.";
            }
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
            <form action="/edit.php" method="POST" class="post-form" enctype="multipart/form-data">
                <!-- Hidden input to pass the ID via POST securely -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($post_id); ?>">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="title" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Post Title</label>
                    <input type="text" id="title" name="title" class="form-control" style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1.1rem;" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="image" style="display: block; font-weight: 500; margin-bottom: 8px; color: #F5F7FA;">Post Image</label>
                    <div style="position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 15px; background: #071A2A; padding: 20px; border-radius: 12px; border: 1px solid rgba(8, 200, 245, 0.2);">
                        
                        <?php if (!empty($image_path)): ?>
                            <div id="current-image-container" style="margin-bottom: 15px;">
                                <p style="color: #A8B8C8; font-size: 0.9rem; margin-bottom: 8px;">Current Image:</p>
                                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Current Post Image" style="max-width: 100%; max-height: 200px; border-radius: 8px; object-fit: cover; border: 1px solid #08C8F5; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="remove_image" name="remove_image" value="yes">
                                    <label for="remove_image" style="color: #F5F7FA; font-size: 0.9rem;">Remove current image</label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <label for="image" style="display: inline-block; padding: 12px 24px; background: transparent; color: #F5F7FA; border: 1px solid #08C8F5; border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600; box-shadow: 0 0 10px rgba(8, 200, 245, 0.15); transition: all 0.3s ease; max-width: 250px;" onmouseover="this.style.boxShadow='0 0 15px rgba(8, 200, 245, 0.4)'" onmouseout="this.style.boxShadow='0 0 10px rgba(8, 200, 245, 0.15)'">
                            <?php echo empty($image_path) ? 'Choose Image' : 'Choose New Image'; ?>
                        </label>
                        <input type="file" id="image" name="image" accept=".jpg, .jpeg, .png, .webp" style="display: none;" onchange="previewImage(this)">
                        <span id="file-name" style="color: #A8B8C8; font-size: 0.9rem;">No file chosen</span>
                        <div id="image-preview-container" style="display: none; position: relative; margin-top: 10px;">
                            <p style="color: #A8B8C8; font-size: 0.9rem; margin-bottom: 8px;">New Image Preview:</p>
                            <img id="image-preview" src="#" alt="Image Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; object-fit: cover; border: 1px solid #08C8F5;">
                            <button type="button" onclick="removeNewImage()" style="position: absolute; top: 35px; right: 10px; background: rgba(7, 26, 42, 0.85); color: #F5F7FA; border: 1px solid #08C8F5; border-radius: 4px; padding: 5px 12px; cursor: pointer; font-size: 0.85rem;">Remove Selection</button>
                        </div>
                    </div>
                </div>

                <script>
                function previewImage(input) {
                    const previewContainer = document.getElementById('image-preview-container');
                    const previewImage = document.getElementById('image-preview');
                    const fileName = document.getElementById('file-name');
                    const currentImageContainer = document.getElementById('current-image-container');
                    const removeCheckbox = document.getElementById('remove_image');
                    
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewContainer.style.display = 'block';
                            fileName.textContent = input.files[0].name;
                            if (currentImageContainer) {
                                currentImageContainer.style.display = 'none';
                            }
                            if (removeCheckbox) {
                                removeCheckbox.checked = false;
                            }
                        }
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        removeNewImage();
                    }
                }
                function removeNewImage() {
                    document.getElementById('image').value = '';
                    document.getElementById('image-preview-container').style.display = 'none';
                    document.getElementById('image-preview').src = '#';
                    document.getElementById('file-name').textContent = 'No file chosen';
                    const currentImageContainer = document.getElementById('current-image-container');
                    if (currentImageContainer) {
                        currentImageContainer.style.display = 'block';
                    }
                }
                </script>

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
