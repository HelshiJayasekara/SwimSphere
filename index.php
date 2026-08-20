<?php 
require_once 'config/database.php';
require_once 'includes/header.php'; 
?>

<main>
    <!-- HERO SECTION -->
    <section class="hero" id="home" style="background: linear-gradient(to right, rgba(7, 26, 40, 0.95) 0%, rgba(7, 26, 40, 0.6) 40%, rgba(7, 26, 40, 0) 100%), url('/assets/images/hero_bg.png') no-repeat center right; background-size: cover; position: relative; overflow: hidden; padding: 120px 0; border-bottom: 1px solid var(--clr-border);">
        <div style="position: absolute; top: 50%; left: 30%; width: 500px; height: 500px; background: var(--clr-aqua); filter: blur(150px); opacity: 0.1; transform: translate(-50%, -50%); pointer-events: none;"></div>
        <div class="container hero-container" style="position: relative; z-index: 2;">
            <div class="hero-content" style="max-width: 550px;">
                <p style="color: var(--clr-aqua); font-weight: 600; letter-spacing: 2px; margin-bottom: 15px; font-size: 0.9rem;">IMPROVE. PERFORM. INSPIRE.</p>
                <h1 class="hero-title" style="font-family: 'Poppins', sans-serif; font-weight: 800; color: #F5F7FA; font-size: 4.5rem; line-height: 1.1; margin-bottom: 25px;">Dive Into<br>Better <span style="color: #08C4E9;">Swimming</span></h1>
                <p class="hero-subtitle" style="font-size: 1.15rem; margin-bottom: 35px; color: var(--clr-text-light); line-height: 1.7; max-width: 550px;">Explore swimming techniques, training advice, fitness tips and everything you need to become a better swimmer.</p>
                <div class="hero-buttons">
                    <a href="#articles" class="btn btn-primary btn-large" style="background: linear-gradient(135deg, #08C4E9, #057BD1); color: #FFFFFF; font-weight: 600; border-radius: 10px; box-shadow: 0 0 15px rgba(8, 196, 233, 0.35);">Explore Articles &rarr;</a>
                    <a href="#categories" class="btn btn-outline btn-large" style="background: transparent; border: 1px solid #08C4E9; color: #F5F7FA; font-weight: 600; border-radius: 10px;">Start Swimming</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED CATEGORIES SECTION -->
    <section class="categories" id="categories" style="background-color: var(--clr-bg); padding: 0 0 60px 0; margin-top: -30px; position: relative; z-index: 10;">
        <div class="container">
            <div class="categories-bar" style="display: flex; flex-wrap: wrap; justify-content: space-between; background: var(--clr-ocean); border: 1px solid var(--clr-border); border-radius: 16px; padding: 25px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
                <?php
                $categories = [
                    ['name' => 'Techniques', 'desc' => 'Improve your skills<br>step by step', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--clr-aqua)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M12 18v-6"></path><path d="M9 15h6"></path></svg>'],
                    ['name' => 'Training', 'desc' => 'Workouts and drills<br>for all levels', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--clr-aqua)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6h2v12h-2z"></path><path d="M4 6h2v12H4z"></path><path d="M6 12h12"></path><path d="M22 8h-2v8h2z"></path><path d="M2 8h2v8H2z"></path></svg>'],
                    ['name' => 'Fitness', 'desc' => 'Build strength, stamina<br>and endurance', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--clr-aqua)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path><path d="M12 12l-2-2"></path></svg>'],
                    ['name' => 'Safety', 'desc' => 'Stay safe in and around<br>the water', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--clr-aqua)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M12 8v8"></path><path d="M8 12h8"></path></svg>'],
                    ['name' => 'Equipment', 'desc' => 'Reviews and guides on<br>the best gear', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--clr-aqua)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="12" r="3"></circle><circle cx="17" cy="12" r="3"></circle><path d="M10 12h4"></path><path d="M21 12h1"></path><path d="M2 12h1"></path><path d="M17 9l1-4"></path><path d="M7 9L6 5"></path></svg>']
                ];

                $count = count($categories);
                $i = 0;
                foreach ($categories as $cat) {
                    $border = ($i < $count - 1) ? "border-right: 1px solid rgba(0, 194, 232, 0.15);" : "";
                    echo "
                    <div class='category-item' style='flex: 1; padding: 10px 25px; display: flex; align-items: flex-start; gap: 15px; {$border}'>
                        <div class='category-icon' style='margin-top: 5px;'>{$cat['icon']}</div>
                        <div>
                            <h3 style='font-size: 1.05rem; font-weight: 700; margin-bottom: 5px; color: var(--clr-white);'>{$cat['name']}</h3>
                            <p style='font-size: 0.85rem; color: var(--clr-text-light); line-height: 1.5;'>{$cat['desc']}</p>
                        </div>
                    </div>";
                    $i++;
                }
                ?>
            </div>
        </div>
    </section>

    <!-- LATEST ARTICLES SECTION -->
    <section class="articles section-padding bg-light" id="articles">
        <div class="container">
            <div class="section-header">
                <h2>Latest Articles</h2>
                <p>Stay updated with our most recent tips and guides.</p>
            </div>

            <div class="articles-grid">
                <?php
                // Fetch dynamic articles from database
                try {
                    $user_id_param = $_SESSION['user_id'] ?? 0;
                    $stmt = $pdo->prepare("
                        SELECT 
                            b.id, 
                            b.title, 
                            b.content, 
                            b.created_at, 
                            u.username as author,
                            (SELECT COUNT(*) FROM article_like WHERE blog_post_id = b.id) as like_count,
                            (SELECT COUNT(*) FROM bookmark WHERE blog_post_id = b.id) as bookmark_count,
                            (SELECT 1 FROM article_like WHERE blog_post_id = b.id AND user_id = :uid1 LIMIT 1) as has_liked,
                            (SELECT 1 FROM bookmark WHERE blog_post_id = b.id AND user_id = :uid2 LIMIT 1) as has_bookmarked
                        FROM blogPost b 
                        JOIN user u ON b.user_id = u.id 
                        ORDER BY b.created_at DESC
                    ");
                    $stmt->execute(['uid1' => $user_id_param, 'uid2' => $user_id_param]);
                    $articles = $stmt->fetchAll();
                    
                    if (empty($articles)) {
                        echo "<p style='grid-column: 1 / -1; text-align: center; color: var(--clr-text-light);'>No articles have been published yet.</p>";
                    } else {
                        foreach ($articles as $article) {
                            $excerpt = strip_tags($article['content']);
                            $excerpt = mb_strlen($excerpt) > 100 ? htmlspecialchars(mb_substr($excerpt, 0, 100)) . '...' : htmlspecialchars($excerpt);
                            $title = htmlspecialchars($article['title']);
                            $author = htmlspecialchars($article['author']);
                            $date = date('M j, Y', strtotime($article['created_at']));
                            $id = $article['id'];
                            $like_count = $article['like_count'];
                            $bookmark_count = $article['bookmark_count'];
                            $has_liked = (bool)$article['has_liked'];
                            $has_bookmarked = (bool)$article['has_bookmarked'];
                            
                            $is_logged_in = isset($_SESSION['user_id']);
                            
                            // Buttons HTML
                            $like_btn_class = $has_liked ? 'btn-primary' : 'btn-outline';
                            $like_html = "";
                            if ($is_logged_in) {
                                $action = $has_liked ? 'unlike' : 'like';
                                $title_attr = $has_liked ? 'Unlike' : 'Like';
                                $like_html = "<form action='/like_handler.php' method='POST' style='margin: 0; display: inline-block;'>
                                    <input type='hidden' name='post_id' value='{$id}'>
                                    <input type='hidden' name='action' value='{$action}'>
                                    <input type='hidden' name='redirect_to' value='/index.php#articles'>
                                    <button type='submit' class='btn {$like_btn_class}' style='padding: 6px 12px; font-size: 0.9rem;' title='{$title_attr}' aria-label='{$title_attr}'>❤️ {$like_count}</button>
                                </form>";
                            } else {
                                $like_html = "<a href='/auth/login.php' class='btn btn-outline' style='padding: 6px 12px; font-size: 0.9rem;' title='Log in to like' aria-label='Log in to like'>❤️ {$like_count}</a>";
                            }
                            
                            $bookmark_btn_class = $has_bookmarked ? 'btn-secondary' : 'btn-outline';
                            $bookmark_html = "";
                            if ($is_logged_in) {
                                $action = $has_bookmarked ? 'remove' : 'add';
                                $title_attr = $has_bookmarked ? 'Remove Bookmark' : 'Bookmark';
                                $bookmark_html = "<form action='/bookmark_handler.php' method='POST' style='margin: 0; display: inline-block;'>
                                    <input type='hidden' name='post_id' value='{$id}'>
                                    <input type='hidden' name='action' value='{$action}'>
                                    <input type='hidden' name='redirect_to' value='/index.php#articles'>
                                    <button type='submit' class='btn {$bookmark_btn_class}' style='padding: 6px 12px; font-size: 0.9rem;' title='{$title_attr}' aria-label='{$title_attr}'>🔖 {$bookmark_count}</button>
                                </form>";
                            } else {
                                $bookmark_html = "<a href='/auth/login.php' class='btn btn-outline' style='padding: 6px 12px; font-size: 0.9rem;' title='Log in to bookmark' aria-label='Log in to bookmark'>🔖 {$bookmark_count}</a>";
                            }

                            $img_num = ($id % 3) + 1;
                            echo "
                            <article class='article-card'>
                                <div class='article-image'>
                                    <img src='/assets/images/swimming_{$img_num}.png' alt='{$title} image'>
                                </div>
                                <div class='article-content'>
                                    <h3 class='article-title' style='margin-bottom: 10px;'>{$title}</h3>
                                    <div class='article-meta' style='margin-bottom: 15px; font-size: 0.85rem;'>
                                        <span class='article-author'>👤 {$author}</span>
                                        <span class='article-date'>📅 {$date}</span>
                                    </div>
                                    <p class='article-excerpt'>{$excerpt}</p>
                                    <div class='article-actions' style='display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;'>
                                        {$like_html}
                                        {$bookmark_html}
                                    </div>
                                    <a href='/article.php?id={$id}' class='btn btn-outline btn-block'>Read More</a>
                                </div>
                            </article>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<p style='grid-column: 1 / -1; text-align: center; color: #dc3545;'>Error loading articles.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about section-padding" id="about" style="background-color: var(--clr-bg);">
        <div class="container about-container">
            <div class="about-content">
                <h2>About SwimSphere</h2>
                <p>SwimSphere is your premier digital destination for all things swimming. We believe that swimming is not just a sport, but a vital life skill and a pathway to better health.</p>
                <p>Our blog provides high-quality, educational content tailored for swimmers of all levels. Whether you are looking to refine your <strong>swimming techniques</strong>, build <strong>training</strong> regimens, improve your <strong>fitness</strong>, learn about <strong>safety</strong>, or choose the right <strong>equipment</strong>, SwimSphere has you covered.</p>
            </div>
            <div class="about-image-wrapper">
                <img src="/assets/images/about_swimsphere.png" alt="About SwimSphere" class="about-image">
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION SECTION -->
    <section class="cta">
        <div class="container cta-container">
            <h2>Ready to Improve Your Swimming?</h2>
            <p>Join our community of swimmers and start reaching your goals today.</p>
            <a href="#articles" class="btn btn-secondary btn-large">Explore Swimming Tips</a>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
