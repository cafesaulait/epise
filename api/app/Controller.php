<?php

namespace app;

#[\AllowDynamicProperties]
abstract class Controller
{
    public function loadModel(string $model): void
    {
        $file = ROOT . 'models/' . $model . '.php';
        if (!file_exists($file)) throw new \RuntimeException("Modèle introuvable : {$model}");
        require_once $file;
        $class = '\\models\\' . $model;
        $this->$model = new $class();
    }

    public function render(string $fichier, array $data = [], string $layout = 'default'): void
    {
        extract($data);
        ob_start();
        $controller = strtolower((new \ReflectionClass($this))->getShortName());
        $view = ROOT . 'views/' . $controller . '/' . $fichier . '.php';
        if (!file_exists($view)) throw new \RuntimeException("Vue introuvable : {$view}");
        require $view;
        $content = ob_get_clean();
        require ROOT . 'views/layout/' . $layout . '.php';
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function jsonInput(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?: [];
    }

    protected function isAdmin(): bool
    {
        return !empty($_SESSION['admin_id']);
    }
}
