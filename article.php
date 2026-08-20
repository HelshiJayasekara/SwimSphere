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
            
            <div class="article-footer" style="padding: 30px 60px; background: #fbfbfb; border-top: 1px solid #eee; text-align: center;">
                <a href="/index.php#articles" class="btn btn-outline" style="display: inline-block; font-size: 1.1rem;">← Back to All Articles</a>
            </div>
            
        </article>
        
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
