<?php
require_once 'config/database.php';
$user_id_param = 1; // Assuming testing with a valid user
$stmt = $pdo->prepare("
    SELECT 
        b.id, 
        (SELECT COUNT(*) FROM article_like WHERE blog_post_id = b.id) as like_count,
        (SELECT COUNT(*) FROM bookmark WHERE blog_post_id = b.id) as bookmark_count,
        (SELECT 1 FROM article_like WHERE blog_post_id = b.id AND user_id = :uid1 LIMIT 1) as has_liked,
        (SELECT 1 FROM bookmark WHERE blog_post_id = b.id AND user_id = :uid2 LIMIT 1) as has_bookmarked
    FROM blogPost b 
    WHERE b.id = 7
");
$stmt->execute(['uid1' => $user_id_param, 'uid2' => $user_id_param]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\$id = " . var_export($article['id'], true) . "\n";
echo "\$like_count = " . var_export($article['like_count'], true) . "\n";
echo "\$bookmark_count = " . var_export($article['bookmark_count'], true) . "\n";
echo "\$has_liked = " . var_export($article['has_liked'], true) . "\n";
echo "\$has_bookmarked = " . var_export($article['has_bookmarked'], true) . "\n";
