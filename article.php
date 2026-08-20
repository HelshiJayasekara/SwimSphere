<?php
// Start session for potential header auth logic
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Get post ID from query string
$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    // No ID provided, redirect to home
    header("Location: /index.php");
    exit;
}

try {
    // Fetch the specific blog post and its author securely
    $stmt = $pdo->prepare("
        SELECT b.id, b.title, b.content, b.created_at, u.username as author 
        FROM blogPost b 
        JOIN user u ON b.user_id = u.id 
        WHERE b.id = :id
    ");
    $stmt->execute(['id' => $post_id]);
    $article = $stmt->fetch();
    
    // If article not found (deleted or invalid ID), redirect to home
    if (!$article) {
        header("Location: /index.php");
        exit;
    }
} catch (PDOException $e) {
    // Database error fallback
    header("Location: /index.php");
    exit;
}

// Format the date beautifully
$date = date('F j, Y', strtotime($article['created_at']));

// Fetch comments for this post
try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.content, c.created_at, c.user_id, u.username as author 
        FROM comment c 
        JOIN user u ON c.user_id = u.id 
        WHERE c.blog_post_id = :post_id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute(['post_id' => $post_id]);
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {
    $comments = [];
}

// Fetch Like Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM article_like WHERE blog_post_id = :post_id");
$stmt->execute(['post_id' => $post_id]);
$like_count = $stmt->fetchColumn();

$has_liked = false;
$has_bookmarked = false;

if (isset($_SESSION['user_id'])) {
    // Check if liked
    $stmt = $pdo->prepare("SELECT 1 FROM article_like WHERE blog_post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $_SESSION['user_id']]);
    $has_liked = (bool)$stmt->fetchColumn();
    
    // Check if bookmarked
    $stmt = $pdo->prepare("SELECT 1 FROM bookmark WHERE blog_post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $_SESSION['user_id']]);
    $has_bookmarked = (bool)$stmt->fetchColumn();
}

require_once 'includes/header.php';
?>

<main class="single-article-page bg-light" style="padding: 60px 0;">
    <div class="container">
        
        <!-- Reusing SwimSphere Design tokens for the article container -->
        <article class="article-full" style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-md); max-width: 900px; margin: 0 auto;">
            
            <div class="article-hero-image" style="width: 100%; height: 400px; background-color: var(--clr-ocean); display: flex; align-items: center; justify-content: center; position: relative;">
                <img src="/assets/images/placeholder.svg" alt="Cover Image" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(10,25,47,0.95), transparent);"></div>
                
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 50px;">
                    <h1 style="color: white; font-size: 3rem; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); line-height: 1.2;">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </h1>
                    <div class="article-meta" style="color: var(--clr-light-cyan); font-size: 1.1rem; display: flex; gap: 25px; font-weight: 500;">
                        <span style="display: flex; align-items: center; gap: 8px;">👤 By <?php echo htmlspecialchars($article['author']); ?></span>
                        <span style="display: flex; align-items: center; gap: 8px;">📅 Published on <?php echo $date; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="article-body" style="padding: 60px; font-size: 1.15rem; line-height: 1.8; color: var(--clr-text);">
                <?php 
                // Security check: Use htmlspecialchars FIRST to neutralize any injected HTML/scripts,
                // THEN use nl2br to convert genuine line breaks into <br> tags so paragraphs format nicely.
                echo nl2br(htmlspecialchars($article['content'])); 
                ?>
            </div>
            
            <div class="article-footer" style="padding: 30px 60px; background: #fbfbfb; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                
                <!-- Like & Bookmark Actions -->
                <div class="article-actions" style="display: flex; gap: 15px; align-items: center;">
                    
                    <!-- Likes -->
                    <div class="like-section" style="display: flex; align-items: center; gap: 10px;">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/like_handler.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                <input type="hidden" name="action" value="<?php echo $has_liked ? 'unlike' : 'like'; ?>">
                                <input type="hidden" name="redirect_to" value="/article.php?id=<?php echo $post_id; ?>">
                                <?php $title_attr = $has_liked ? 'Unlike' : 'Like'; ?>
                                <button type="submit" class="btn <?php echo $has_liked ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 8px 16px;" title="<?php echo $title_attr; ?>" aria-label="<?php echo $title_attr; ?>">
                                    ❤️ <?php echo $like_count; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="/auth/login.php" class="btn btn-outline" style="padding: 8px 16px;" title="Log in to like" aria-label="Log in to like">❤️ <?php echo $like_count; ?></a>
                        <?php endif; ?>
                    </div>

                    <!-- Bookmarks -->
                    <div class="bookmark-section">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/bookmark_handler.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                <input type="hidden" name="action" value="<?php echo $has_bookmarked ? 'remove' : 'add'; ?>">
                                <input type="hidden" name="redirect_to" value="/article.php?id=<?php echo $post_id; ?>">
                                <?php $title_attr = $has_bookmarked ? 'Remove Bookmark' : 'Bookmark'; ?>
                                <button type="submit" class="btn <?php echo $has_bookmarked ? 'btn-secondary' : 'btn-outline'; ?>" style="padding: 8px 16px;" title="<?php echo $title_attr; ?>" aria-label="<?php echo $title_attr; ?>">
                                    🔖
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="/auth/login.php" class="btn btn-outline" style="padding: 8px 16px;" title="Log in to bookmark" aria-label="Log in to bookmark">🔖</a>
                        <?php endif; ?>
                    </div>
                    
                </div>

                <a href="/index.php#articles" class="btn btn-outline" style="display: inline-block; font-size: 1.1rem;">← Back to All Articles</a>
            </div>

            <!-- Comments Section -->
            <div class="article-comments" style="padding: 40px 60px; background: white; border-top: 1px solid #eee;">
                <h3 style="font-size: 1.8rem; color: var(--clr-navy); margin-bottom: 30px;">Comments (<?php echo count($comments); ?>)</h3>

                <!-- Error/Success Messages -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #f5c6cb;">
                        <p style="margin: 0; font-weight: 500;">⚠️ <?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #c3e6cb;">
                        <p style="margin: 0; font-weight: 500;">✅ <?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <!-- Comment Form -->
                <div class="comment-form-container" style="margin-bottom: 40px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="/post_comment.php" method="POST" class="comment-form">
                            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
                            <div class="form-group" style="margin-bottom: 15px;">
                                <textarea name="content" class="form-control" placeholder="Share your thoughts..." style="width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; min-height: 100px; resize: vertical;" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt" style="background: var(--clr-light-cyan); padding: 20px; border-radius: var(--radius-sm); text-align: center;">
                            <p style="color: var(--clr-ocean); font-weight: 500; margin-bottom: 10px;">You must be logged in to post a comment.</p>
                            <a href="/auth/login.php" class="btn btn-primary">Log In to Comment</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Comments List -->
                <div class="comments-list" style="display: flex; flex-direction: column; gap: 20px;">
                    <?php if (empty($comments)): ?>
                        <p style="color: var(--clr-text-light); font-style: italic;">No comments yet. Be the first to share your thoughts!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment" style="background: #fbfbfb; padding: 20px; border-radius: var(--radius-sm); border-left: 3px solid var(--clr-aqua);">
                                <div class="comment-header" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span style="font-weight: 600; color: var(--clr-navy);">👤 <?php echo htmlspecialchars($comment['author']); ?></span>
                                    <span style="font-size: 0.85rem; color: var(--clr-text-light);"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-body" style="color: var(--clr-text); line-height: 1.6; margin-bottom: 10px;">
                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                </div>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                    <div class="comment-actions">
                                        <form action="/delete_comment.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your comment?');" style="margin: 0;">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                            <button type="submit" class="btn btn-outline" style="padding: 2px 8px; font-size: 0.8rem; color: #dc3545; border-color: transparent; background: transparent; cursor: pointer; text-decoration: underline;">Delete</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
        </article>
        
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
