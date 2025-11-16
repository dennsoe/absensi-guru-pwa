<?php

/**
 * Base Controller Class
 * Semua controller extends dari class ini
 */
class Controller {
    
    /**
     * Load Model
     */
    protected function model($model) {
        $modelPath = __DIR__ . '/../models/' . $model . '.php';
        
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }
        
        throw new Exception("Model {$model} tidak ditemukan");
    }
    
    /**
     * Load View
     */
    protected function view($view, $data = []) {
        // Extract data untuk digunakan di view
        extract($data);
        
        // Check if using layout
        $useLayout = !isset($data['no_layout']) || !$data['no_layout'];
        
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View {$view} tidak ditemukan");
        }
        
        if ($useLayout) {
            // Load dengan layout
            $content = $viewPath;
            require_once __DIR__ . '/../views/layouts/main.php';
        } else {
            // Load tanpa layout
            require_once $viewPath;
        }
    }
    
    /**
     * Return JSON Response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Return Success JSON
     */
    protected function jsonSuccess($message = 'Berhasil', $data = null) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->json($response);
    }
    
    /**
     * Return Error JSON
     */
    protected function jsonError($message = 'Terjadi kesalahan', $statusCode = 400, $errors = null) {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $statusCode);
    }
    
    /**
     * Redirect
     */
    protected function redirect($url) {
        $router = new Router();
        header('Location: ' . $router->url($url));
        exit;
    }
    
    /**
     * Get POST Data
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET Data
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get JSON Body
     */
    protected function getJsonBody() {
        return json_decode(file_get_contents('php://input'), true);
    }
    
    /**
     * Check if Request is POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if Request is GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if Request is AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Validate Required Fields
     */
    protected function validateRequired($fields, $data) {
        $errors = [];
        
        foreach ($fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[$field] = "{$label} tidak boleh kosong";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitize Input
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Upload File
     */
    protected function uploadFile($file, $destination = 'uploads', $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'File tidak valid'];
        }
        
        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Ukuran file maksimal 5MB'];
        }
        
        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $ext;
        $uploadPath = __DIR__ . '/../../public/' . $destination . '/';
        
        // Create directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $targetFile = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => '/' . $destination . '/' . $filename
            ];
        }
        
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
    
    /**
     * Check Authentication
     */
    protected function requireAuth($roles = []) {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->jsonError('Unauthorized', 401);
            } else {
                $this->redirect('/login');
            }
        }
        
        // Check role
        if (!empty($roles) && !in_array($_SESSION['role'], $roles)) {
            if ($this->isAjax()) {
                $this->jsonError('Forbidden', 403);
            } else {
                $this->redirect('/dashboard');
            }
        }
    }
    
    /**
     * Get Current User
     */
    protected function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $userModel = $this->model('User');
        return $userModel->findById($_SESSION['user_id']);
    }
}