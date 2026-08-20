<?php
require_once 'config/database.php';
$stmt = $pdo->query("SELECT id, title FROM blogPost LIMIT 1");
$post = $stmt->fetch();
$post_id = $post['id'];

echo "Testing Post ID: $post_id\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM article_like WHERE blog_post_id = $post_id");
$like_count = $stmt->fetchColumn();
echo "Initial Like Count: $like_count\n";

// Emulate clicking Like
$pdo->exec("INSERT IGNORE INTO article_like (user_id, blog_post_id) VALUES (1, $post_id)");
$stmt = $pdo->query("SELECT COUNT(*) FROM article_like WHERE blog_post_id = $post_id");
$like_count = $stmt->fetchColumn();
echo "Like Count After Liking: $like_count\n";

// Emulate clicking Unlike
$pdo->exec("DELETE FROM article_like WHERE user_id = 1 AND blog_post_id = $post_id");
$stmt = $pdo->query("SELECT COUNT(*) FROM article_like WHERE blog_post_id = $post_id");
$like_count = $stmt->fetchColumn();
echo "Like Count After Unliking: $like_count\n";
