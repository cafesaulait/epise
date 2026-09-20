<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

date_default_timezone_set('Pacific/Noumea');
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);

// === CORS ===
$origine = $_SERVER['HTTP_ORIGIN'] ?? '';
$originsAutorisees = [
    'http://localhost:4200',
    'http://localhost:4201',
    'https://epise-unc.netlify.app'
];

if (in_array($origine, $originsAutorisees, true)) {
    header("Access-Control-Allow-Origin: $origine");
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Vary: Origin');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
// === FIN CORS ===

require_once ROOT . 'app/Debug.php';
require_once ROOT . 'app/ConnexionBDD.php';
require_once ROOT . 'app/Model.php';
require_once ROOT . 'app/Controller.php';

$params = isset($_GET['p']) ? explode('/', trim($_GET['p'], '/')) : [];
$params = array_values(array_filter($params, fn($p) => $p !== ''));

$controllerName = ucfirst($params[0] ?? 'main');
$controllerFile = ROOT . 'controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo 'La page recherchée n\'existe pas';
    exit;
}

require_once $controllerFile;
$class = '\\controllers\\' . $controllerName;
$controller = new $class();

if (isset($params[1]) && ctype_digit($params[1])) {
    $action = 'index';
    unset($params[0]);
} else {
    $action = $params[1] ?? 'index';
    unset($params[0], $params[1]);
}

if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'La page recherchée n\'existe pas';
    exit;
}

$params = array_values($params);
$controller->$action(...$params);
