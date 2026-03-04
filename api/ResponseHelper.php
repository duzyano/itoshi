<?php
// api/ResponseHelper.php
// Standardized API response formatting

class ResponseHelper {
    
    /**
     * Send a success response
     */
    public static function success($data = [], $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send an error response
     */
    public static function error($message = 'Error', $statusCode = 400, $errors = []) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Get JSON payload from request
     */
    public static function getJsonPayload() {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }
}
?>
