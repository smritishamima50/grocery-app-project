-- Admin Audit Log Table
-- This table logs all admin actions on users for security and compliance

CREATE TABLE IF NOT EXISTS admin_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type ENUM('POINTS_ADJUST', 'ACCOUNT_DEACTIVATE', 'ACCOUNT_ACTIVATE', 'NOTE_UPDATE', 'VIP_TOGGLE', 'PRIORITY_TOGGLE') NOT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
);

