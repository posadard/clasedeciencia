<?php
// Clase de Ciencia - Configuración principal
// Actualiza las credenciales de base de datos antes de usar.

date_default_timezone_set('America/Bogota');

define('SITE_NAME', 'Clase de Ciencia');
define('SITE_URL', 'https://clasedeciencia.com'); // Actualiza si es distinto

// Credenciales (el usuario las configura manualmente)
define('DB_HOST', 'localhost');
define('DB_NAME', 'u626603208_clasedeciencia');
define('DB_USER', 'u626603208_clasedeciencia');
define('DB_PASS', 'W|6m*+@AE6n:');

$pdo = null;
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB Connection Error: ' . $e->getMessage());
    // El sitio debe mostrar contenido estático si no hay DB
}
?>