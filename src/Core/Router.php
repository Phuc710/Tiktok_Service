<?php

namespace App\Core;

/**
 * Router — Đăng ký và dispatch routes
 * 
 * Dùng:
 *   $router->get('/path',  fn() => ...)
 *   $router->post('/path', fn() => ...)
 *   $router->any('/path',  fn() => ...)
 *   $router->dispatch($path, $method)
 */
class Router {

    private array $routes = [];

    // ── Đăng ký route ─────────────────────────────────────────────────────────

    public function get(string $path, \Closure $handler): static {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, \Closure $handler): static {
        return $this->add('POST', $path, $handler);
    }

    public function any(string $path, \Closure $handler): static {
        return $this->add('ANY', $path, $handler);
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    private function add(string $method, string $path, \Closure $handler): static {
        $this->routes[] = ['method' => $method, 'path' => $path, 'handler' => $handler];
        return $this;
    }

    // ── Dispatch ──────────────────────────────────────────────────────────────

    public function dispatch(string $path, string $method): void {
        $method = strtoupper($method);
        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) continue;
            if ($route['path'] !== $path) continue;
            ($route['handler'])();
            return;
        }
        $this->handle404($path);
    }

    private function handle404(string $path): void {
        // 404 — chia sẻ $config qua global (đã inject từ index.php)
        global $config;
        http_response_code(404);
        $pageTitle  = '404 — Không tìm thấy trang';
        $activePage = '';
        renderTemplate('header');
        ?>
        <section class="app-container error-section">
            <div class="error-alert" style="text-align:center; padding: 3rem 2rem;">
                <i class="fa-solid fa-circle-xmark error-icon" style="font-size:3rem; display:block; margin-bottom:1rem;"></i>
                <b style="font-size:1.4rem;">404 — Trang không tồn tại</b><br><br>
                <a href="<?php echo $config['base_url']; ?>/" style="color: var(--accent);">← Quay về trang chủ</a>
            </div>
        </section>
        <?php
        renderTemplate('footer');
    }
}
