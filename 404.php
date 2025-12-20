<?php
/**
 * 404 Error Page
 */

http_response_code(404);

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$page_title = '404 - Page Not Found';
$page_description = 'The page you requested could not be found';

include 'includes/header.php';
?>

<div class="container error-page">
    <div class="error-content">
        <h1>404 - Page Not Found</h1>
        <p>Sorry, the page you're looking for doesn't exist or has been moved.</p>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Go Home</a>
            <a href="/library.php" class="btn btn-secondary">Browse Library</a>
        </div>
        
        <div class="helpful-links">
            <h2>Popular Pages</h2>
            <ul>
                <li><a href="/library.php">Article Library</a></li>
                <li><a href="/dictionary.php">Chemical Dictionary</a></li>
                <li><a href="/essentials.php">Essential Products</a></li>
                <li><a href="/contact.php">Contact Us</a></li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
