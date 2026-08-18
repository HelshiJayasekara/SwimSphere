<?php require_once 'includes/header.php'; ?>

<main>
    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="container hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Dive Into Better Swimming</h1>
                <p class="hero-subtitle">Explore swimming techniques, training advice, fitness tips and everything you need to become a better swimmer.</p>
                <div class="hero-buttons">
                    <a href="#articles" class="btn btn-primary btn-large">Explore Articles</a>
                    <a href="#categories" class="btn btn-outline btn-large">Start Swimming</a>
                </div>
            </div>
            <div class="hero-image-wrapper">
                <img src="assets/images/placeholder.svg" alt="Swimmer underwater placeholder" class="hero-image">
            </div>
        </div>
    </section>

    <!-- FEATURED CATEGORIES SECTION -->
    <section class="categories section-padding" id="categories">
        <div class="container">
            <div class="section-header">
                <h2>Featured Categories</h2>
                <p>Find the exact resources you need based on your swimming goals.</p>
            </div>
            
            <div class="categories-grid">
                <?php
                $categories = [
                    ['name' => 'Freestyle', 'desc' => 'Master the most popular and fastest swimming stroke.', 'icon' => '🏊‍♂️'],
                    ['name' => 'Breaststroke', 'desc' => 'Improve your timing and technique for breaststroke.', 'icon' => '🐸'],
                    ['name' => 'Backstroke', 'desc' => 'Tips for floating, kicking, and pulling in backstroke.', 'icon' => '🔄'],
                    ['name' => 'Butterfly', 'desc' => 'Conquer the most challenging and powerful stroke.', 'icon' => '🦋'],
                    ['name' => 'Training', 'desc' => 'Workouts and drills to build speed and stamina.', 'icon' => '⏱️'],
                    ['name' => 'Fitness', 'desc' => 'Swimming for weight loss, toning, and cardiovascular health.', 'icon' => '💪'],
                    ['name' => 'Safety', 'desc' => 'Crucial safety rules for pool and open water swimming.', 'icon' => '🛟'],
                    ['name' => 'Equipment', 'desc' => 'Reviews and guides on the best swimming gear.', 'icon' => '🥽']
                ];

                foreach ($categories as $cat) {
                    echo "
                    <div class='category-card'>
                        <div class='category-icon'>{$cat['icon']}</div>
                        <h3>{$cat['name']}</h3>
                        <p>{$cat['desc']}</p>
                    </div>";
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
                $articles = [
                    [
                        'title' => '5 Essential Freestyle Swimming Techniques',
                        'category' => 'Freestyle',
                        'excerpt' => 'Learn the core principles of freestyle swimming to improve your speed and efficiency in the water.',
                        'author' => 'Sarah Connor',
                        'date' => 'Oct 12, 2026'
                    ],
                    [
                        'title' => 'How to Improve Your Swimming Endurance',
                        'category' => 'Training',
                        'excerpt' => 'Discover the best workouts and breathing techniques to swim longer distances without getting tired.',
                        'author' => 'Mike Phelps',
                        'date' => 'Oct 10, 2026'
                    ],
                    [
                        'title' => 'A Beginner\'s Guide to Breaststroke',
                        'category' => 'Breaststroke',
                        'excerpt' => 'Break down the complex movements of breaststroke into simple, actionable steps for beginners.',
                        'author' => 'Emma Splash',
                        'date' => 'Oct 08, 2026'
                    ],
                    [
                        'title' => 'Common Swimming Mistakes Beginners Make',
                        'category' => 'Techniques',
                        'excerpt' => 'Avoid these common pitfalls to fast-track your swimming progress and prevent injuries.',
                        'author' => 'John Ocean',
                        'date' => 'Oct 05, 2026'
                    ],
                    [
                        'title' => 'Essential Swimming Safety Rules',
                        'category' => 'Safety',
                        'excerpt' => 'Whether you are in a pool or open water, these safety rules are non-negotiable for every swimmer.',
                        'author' => 'Sarah Connor',
                        'date' => 'Oct 02, 2026'
                    ],
                    [
                        'title' => 'Choosing the Right Swimming Equipment',
                        'category' => 'Equipment',
                        'excerpt' => 'From goggles to fins, find out what gear you actually need and what you can skip.',
                        'author' => 'Mike Phelps',
                        'date' => 'Sep 30, 2026'
                    ]
                ];

                foreach ($articles as $article) {
                    echo "
                    <article class='article-card'>
                        <div class='article-image'>
                            <img src='assets/images/placeholder.svg' alt='{$article['title']} placeholder'>
                            <span class='article-badge'>{$article['category']}</span>
                        </div>
                        <div class='article-content'>
                            <h3 class='article-title'>{$article['title']}</h3>
                            <p class='article-excerpt'>{$article['excerpt']}</p>
                            <div class='article-meta'>
                                <span class='article-author'>👤 {$article['author']}</span>
                                <span class='article-date'>📅 {$article['date']}</span>
                            </div>
                            <a href='#' class='btn btn-outline btn-block'>Read More</a>
                        </div>
                    </article>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about section-padding" id="about">
        <div class="container about-container">
            <div class="about-content">
                <h2>About SwimSphere</h2>
                <p>SwimSphere is your premier digital destination for all things swimming. We believe that swimming is not just a sport, but a vital life skill and a pathway to better health.</p>
                <p>Our blog provides high-quality, educational content tailored for swimmers of all levels. Whether you are looking to refine your <strong>swimming techniques</strong>, build <strong>training</strong> regimens, improve your <strong>fitness</strong>, learn about <strong>safety</strong>, or choose the right <strong>equipment</strong>, SwimSphere has you covered.</p>
                <div class="about-stats">
                    <div class="stat-item">
                        <span class="stat-number">100+</span>
                        <span class="stat-label">Articles</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">10k</span>
                        <span class="stat-label">Readers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">4</span>
                        <span class="stat-label">Strokes</span>
                    </div>
                </div>
            </div>
            <div class="about-image-wrapper">
                <img src="assets/images/placeholder.svg" alt="About SwimSphere placeholder" class="about-image">
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
