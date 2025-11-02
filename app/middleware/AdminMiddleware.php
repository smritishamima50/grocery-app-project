<?php
/**
 * Admin Middleware - RBAC (Role-Based Access Control)
 * Ensures only admin users can access admin routes
 * 
 * @author Admin System
 * @version 1.0
 */

class AdminMiddleware {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Check if user is authenticated and has admin role
     * @return bool
     */
    public function isAdmin(): bool {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if role is admin (case-insensitive)
        $role = $_SESSION['role'] ?? '';
        return strtolower($role) === 'admin';
    }
    
    /**
     * Require admin access - redirect if not admin
     * @return void
     */
    public function requireAdmin(): void {
        if (!$this->isAdmin()) {
            // Log unauthorized access attempt
            $this->logUnauthorizedAccess();
            
            // Redirect to 403 page or login
            $this->redirectTo403();
        }
    }
    
    /**
     * Get admin user data (without password_hash)
     * @return array|null
     */
    public function getAdminData(): ?array {
        if (!$this->isAdmin()) {
            return null;
        }
        
        $userId = $_SESSION['user_id'];
        $stmt = $this->pdo->prepare("
            SELECT id, email, phone, role, first_name, last_name, created_at, updated_at
            FROM users 
            WHERE id = ? AND role = 'admin'
        ");
        $stmt->execute([$userId]);
        $admin = $stmt->fetch();
        
        return $admin ?: null;
    }
    
    /**
     * Check admin permissions for specific actions
     * @param string $action
     * @return bool
     */
    public function canPerformAction(string $action): bool {
        if (!$this->isAdmin()) {
            return false;
        }
        
        // Define admin permissions
        $adminPermissions = [
            'view_dashboard',
            'manage_products',
            'manage_orders',
            'manage_users',
            'manage_coupons',
            'manage_categories',
            'manage_subscriptions',
            'manage_surprise_gifts',
            'view_analytics',
            'manage_inventory',
            'update_order_status',
            'delete_products',
            'delete_categories',
            'create_products',
            'create_categories'
        ];
        
        return in_array($action, $adminPermissions);
    }
    
    /**
     * Log unauthorized access attempts
     * @return void
     */
    private function logUnauthorizedAccess(): void {
        $userId = $_SESSION['user_id'] ?? 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        error_log("UNAUTHORIZED ADMIN ACCESS ATTEMPT - User ID: $userId, IP: $ip, URI: $requestUri, User Agent: $userAgent");
    }
    
    /**
     * Redirect to 403 Forbidden page
     * @return void
     */
    private function redirectTo403(): void {
        // Set 403 status code
        http_response_code(403);
        
        // Redirect to 403 page or login
        if (isset($_SESSION['user_id'])) {
            // User is logged in but not admin - show 403
            header('Location: /403');
        } else {
            // User not logged in - redirect to login
            header('Location: /login');
        }
        exit;
    }
    
    /**
     * Get admin initials for avatar
     * @return string
     */
    public function getAdminInitials(): string {
        $adminData = $this->getAdminData();
        if (!$adminData) {
            return 'A';
        }
        
        $firstName = $adminData['first_name'] ?? '';
        $lastName = $adminData['last_name'] ?? '';
        
        $initials = '';
        if ($firstName) $initials .= strtoupper(substr($firstName, 0, 1));
        if ($lastName) $initials .= strtoupper(substr($lastName, 0, 1));
        
        return $initials ?: 'A';
    }
    
    /**
     * Get admin full name
     * @return string
     */
    public function getAdminFullName(): string {
        $adminData = $this->getAdminData();
        if (!$adminData) {
            return 'Admin User';
        }
        
        $firstName = $adminData['first_name'] ?? '';
        $lastName = $adminData['last_name'] ?? '';
        
        return trim($firstName . ' ' . $lastName) ?: 'Admin User';
    }
}
?>
