<?php
// Secure session initialization
require_once 'includes/session.php';

// 1. Only authenticated users can access dashboard.php.
// If a user who is NOT logged in attempts to access, redirect them to /auth/login.php.
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

// Include the database connection
require_once 'config/database.php';

// If email isn't in session (e.g. they logged in before we updated login.php), safely fetch it.
$user_email = $_SESSION['email'] ?? '';
if (empty($user_email)) {
    $stmt = $pdo->prepare("SELECT email FROM user WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user_email = $stmt->fetchColumn();
    $_SESSION['email'] = $user_email;
}

// ==========================================
// FETCH USER'S POSTS for Activity and Blog Posts Feed
// ==========================================
$stmt = $pdo->prepare("SELECT id, title, content, created_at FROM blogPost WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$posts = $stmt->fetchAll();

// Fetch Total Comments Made
$stmt = $pdo->prepare("SELECT COUNT(*) FROM comment WHERE user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_comments = $stmt->fetchColumn();

// Fetch Total Likes Received (on their posts)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM article_like al JOIN blogPost bp ON al.blog_post_id = bp.id WHERE bp.user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_likes_received = $stmt->fetchColumn();

// Fetch Bookmarks
$stmt = $pdo->prepare("
    SELECT b.id as bookmark_id, bp.id as post_id, bp.title, b.created_at as bookmarked_at 
    FROM bookmark b 
    JOIN blogPost bp ON b.blog_post_id = bp.id 
    WHERE b.user_id = :user_id 
    ORDER BY b.created_at DESC
");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$bookmarks = $stmt->fetchAll();

// Fetch Total Likes Given
$stmt = $pdo->prepare("SELECT COUNT(*) FROM article_like WHERE user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_likes_given = $stmt->fetchColumn();

// Fetch Liked Articles
$stmt = $pdo->prepare("
    SELECT al.id as like_id, bp.id as post_id, bp.title, u.username as author, al.created_at as liked_at,
           (SELECT COUNT(*) FROM article_like WHERE blog_post_id = bp.id) as like_count
    FROM article_like al
    JOIN blogPost bp ON al.blog_post_id = bp.id
    JOIN user u ON bp.user_id = u.id
    WHERE al.user_id = :user_id 
    ORDER BY al.created_at DESC
");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$liked_articles = $stmt->fetchAll();

// Include the standard SwimSphere header
require_once 'includes/header.php';
?>

<main class="dashboard-page section-padding bg-light">
    <div class="container">
        
        <!-- SUCCESS MESSAGE -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #c3e6cb;">
                <p style="margin: 0; font-weight: 500;">✅ <?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <!-- ERROR MESSAGE -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #f5c6cb;">
                <p style="margin: 0; font-weight: 500;">⚠️ <?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- DASHBOARD HEADER -->
        <div class="dashboard-header" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 40px; gap: 20px;">
            <div>
                <!-- Display a welcoming dashboard using session info -->
                <h2 style="font-size: 2.2rem; color: var(--clr-white); margin-bottom: 5px;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p style="color: var(--clr-text-light); font-size: 1.1rem;">Manage your SwimSphere profile and blog posts.</p>
            </div>
            
            <div class="dashboard-actions" style="display: flex; gap: 15px;">
                <a href="/create.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem; line-height: 1;">+</span> Create New Post
                </a>
                <!-- Logout option -->
                <a href="/auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>

        <div class="dashboard-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
            
            <!-- SIDEBAR: Profile & Resources -->
            <aside class="dashboard-sidebar" style="display: flex; flex-direction: column; gap: 30px;">
                
                <!-- PROFILE SECTION -->
                <div class="profile-section" style="background: var(--clr-card); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.3rem; color: var(--clr-white); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">My Profile</h3>
                    
                    <div style="margin-bottom: 15px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--clr-text-light); margin-bottom: 5px;">Username</span>
                        <div style="font-weight: 600; color: var(--clr-white); font-size: 1.1rem;">@<?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--clr-text-light); margin-bottom: 5px;">Email Address</span>
                        <div style="font-weight: 600; color: var(--clr-white); font-size: 1.1rem; word-break: break-all;"><?php echo htmlspecialchars($user_email); ?></div>
                    </div>
                    
                    <div>
                        <span style="display: block; font-size: 0.9rem; color: var(--clr-text-light); margin-bottom: 5px;">Account Role</span>
                        <div style="display: inline-block; padding: 4px 10px; background: var(--clr-light-cyan); color: var(--clr-white); border-radius: 4px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">
                            <?php echo htmlspecialchars($_SESSION['role'] ?? 'User'); ?>
                        </div>
                    </div>
                </div>

                <!-- RESOURCES SECTION -->
                <div class="resources-section" style="background: var(--clr-card); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.3rem; color: var(--clr-white); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">Swimming Resources</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px;">
                        <li><a href="/#categories" style="display: flex; align-items: center; gap: 10px; font-weight: 500; color: var(--clr-white);">🏊‍♂️ Mastering the Freestyle</a></li>
                        <li><a href="/#articles" style="display: flex; align-items: center; gap: 10px; font-weight: 500; color: var(--clr-white);">⏱️ 4-Week Endurance Plan</a></li>
                        <li><a href="/#articles" style="display: flex; align-items: center; gap: 10px; font-weight: 500; color: var(--clr-white);">🥽 Best Goggles Reviews</a></li>
                        <li><a href="/#articles" style="display: flex; align-items: center; gap: 10px; font-weight: 500; color: var(--clr-white);">🛡️ Open Water Safety</a></li>
                    </ul>
                </div>
                
            </aside>

            <!-- MAIN CONTENT: Activity & Posts -->
            <div class="dashboard-main" style="display: flex; flex-direction: column; gap: 30px;">
                
                <!-- USER ACTIVITY SECTION -->
                <div class="activity-section" style="background: var(--clr-card); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.3rem; color: var(--clr-white); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">User Activity</h3>
                    
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: var(--clr-white); font-weight: 700; line-height: 1; margin-bottom: 5px;"><?php echo count($posts); ?></div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Total Posts</div>
                        </div>
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: var(--clr-aqua); font-weight: 700; line-height: 1; margin-bottom: 5px;"><?php echo $total_comments; ?></div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Comments</div>
                        </div>
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: #e83e8c; font-weight: 700; line-height: 1; margin-bottom: 5px;"><?php echo $total_likes_received; ?></div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Likes Received</div>
                        </div>
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: #fd7e14; font-weight: 700; line-height: 1; margin-bottom: 5px;"><?php echo $total_likes_given; ?></div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Likes Given</div>
                        </div>
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: var(--clr-white); font-weight: 700; line-height: 1; margin-bottom: 5px;"><?php echo count($bookmarks); ?></div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Bookmarks</div>
                        </div>
                    </div>
                </div>

                <!-- MY BLOG POSTS SECTION -->
                <section class="user-posts">
                    <h3 style="font-size: 1.5rem; color: var(--clr-white); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">My Blog Posts</h3>
                    
                    <?php if (empty($posts)): ?>
                        <div class="no-posts-state" style="background: var(--clr-card); padding: 50px 20px; text-align: center; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                            <div style="font-size: 3rem; margin-bottom: 20px; opacity: 0.8;">📝</div>
                            <h4 style="font-size: 1.3rem; color: var(--clr-white); margin-bottom: 10px;">You haven't created any posts yet.</h4>
                            <p style="color: var(--clr-text-light); margin-bottom: 25px; max-width: 400px; margin-inline: auto;">
                                Share your swimming techniques, training routines, or equipment reviews with the SwimSphere community!
                            </p>
                            <a href="/create.php" class="btn btn-primary">Start Writing Now</a>
                        </div>
                        
                    <?php else: ?>
                        <!-- Display posts safely -->
                        <div class="posts-list" style="display: flex; flex-direction: column; gap: 20px;">
                            <?php foreach ($posts as $post): ?>
                                <article class="post-card" style="background: var(--clr-card); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; border-left: 4px solid var(--clr-aqua);">
                                    
                                    <div class="post-content" style="padding: 20px 25px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                            <h4 style="font-size: 1.25rem; color: var(--clr-white); line-height: 1.3; margin: 0;">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </h4>
                                            <div class="post-meta" style="font-size: 0.8rem; color: var(--clr-text-light); font-weight: 500; white-space: nowrap; margin-left: 15px;">
                                                <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                            </div>
                                        </div>
                                        
                                        <p style="color: var(--clr-text); font-size: 0.95rem; margin-bottom: 15px; line-height: 1.5;">
                                            <?php 
                                            // Strip tags and enforce maximum length for the excerpt
                                            $excerpt = strip_tags($post['content']);
                                            if (mb_strlen($excerpt) > 150) {
                                                echo htmlspecialchars(mb_substr($excerpt, 0, 150)) . '...';
                                            } else {
                                                echo htmlspecialchars($excerpt);
                                            }
                                            ?>
                                        </p>

                                        <div class="post-actions" style="display: flex; gap: 10px; align-items: center;">
                                            <a href="/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline" style="padding: 4px 12px; font-size: 0.85rem;">Edit</a>
                                            <form action="/delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');" style="margin: 0;">
                                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" class="btn btn-outline" style="padding: 4px 12px; font-size: 0.85rem; color: #dc3545; border-color: #dc3545; background: transparent; cursor: pointer;" onmouseover="this.style.backgroundColor='#dc3545'; this.style.color='white';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#dc3545';">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                </section>
                
                <!-- MY BOOKMARKS SECTION -->
                <section class="user-bookmarks">
                    <h3 style="font-size: 1.5rem; color: var(--clr-white); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">My Bookmarks</h3>
                    
                    <?php if (empty($bookmarks)): ?>
                        <div class="no-posts-state" style="background: var(--clr-card); padding: 40px 20px; text-align: center; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                            <div style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.8;">🔖</div>
                            <h4 style="font-size: 1.2rem; color: var(--clr-white); margin-bottom: 10px;">No bookmarked articles.</h4>
                            <p style="color: var(--clr-text-light); margin-bottom: 0;">Explore the home page and bookmark articles you want to read later.</p>
                        </div>
                    <?php else: ?>
                        <div class="posts-list" style="display: flex; flex-direction: column; gap: 15px;">
                            <?php foreach ($bookmarks as $bkmk): ?>
                                <article class="post-card" style="background: var(--clr-card); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; border-left: 4px solid var(--clr-ocean);">
                                    <div class="post-content" style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                        
                                        <div>
                                            <h4 style="font-size: 1.1rem; color: var(--clr-white); margin: 0 0 5px 0;">
                                                <?php echo htmlspecialchars($bkmk['title']); ?>
                                            </h4>
                                            <div class="post-meta" style="font-size: 0.8rem; color: var(--clr-text-light); font-weight: 500;">
                                                Bookmarked on <?php echo date('M j, Y', strtotime($bkmk['bookmarked_at'])); ?>
                                            </div>
                                        </div>

                                        <div class="post-actions" style="display: flex; gap: 10px; align-items: center;">
                                            <a href="/article.php?id=<?php echo $bkmk['post_id']; ?>" class="btn btn-outline" style="padding: 4px 12px; font-size: 0.85rem;">Read</a>
                                            <form action="/bookmark_handler.php" method="POST" style="margin: 0;">
                                                <input type="hidden" name="post_id" value="<?php echo $bkmk['post_id']; ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="redirect_to" value="/dashboard.php">
                                                <button type="submit" class="btn btn-secondary" style="padding: 4px 12px; font-size: 0.85rem; cursor: pointer;" title="Remove Bookmark" aria-label="Remove Bookmark">🔖</button>
                                            </form>
                                        </div>
                                        
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- MY LIKED ARTICLES SECTION -->
                <section class="user-liked" id="liked">
                    <h3 style="font-size: 1.5rem; color: var(--clr-white); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">My Liked Articles</h3>
                    
                    <?php if (empty($liked_articles)): ?>
                        <div class="no-posts-state" style="background: var(--clr-card); padding: 40px 20px; text-align: center; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                            <div style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.8;">❤️</div>
                            <h4 style="font-size: 1.2rem; color: var(--clr-white); margin-bottom: 10px;">No liked articles.</h4>
                            <p style="color: var(--clr-text-light); margin-bottom: 0;">Explore the home page and like articles you enjoy.</p>
                        </div>
                    <?php else: ?>
                        <div class="posts-list" style="display: flex; flex-direction: column; gap: 15px;">
                            <?php foreach ($liked_articles as $liked): ?>
                                <article class="post-card" style="background: var(--clr-card); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; border-left: 4px solid #e83e8c;">
                                    <div class="post-content" style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                        
                                        <div>
                                            <h4 style="font-size: 1.1rem; color: var(--clr-white); margin: 0 0 5px 0;">
                                                <?php echo htmlspecialchars($liked['title']); ?>
                                            </h4>
                                            <div class="post-meta" style="font-size: 0.8rem; color: var(--clr-text-light); font-weight: 500;">
                                                👤 <?php echo htmlspecialchars($liked['author']); ?> &middot; Liked on <?php echo date('M j, Y', strtotime($liked['liked_at'])); ?> &middot; ❤️ <?php echo $liked['like_count']; ?>
                                            </div>
                                        </div>

                                        <div class="post-actions" style="display: flex; gap: 10px; align-items: center;">
                                            <a href="/article.php?id=<?php echo $liked['post_id']; ?>" class="btn btn-outline" style="padding: 4px 12px; font-size: 0.85rem;">Read Article</a>
                                            <form action="/like_handler.php" method="POST" style="margin: 0;">
                                                <input type="hidden" name="post_id" value="<?php echo $liked['post_id']; ?>">
                                                <input type="hidden" name="action" value="unlike">
                                                <input type="hidden" name="redirect_to" value="/dashboard.php#liked">
                                                <button type="submit" class="btn btn-primary" style="padding: 4px 12px; font-size: 0.85rem; cursor: pointer;" title="Unlike" aria-label="Unlike">❤️ <?php echo $liked['like_count']; ?></button>
                                            </form>
                                        </div>
                                        
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                
            </div>
            
        </div>
    </div>
</main>

<style>
/* Dashboard responsiveness inline rules to keep CSS self-contained */
@media (max-width: 900px) {
    .dashboard-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
