<?php

/**
 * User Model
 */
class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    /**
     * Find By Username
     */
    public function findByUsername($username) {
        return $this->findBy('username', $username, 1);
    }
    
    /**
     * Verify Login
     */
    public function verifyLogin($username, $password) {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // Check if user is active
        if ($user['status'] !== 'aktif') {
            return false;
        }
        
        // Update last login
        $this->update($user['user_id'], [
            'last_login' => date('Y-m-d H:i:s')
        ]);
        
        return $user;
    }
    
    /**
     * Create New User
     */
    public function createUser($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update User
     */
    public function updateUser($userId, $data) {
        // Hash password if changed
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->update($userId, $data);
    }
    
    /**
     * Get Users By Role
     */
    public function getUsersByRole($role) {
        return $this->findWhere('role = :role', ['role' => $role]);
    }
    
    /**
     * Get Active Users
     */
    public function getActiveUsers() {
        return $this->findWhere('status = :status', ['status' => 'aktif']);
    }
    
    /**
     * Change Password
     */
    public function changePassword($userId, $newPassword) {
        return $this->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }
    
    /**
     * Check Username Exists
     */
    public function usernameExists($username, $excludeId = null) {
        return $this->exists('username', $username, $excludeId);
    }
}