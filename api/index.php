<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); 

session_start();
date_default_timezone_set('Pacific/Noumea');
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);

$origine = $_SERVER['HTTP_ORIGIN'] ?? '';

$originsAutorisees = [
    'http://localhost:4200',
    'http://localhost:4201',
    'https://epise-unc.netlify.app'
];

if (in_array($origine, $originsAutorisees, true)) {
    header("Access-Control-Allow-Origin: $origine");
} else {
    header("Access-Control-Allow-Origin: https://epise-unc.netlify.app");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
