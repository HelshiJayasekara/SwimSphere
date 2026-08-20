#!/bin/bash
rm -f cookies.txt
curl -s -c cookies.txt -b cookies.txt -X POST -d "email=comment99@test.com&password=pass" http://localhost:8080/auth/login.php > /dev/null
POST_ID=$(/Applications/XAMPP/xamppfiles/bin/mysql -u root swimsphere -e "SELECT id FROM blogPost ORDER BY id DESC LIMIT 1;" -s)

echo "AJAX Like"
curl -s -c cookies.txt -b cookies.txt -X POST -d "post_id=$POST_ID&action=like&ajax=1" http://localhost:8080/like_handler.php

echo -e "\n\nChecking index.php"
curl -s -c cookies.txt -b cookies.txt "http://localhost:8080/index.php" | grep "❤️" | head -n 3
