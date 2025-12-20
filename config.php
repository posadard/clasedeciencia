<?php
/**
 * The Green Almanac - Configuration File
 * Database and site settings
 */

// Database Configuration (Clase de Ciencia)
define('DB_HOST', 'localhost');
define('DB_NAME', 'u626603208_clasedeciencia');
define('DB_USER', 'u626603208_clasedeciencia');
define('DB_PASS', ':;3y>|mn~X0t');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Clase de Ciencia');
define('SITE_URL', 'https://clasedeciencia.com');
define('SITE_DESCRIPTION', 'Proyectos científicos interactivos para estudiantes colombianos (6°-11°).');

// Store/Partner Integration (placeholder)
define('CHEMICALSTORE_URL', 'https://chemicalstore.com');
define('UTM_SOURCE', 'clasedeciencia');
define('UTM_MEDIUM', 'referral');

// Contact Email
define('CONTACT_EMAIL', 'soporte@clasedeciencia.com');

// Pagination
define('POSTS_PER_PAGE', 12);

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone (Colombia)
date_default_timezone_set('America/Bogota');

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
