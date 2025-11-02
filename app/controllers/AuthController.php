<?php
require_once 'BaseController.php';

class AuthController extends BaseController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $this->redirect('/');
            } else {
                $error = "Invalid email or password";
                $this->render('auth/login', ['error' => $error]);
            }
        } else {
            $this->render('auth/login');
        }
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';

            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already exists";
                $this->render('auth/signup', ['error' => $error]);
                return;
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("INSERT INTO users (email, phone, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$email, $phone, $passwordHash, $firstName, $lastName])) {
                $_SESSION['user_id'] = $this->pdo->lastInsertId();
                $_SESSION['role'] = 'customer';
                $_SESSION['first_name'] = $firstName;
                $this->redirect('/');
            } else {
                $error = "Registration failed";
                $this->render('auth/signup', ['error' => $error]);
            }
        } else {
            $this->render('auth/signup');
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
?>