<?php
// Include the database connection
require_once '../config/database.php';

// Initialize variables for form preservation and messages
$username = '';
$email = '';
$errors = [];
$successMessage = '';

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Retrieve and trim user inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Server-side Validation
    
    // Validate Username
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    
    // Validate Email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email format.";
    }
    
    // Validate Password
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    // Validate Password Confirmation
    if (empty($confirm_password)) {
        $errors[] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // 3. Database Validation (Check for unique username and email)
    if (empty($errors)) {
        // Check if username already exists using a prepared statement
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $errors[] = "That username is already taken. Please choose another.";
        }

        // Check if email already exists using a prepared statement
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "That email address is already registered.";
        }
    }

    // 4. Insert New User
    if (empty($errors)) {
        // Securely hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the INSERT statement. Note we do NOT specify the role, allowing it to default to 'user'
        $stmt = $pdo->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
        
        try {
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password
            ]);
            
            // Set success message
            $successMessage = "Registration successful! Welcome to SwimSphere.";
            
            // Clear form data on success
            $username = '';
            $email = '';
        } catch (PDOException $e) {
            $errors[] = "A database error occurred during registration. Please try again later.";
        }
    }
}

// Include the frontend header UI
require_once '../includes/header.php';
?>

<main class="auth-page section-padding bg-light">
    <div class="container">
        <div class="auth-card" style="max-width: 500px; margin: 0 auto; padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
            
            <div class="section-header" style="margin-bottom: 30px;">
                <h2 style="font-size: 2rem;">Join SwimSphere</h2>
                <p>Create an account to join our swimming community.</p>
            </div>

            <!-- Display Success Message -->
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.05);">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                            <path d="M22 11.08V12a10 10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <p style="margin: 0; font-weight: 500; font-size: 1.05rem;"><?php echo htmlspecialchars($successMessage); ?></p>
                    </div>
                    <a href="/auth/login.php" class="btn btn-primary btn-block" style="text-align: center;">Go to Login</a>
                </div>
            <?php else: ?>

                <!-- Display Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ff6b6b; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.05);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <ul style="list-style: none; padding: 0; margin: 0; font-weight: 500; font-size: 0.95rem; line-height: 1.5;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form action="/auth/register.php" method="POST" class="auth-form">
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="username" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-white);">Username</label>
                        <!-- htmlspecialchars is critical here to prevent XSS attacks when preserving user input -->
                        <input type="text" id="username" name="username" class="form-control" style="width: 100%; padding: 12px; border-radius: 5px; font-family: inherit;" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-white);">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 12px; border-radius: 5px; font-family: inherit;" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="password" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-white);">Password</label>
                        <input type="password" id="password" name="password" class="form-control" style="width: 100%; padding: 12px; border-radius: 5px; font-family: inherit;" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label for="confirm_password" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-white);">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" style="width: 100%; padding: 12px; border-radius: 5px; font-family: inherit;" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-large">Create Account</button>
                </form>

                <div class="auth-links" style="text-align: center; margin-top: 25px;">
                    <p style="color: var(--clr-text-light);">Already have an account? <a href="/auth/login.php" style="font-weight: 600;">Log In</a></p>
                </div>
                
            <?php endif; ?>
        </div>
    </div>
</main>

<?php 
// Include the frontend footer UI
require_once '../includes/footer.php'; 
?>
