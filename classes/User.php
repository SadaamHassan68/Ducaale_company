<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            return true;
        }
        return false;
    }

    public function register($name, $email, $password) {
        // Check if email already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email is already registered.'];
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password_hash, role) 
            VALUES (:name, :email, :password_hash, 'Passenger')
        ");
        
        try {
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => $password_hash
            ]);
            return ['success' => true, 'message' => 'Registration successful!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed.'];
        }
    }

    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public function requireRole($required_role) {
        if (!$this->isLoggedIn()) {
            header("Location: /Booking/login.php");
            exit;
        }
        if ($_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'Admin') {
            die("Access Denied: You do not have permission to view this page.");
        }
    }

    public function updateProfile($id, $name, $email) {
        // Check if email is already taken by another user
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $email, 'id' => $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email is already in use by another account.'];
        }

        $stmt = $this->pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
        if ($stmt->execute(['name' => $name, 'email' => $email, 'id' => $id])) {
            $_SESSION['name'] = $name; // Update session name
            return ['success' => true, 'message' => 'Profile updated successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to update profile.'];
    }

    public function updatePassword($id, $currentPassword, $newPassword) {
        $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if ($user && password_verify($currentPassword, $user['password_hash'])) {
            $new_hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            if ($stmt->execute(['hash' => $new_hash, 'id' => $id])) {
                return ['success' => true, 'message' => 'Password updated successfully.'];
            }
            return ['success' => false, 'message' => 'Failed to update password.'];
        }
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: /Booking/login.php");
        exit;
    }
}
?>
