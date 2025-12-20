<?php
/**
 * Admin Panel - Login Page
 */

session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple authentication (change these credentials!)
    $admin_username = 'admin';
    $admin_password = 'Thegreen2025'; // Same as DB password for now
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - The Green Almanac</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: white;
            border: 2px solid #000;
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: 2px solid #000;
            border-color: #000;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #000;
            color: white;
            border: 2px solid #000;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #333;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #c62828;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <p style="text-align: center; margin-bottom: 1.5rem; color: #666;">The Green Almanac</p>
        
        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="back-link">
            <a href="/">← Back to Website</a>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #f5f5f5; font-size: 0.85rem;">
            <strong>Default Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>Thegreen2025</code><br>
            <em style="color: #c62828;">⚠️ Change these in production!</em>
        </div>
    </div>
</body>
</html>
