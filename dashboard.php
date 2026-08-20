<?php
// Start the session to access user authentication data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// AUTHENTICATION CHECK
// ==========================================
// 1. Only authenticated users can access dashboard.php.
// 2. If a user who is NOT logged in attempts to access, redirect to login.
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

// Include the database connection
require_once 'config/database.php';

// ==========================================
// FETCH USER'S POSTS
// ==========================================
// 14. Do not trust user IDs supplied through GET or POST. 
//     Always use the authenticated user's ID from $_SESSION.
// 7. Retrieve only the blog posts belonging to the currently logged-in user.
$stmt = $pdo->prepare("SELECT id, title, content, created_at FROM blogPost WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$posts = $stmt->fetchAll();

// Include the standard SwimSphere header
require_once 'includes/header.php';
?>

<main class="dashboard-page section-padding bg-light">
    <div class="container">
        
        <!-- DASHBOARD HEADER -->
        <div class="dashboard-header" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 40px; gap: 20px;">
            <div>
                <!-- 4. Display a welcoming dashboard -->
                <h2 style="font-size: 2.2rem; color: var(--clr-navy); margin-bottom: 5px;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <!-- 5. Display the logged-in user's information -->
                <p style="color: var(--clr-text-light); font-size: 1.1rem;">Manage your SwimSphere blog posts and account.</p>
            </div>
            
            <div class="dashboard-actions" style="display: flex; gap: 15px;">
                <!-- 11. Add a prominent "Create New Post" button. -->
                <a href="#" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem; line-height: 1;">+</span> Create New Post
                </a>
                <!-- 12. Add a Logout option. -->
                <a href="/auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>

        <!-- MY BLOG POSTS SECTION -->
        <section class="user-posts">
            <!-- 6. The dashboard should contain a section called: "My Blog Posts" -->
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 1.5rem; color: var(--clr-ocean); display: inline-block; position: relative;">
                    My Blog Posts
                    <div style="position: absolute; bottom: -8px; left: 0; width: 40px; height: 3px; background: var(--clr-aqua); border-radius: 2px;"></div>
                </h3>
            </div>
            
            <?php if (empty($posts)): ?>
                <!-- 10. If the user has no posts, display a friendly message -->
                <div class="no-posts-state" style="background: white; padding: 60px 20px; text-align: center; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                    <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.8;">📝</div>
                    <h4 style="font-size: 1.4rem; color: var(--clr-navy); margin-bottom: 10px;">You haven't created any posts yet.</h4>
                    <p style="color: var(--clr-text-light); margin-bottom: 25px; max-width: 500px; margin-inline: auto;">
                        Share your swimming techniques, training routines, or equipment reviews with the SwimSphere community!
                    </p>
                    <a href="#" class="btn btn-primary">Start Writing Now</a>
                </div>
                
            <?php else: ?>
                <!-- 8. Display the user's posts in a clean responsive layout. -->
                <div class="posts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px;">
                    
                    <?php foreach ($posts as $post): ?>
                        <!-- 9. Each post should display: title, excerpt, date, Edit, Delete -->
                        <article class="post-card" style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            
                            <div class="post-content" style="padding: 25px; flex-grow: 1;">
                                <h4 style="font-size: 1.3rem; color: var(--clr-navy); margin-bottom: 12px; line-height: 1.3;">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </h4>
                                
                                <div class="post-meta" style="font-size: 0.85rem; color: var(--clr-text-light); margin-bottom: 15px; font-weight: 500;">
                                    📅 Created on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                </div>
                                
                                <p style="color: var(--clr-text); font-size: 0.95rem; margin-bottom: 0; line-height: 1.6;">
                                    <?php 
                                    // Generate a clean excerpt by stripping HTML tags and limiting length
                                    $excerpt = strip_tags($post['content']);
                                    if (mb_strlen($excerpt) > 120) {
                                        echo htmlspecialchars(mb_substr($excerpt, 0, 120)) . '...';
                                    } else {
                                        echo htmlspecialchars($excerpt);
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <div class="post-actions" style="padding: 15px 25px; background: #fbfbfb; border-top: 1px solid #f0f0f0; display: flex; gap: 12px;">
                                <!-- Edit Button -->
                                <a href="#" class="btn btn-outline" style="padding: 8px 15px; font-size: 0.9rem; flex: 1; text-align: center;">Edit</a>
                                
                                <!-- Delete Button (using form for secure POST deletion in the future) -->
                                <!-- For now, it's just a button styled as requested -->
                                <a href="#" class="btn btn-outline" style="padding: 8px 15px; font-size: 0.9rem; flex: 1; text-align: center; color: #dc3545; border-color: #dc3545;" onmouseover="this.style.backgroundColor='#dc3545'; this.style.color='white';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#dc3545';">Delete</a>
                            </div>
                            
                        </article>
                    <?php endforeach; ?>
                    
                </div>
            <?php endif; ?>
            
        </section>
        
    </div>
</main>

<?php 
// Include the standard SwimSphere footer
require_once 'includes/footer.php'; 
?>
