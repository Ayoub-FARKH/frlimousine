<?php
/**
 * performance-config.php - Configuration des performances pour FRLimousine
 * À placer sur le serveur OVH pour des performances optimales
 */

// Configuration PHP optimale pour OVH
ini_set('memory_limit', '128M');
ini_set('max_execution_time', '30');
ini_set('max_input_time', '60');
ini_set('post_max_size', '8M');
ini_set('upload_max_filesize', '8M');
ini_set('max_file_uploads', '1');

// Activation d'OPcache si disponible
if (function_exists('opcache_get_status')) {
    ini_set('opcache.enable', '1');
    ini_set('opcache.memory_consumption', '128');
    ini_set('opcache.max_accelerated_files', '10000');
    ini_set('opcache.revalidate_freq', '60');
}

// Configuration de session optimisée
ini_set('session.gc_maxlifetime', '1440');
ini_set('session.cookie_lifetime', '0');
ini_set('session.use_cookies', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1'); // À activer avec HTTPS

// Headers de sécurité et performance
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Cache des pages statiques (si utilisation de CMS)
$cacheTime = 3600; // 1 heure
header('Cache-Control: public, max-age=' . $cacheTime);

// Compression si non activée dans .htaccess
if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
    // Pas de compression supportée
} else {
    // Compression activée via .htaccess
}

// Optimisation base de données (si utilisation de MySQL)
if (function_exists('mysqli_connect')) {
    $mysqli = mysqli_connect('localhost', 'user', 'password', 'database');
    if ($mysqli) {
        mysqli_query($mysqli, "SET NAMES 'utf8'");
        mysqli_query($mysqli, "SET CHARACTER SET utf8");
        mysqli_query($mysqli, "SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        mysqli_close($mysqli);
    }
}

// Fonction de mesure des performances
function getPerformanceMetrics() {
    $metrics = [
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true),
        'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'database_enabled' => function_exists('mysqli_connect') ? 'Oui' : 'Non',
        'opcache_enabled' => function_exists('opcache_get_status') ? 'Oui' : 'Non'
    ];

    return $metrics;
}

// Logging des performances (optionnel)
if (isset($_GET['debug']) && $_GET['debug'] === 'performance') {
    $metrics = getPerformanceMetrics();
    error_log('FRLimousine Performance: ' . json_encode($metrics));
}

// Configuration des erreurs en production
if ($_SERVER['SERVER_NAME'] === 'www.frlimousine.com') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    // Mode développement
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
}

// Optimisation du buffer de sortie
if (ob_get_level() === 0) {
    ob_start('ob_gzhandler');
}

// Configuration de timezone
date_default_timezone_set('Europe/Paris');

// Fonction d'autoload optimisée (si utilisation de classes)
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Configuration des limites de fichiers
if (isset($_FILES)) {
    foreach ($_FILES as $key => $file) {
        if ($file['size'] > 8 * 1024 * 1024) { // 8MB
            die('Fichier trop volumineux');
        }
    }
}

// Protection contre les attaques DoS de base
if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
    $timeDiff = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    if ($timeDiff > 30) { // Requête trop lente
        http_response_code(408);
        die('Timeout de requête');
    }
}

// Configuration de sécurité supplémentaire
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' https://fonts.gstatic.com');

// Fin de la configuration
?>