<?php
/**
 * Admin Panel - Login Page
 */

session_start();

// Debug: collect early login diagnostics
$LOGIN_DEBUG = [];
$LOGIN_DEBUG[] = '🔍 [Login] Session started';
$LOGIN_DEBUG[] = '🔍 [Login] Already logged: ' . ((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ? 'true' : 'false');

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $LOGIN_DEBUG[] = '✅ [Login] Redirect to dashboard (already logged)';
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
        $LOGIN_DEBUG[] = '✅ [Login] Auth OK for ' . $username;
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
        $LOGIN_DEBUG[] = '❌ [Login] Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Clase de Ciencia</title>
    <style>
        /* Minimal theme alignment with site styles (colors/typography) */
        :root {
            --color-primary: #1f3c88;
            --color-primary-light: #3d5ba9;
            --color-accent: #f9a825;
            --color-text: #2b2b2b;
            --color-text-light: #5f6368;
            --color-bg: #ffffff;
            --color-bg-alt: #f4f6f8;
            --color-border: #d4d8dd;
            --radius: 8px;
            --radius-sm: 4px;
            --shadow-sm: 0 4px 10px rgba(31,60,136,0.08);
            --shadow-md: 0 8px 18px rgba(31,60,136,0.12);
            --color-error: #d32f2f;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background-color: var(--color-bg-alt);
            color: var(--color-text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .login-container {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 2rem;
            max-width: 420px;
            width: 100%;
        }

        h1 {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
            text-align: center;
            color: var(--color-primary);
            letter-spacing: -0.01em;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--color-text-light);
            font-size: 0.95rem;
        }

        .form-group { margin-bottom: 1rem; }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--color-text);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            font-size: 1rem;
            background: var(--color-bg);
            color: var(--color-text);
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(31, 60, 136, 0.18);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--color-primary);
            color: #fff;
            border: 1px solid var(--color-primary);
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.08s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        .btn:hover {
            background-color: var(--color-primary-light);
            border-color: var(--color-primary-light);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .error {
            background-color: #fdecea;
            color: var(--color-error);
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #f5c2c7;
            border-radius: 6px;
        }

        .back-link { text-align: center; margin-top: 1rem; }
        .back-link a {
            color: var(--color-primary);
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: color 0.2s ease, border-color 0.2s ease;
        }
        .back-link a:hover {
            color: var(--color-primary-light);
            border-bottom-color: var(--color-accent);
        }
    </style>
</head>
<body>
    <div class="login-container">
                <h1>Admin Login</h1>
                <p class="subtitle">Clase de Ciencia</p>
                <script>
                    try {
                        var msgs = <?= json_encode($LOGIN_DEBUG, JSON_UNESCAPED_UNICODE) ?>;
                        console.log('🔍 [Login] Diagnostics:');
                        msgs.forEach(function(m){ console.log(m); });
                    } catch (e) { console.log('❌ [Login] Diagnostics emit error:', e && e.message); }
                </script>
        
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
