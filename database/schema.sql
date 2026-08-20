-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS swimsphere;

-- Select the database
USE swimsphere;

-- ==========================================
-- TABLE: user
-- ==========================================
-- Stores registered users and their authentication credentials.
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    -- 255 characters is the recommended length to safely store PHP's password_hash() output.
    password VARCHAR(255) NOT NULL,
    -- Role defaults to 'user' but can be elevated to 'admin'.
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLE: blogPost
-- ==========================================
-- Stores the blog content created by users.
CREATE TABLE IF NOT EXISTS blogPost (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    -- Automatically records the exact time the row is inserted.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Automatically updates to the current time whenever the row is modified.
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key establishing a One-To-Many relationship (One User -> Many Posts).
    -- ON DELETE CASCADE ensures that if a user is deleted, all their posts are automatically removed.
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLE: article_like
-- ==========================================
CREATE TABLE IF NOT EXISTS article_like (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  blog_post_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_post_like (user_id, blog_post_id),
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (blog_post_id) REFERENCES blogPost(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLE: bookmark
-- ==========================================
CREATE TABLE IF NOT EXISTS bookmark (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  blog_post_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_post_bookmark (user_id, blog_post_id),
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (blog_post_id) REFERENCES blogPost(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
