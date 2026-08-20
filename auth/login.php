<?php
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
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // 4. Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Start the session (if not already started)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect securely to the homepage
            header("Location: /index.php");
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
        <!-- We use inline styles here to match the exact design language of register.php -->
        <div class="auth-card" style="max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
            
            <div class="section-header" style="margin-bottom: 30px;">
                <h2 style="font-size: 2rem;">Welcome Back</h2>
                <p>Log in to your SwimSphere account.</p>
            </div>

            <!-- Display Error Message -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <p style="margin: 0; font-weight: 500;">⚠️ <?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="/auth/login.php" method="POST" class="auth-form">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="email" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="password" style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--clr-navy);">Password</label>
                    <input type="password" id="password" name="password" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;" required>
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
