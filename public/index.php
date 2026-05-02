<?php

/**
 * ============================================================
 * KaiTiktok — Front Controller (Entry Point)
 * ============================================================
 * Mọi request đều vào đây qua .htaccess rewrite.
 * File này CHỈ làm bootstrap + dispatch — KHÔNG chứa logic route.
 *
 * Để thêm route mới: tạo/sửa file trong routes/
 * ============================================================
 */

declare(strict_types=1);

// ── 1. Autoloader ─────────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    if (!str_starts_with($class, $prefix)) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
});

// ── 2. Bootstrap ──────────────────────────────────────────────────────────────
use App\Core\EnvLoader;
use App\Core\Router;

EnvLoader::load(__DIR__ . '/../.env');
$config = require __DIR__ . '/../config/config.php';

// ── 3. Helpers ────────────────────────────────────────────────────────────────

/** Emit a JSON response and exit. */
function jsonResponse(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/** Read JSON POST body or fall back to $_POST / $_GET. */
function readInput(): array {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw  = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json) && !empty($json)) return $json;
        return $_POST;
    }
    return $_GET;
}

/** Render a PHP template file (from templates/ directory). */
function renderTemplate(string $name, array $vars = []): void {
    global $config, $pageTitle, $activePage, $extraHead;
    extract($vars);
    require __DIR__ . '/../templates/' . $name . '.php';
}

// ── 4. Resolve current path ───────────────────────────────────────────────────
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath   = rtrim(dirname($scriptName), '/');

// Tránh "//" bị parse_url hiểu nhầm là hostname
$parsedUrl  = parse_url('http://localhost' . $requestUri, PHP_URL_PATH) ?: '/';
$relPath    = substr($parsedUrl, strlen($basePath));

// Normalize path
$path   = preg_replace('#/+#', '/', '/' . trim($relPath, '/'));
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// ── 5. Load routes ────────────────────────────────────────────────────────────
$router = new Router();

require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/boost.php';
require __DIR__ . '/../routes/profile.php';
require __DIR__ . '/../routes/api.php';
require __DIR__ . '/../routes/download.php';

// ── 6. Dispatch ───────────────────────────────────────────────────────────────
$router->dispatch($path, $method);
