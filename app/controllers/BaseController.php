<?php
// Base controller class
class BaseController {
    protected $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    protected function render($view, $data = []) {
        extract($data);
        $viewFile = 'app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "View not found: $view";
        }
    }

    protected function redirect($url) {
        if (!headers_sent()) {
            header("Location: $url");
        }
        exit;
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    protected function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    protected function requireAdmin() {
        if (!$this->isAdmin()) {
            $this->redirect('/');
        }
    }
}
?>