<?php
/**
 * The Green Almanac - Configuration File
 * Database and site settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'toys2000_green');
define('DB_USER', 'toys2000_green');
define('DB_PASS', 'Thegreen2025');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'The Green Almanac');
define('SITE_URL', 'https://thegreenalmanac.com');
define('SITE_DESCRIPTION', 'Practical chemistry guidance for homesteaders and farmers');

// ChemicalStore Integration
define('CHEMICALSTORE_URL', 'https://chemicalstore.com');
define('UTM_SOURCE', 'thegreenalmanac');
define('UTM_MEDIUM', 'referral');

// Contact Email
define('CONTACT_EMAIL', 'office@chemicalstore.com');

// Pagination
define('POSTS_PER_PAGE', 12);

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/New_York');

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
