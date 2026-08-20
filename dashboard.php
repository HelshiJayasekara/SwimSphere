<?php
// Start the session to access user authentication data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
                <h2 style="font-size: 2.2rem; color: var(--clr-navy); margin-bottom: 5px;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
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
                <div class="profile-section" style="background: white; border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.3rem; color: var(--clr-ocean); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">My Profile</h3>
                    
                    <div style="margin-bottom: 15px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--clr-text-light); margin-bottom: 5px;">Username</span>
                        <div style="font-weight: 600; color: var(--clr-navy); font-size: 1.1rem;">@<?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--clr-text-light); margin-bottom: 5px;">Email Address</span>
                        <div style="font-weight: 600; color: var(--clr-navy); font-size: 1.1rem; word-break: break-all;"><?php echo htmlspecialchars($user_email); ?></div>
                    </div>
                    
                    <div>
                        <span style="display: block; font-size: 0.9rem; color: var(--clr-text-light); margin-bottom: 5px;">Account Role</span>
                        <div style="display: inline-block; padding: 4px 10px; background: var(--clr-light-cyan); color: var(--clr-ocean); border-radius: 4px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">
                            <?php echo htmlspecialchars($_SESSION['role'] ?? 'User'); ?>
                        </div>
                    </div>
                </div>

                <!-- RESOURCES SECTION -->
                <div class="resources-section" style="background: white; border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.3rem; color: var(--clr-ocean); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">Swimming Resources</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px;">
                        <li><a href="/#categories" style="display: flex; align-items: center; gap: 10px; font-weight: 500;">🏊‍♂️ Mastering the Freestyle</a></li>
                        <li><a href="/#articles" style="display: flex; align-items: center; gap: 10px; font-weight: 500;">⏱️ 4-Week Endurance Plan</a></li>
                        <li><a href="/#articles" style="display: flex; align-items: center; gap: 10px; font-weight: 500;">🥽 Best Goggles Reviews</a></li>
                        <li><a href="/#articles" style="display: flex; align-items: center; gap: 10px; font-weight: 500;">🛡️ Open Water Safety</a></li>
                    </ul>
                </div>
                
            </aside>

            <!-- MAIN CONTENT: Activity & Posts -->
            <div class="dashboard-main" style="display: flex; flex-direction: column; gap: 30px;">
                
                <!-- USER ACTIVITY SECTION -->
                <div class="activity-section" style="background: white; border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.3rem; color: var(--clr-ocean); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">User Activity</h3>
                    
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: var(--clr-navy); font-weight: 700; line-height: 1; margin-bottom: 5px;"><?php echo count($posts); ?></div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Total Posts</div>
                        </div>
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: var(--clr-aqua); font-weight: 700; line-height: 1; margin-bottom: 5px;">0</div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Comments</div>
                        </div>
                        <div style="flex: 1; min-width: 150px; background: var(--clr-bg); padding: 20px; border-radius: var(--radius-sm); text-align: center; border: 1px solid #eee;">
                            <div style="font-size: 2.5rem; color: var(--clr-ocean); font-weight: 700; line-height: 1; margin-bottom: 5px;">Active</div>
                            <div style="color: var(--clr-text-light); font-size: 0.9rem; text-transform: uppercase; font-weight: 600;">Account Status</div>
                        </div>
                    </div>
                </div>

                <!-- MY BLOG POSTS SECTION -->
                <section class="user-posts">
                    <h3 style="font-size: 1.5rem; color: var(--clr-ocean); margin-bottom: 20px; border-bottom: 2px solid var(--clr-aqua); padding-bottom: 10px; display: inline-block;">My Blog Posts</h3>
                    
                    <?php if (empty($posts)): ?>
                        <div class="no-posts-state" style="background: white; padding: 50px 20px; text-align: center; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                            <div style="font-size: 3rem; margin-bottom: 20px; opacity: 0.8;">📝</div>
                            <h4 style="font-size: 1.3rem; color: var(--clr-navy); margin-bottom: 10px;">You haven't created any posts yet.</h4>
                            <p style="color: var(--clr-text-light); margin-bottom: 25px; max-width: 400px; margin-inline: auto;">
                                Share your swimming techniques, training routines, or equipment reviews with the SwimSphere community!
                            </p>
                            <a href="/create.php" class="btn btn-primary">Start Writing Now</a>
                        </div>
                        
                    <?php else: ?>
                        <!-- Display posts safely -->
                        <div class="posts-list" style="display: flex; flex-direction: column; gap: 20px;">
                            <?php foreach ($posts as $post): ?>
                                <article class="post-card" style="background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; border-left: 4px solid var(--clr-aqua);">
                                    
                                    <div class="post-content" style="padding: 20px 25px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                            <h4 style="font-size: 1.25rem; color: var(--clr-navy); line-height: 1.3; margin: 0;">
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
