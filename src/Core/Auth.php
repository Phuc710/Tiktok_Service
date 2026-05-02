<?php

namespace App\Core;

/**
 * Auth — API Key authentication middleware
 *
 * Cách dùng key:
 *   GET  ?api_key=YOUR_KEY
 *   POST header: X-Api-Key: YOUR_KEY
 *   POST body:   { "api_key": "YOUR_KEY" }
 */
class Auth {

    /**
     * Kiểm tra API key. Nếu sai → trả 401 JSON và exit ngay.
     */
    public static function requireApiKey(): void {
        $expected = EnvLoader::get('API_KEY', '');

        if (empty($expected)) {
            // Nếu chưa cấu hình key → coi như dev mode, cho qua
            return;
        }

        $provided = self::resolveKey();

        if (empty($provided) || !hash_equals($expected, $provided)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error'   => 'Unauthorized: Invalid or missing API key.',
                'hint'    => 'Pass key via: ?api_key=KEY, Header X-Api-Key: KEY, or body field api_key',
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    /**
     * Lấy API key từ nhiều nguồn khác nhau (GET / Header / POST body)
     */
    private static function resolveKey(): string {
        // 1. Query string
        if (!empty($_GET['api_key'])) {
            return trim($_GET['api_key']);
        }

        // 2. HTTP Header: X-Api-Key
        $headers = getallheaders();
        $headerKey = $headers['X-Api-Key'] ?? $headers['x-api-key'] ?? '';
        if (!empty($headerKey)) {
            return trim($headerKey);
        }

        // 3. JSON body
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true) ?? [];
        if (!empty($body['api_key'])) {
            return trim($body['api_key']);
        }

        // 4. POST form field
        if (!empty($_POST['api_key'])) {
            return trim($_POST['api_key']);
        }

        return '';
    }
}
