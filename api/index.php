<?php
session_start();
date_default_timezone_set('Pacific/Noumea');
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR);

require_once ROOT . 'app/Debug.php';
require_once ROOT . 'app/ConnexionBDD.php';
require_once ROOT . 'app/Model.php';
require_once ROOT . 'app/Controller.php';

$params = isset($_GET['p']) ? explode('/', trim($_GET['p'], '/')) : [];
$params = array_values(array_filter($params, fn($p) => $p !== ''));

$controllerName = ucfirst($params[0] ?? 'main');
$action = $params[1] ?? 'index';
$controllerFile = ROOT . 'controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo 'La page recherchée n\'existe pas';
    exit;
}

require_once $controllerFile;
$class = '\\controllers\\' . $controllerName;
$controller = new $class();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'La page recherchée n\'existe pas';
    exit;
}

unset($params[0], $params[1]);
$params = array_values($params);
$controller->$action(...$params);
?>