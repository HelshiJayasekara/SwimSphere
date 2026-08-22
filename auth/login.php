<?php
// Secure session initialization
require_once '../includes/session.php';

// Include the database connection
require_once '../config/database.php';

// Initialize variables
$email = '';
$error = '';

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Retrieve and trim user inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2. Server-side Validation
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // 3. Database Lookup using prepared statements
        $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // 4. Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Prevent session fixation
            session_regenerate_id(true);
            
            // Store user data in session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect securely to the dashboard
            header("Location: /dashboard.php");
            exit;
        } else {
            // Generic error message to prevent email enumeration
            $error = "Invalid email or password.";
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
                <h2 style="font-size: 2rem;">Welcome Back</h2>
                <p>Log in to your SwimSphere account.</p>
            </div>

            <!-- Display Error Message -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ff6b6b; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.05);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p style="margin: 0; font-weight: 500; font-size: 0.95rem; line-height: 1.4;"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="/auth/login.php" method="POST" class="auth-form">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="email" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-white);">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 12px; border-radius: 5px; font-family: inherit;" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="password" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-white);">Password</label>
                    <input type="password" id="password" name="password" class="form-control" style="width: 100%; padding: 12px; border-radius: 5px; font-family: inherit;" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-large">Log In</button>
            </form>

            <div class="auth-links" style="text-align: center; margin-top: 25px;">
                <p style="color: var(--clr-text-light);">Don't have an account yet? <a href="/auth/register.php" style="font-weight: 600;">Register</a></p>
            </div>
            
        </div>
    </div>
</main>

<?php 
// Include the frontend footer UI
require_once '../includes/footer.php'; 
?>
