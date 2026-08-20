#!/bin/bash
rm -f cookies.txt

echo "--- 1. Login ---"
curl -s -c cookies.txt -b cookies.txt -X POST -d "email=comment99@test.com&password=pass" http://localhost:8080/auth/login.php > /dev/null
POST_ID=$(/Applications/XAMPP/xamppfiles/bin/mysql -u root swimsphere -e "SELECT id FROM blogPost WHERE title='Comment Test Post 99' ORDER BY id DESC LIMIT 1;" -s)

echo "--- Clear Previous test data ---"
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=unlike&redirect_to=/index.php" http://localhost:8080/like_handler.php > /dev/null
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=remove&redirect_to=/index.php" http://localhost:8080/bookmark_handler.php > /dev/null

echo "--- TEST 1: Home -> Like article ---"
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=like&redirect_to=/index.php" http://localhost:8080/like_handler.php > /dev/null

echo "--- TEST 2: Home -> Like count changes ---"
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/index.php" | grep -o "❤️ Liked &middot; 1" | head -n 1

echo "--- TEST 3: Open article -> Like state is correct ---"
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/article.php?id=$POST_ID" | grep -o "❤️ Liked &middot; 1" | head -n 1

echo "--- TEST 4: Dashboard -> article appears under My Liked Articles ---"
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/dashboard.php" | grep -o "My Liked Articles" > /dev/null && echo "Found Liked Articles section"
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/dashboard.php" | grep -A 20 "My Liked Articles" | grep -o "Comment Test Post 99" | head -n 1

echo "--- TEST 5: Home -> Bookmark article ---"
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=add&redirect_to=/index.php" http://localhost:8080/bookmark_handler.php > /dev/null

echo "--- TEST 6: Dashboard -> article appears under My Bookmarks ---"
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/dashboard.php" | grep -A 20 "My Bookmarks" | grep -o "Comment Test Post 99" | head -n 1

echo "--- TEST 7: Dashboard -> Remove Bookmark ---"
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=remove&redirect_to=/dashboard.php" http://localhost:8080/bookmark_handler.php > /dev/null

echo "--- TEST 8: Return to Home -> bookmark is removed ---"
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/index.php" | grep -o "🔖 Save" | head -n 1

echo "--- TEST 9: Logout -> Like/Bookmark buttons require login ---"
curl -s -c cookies.txt -b cookies.txt http://localhost:8080/auth/logout.php > /dev/null
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/index.php" | grep -o "title='Log in to like'" | head -n 1
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/index.php" | grep -o "title='Log in to bookmark'" | head -n 1

echo "--- TEST 10 & 11: Attempting duplicate inserts in DB directly via curl ---"
# login again
curl -s -c cookies.txt -b cookies.txt -X POST -d "email=comment99@test.com&password=pass" http://localhost:8080/auth/login.php > /dev/null
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=like&redirect_to=/index.php" http://localhost:8080/like_handler.php > /dev/null
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=like&redirect_to=/index.php" http://localhost:8080/like_handler.php > /dev/null
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=add&redirect_to=/index.php" http://localhost:8080/bookmark_handler.php > /dev/null
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=add&redirect_to=/index.php" http://localhost:8080/bookmark_handler.php > /dev/null

/Applications/XAMPP/xamppfiles/bin/mysql -u root swimsphere -e "SELECT COUNT(*) FROM article_like WHERE blog_post_id=$POST_ID;" -s
/Applications/XAMPP/xamppfiles/bin/mysql -u root swimsphere -e "SELECT COUNT(*) FROM bookmark WHERE blog_post_id=$POST_ID;" -s

echo "--- Done testing ---"
