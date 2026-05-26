<?php

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$db   = $_ENV['DB_NAME'] ?? 'test_mercahub';
$user = $_ENV['DB_USER'] ?? 'tom';
$pass = $_ENV['DB_PASS'] ?? '';

define('BASE_URL', $_ENV['BASE_URL'] ?? '/proyecto');

class PDOCompatible {
    private mysqli $mysqli;

    public function __construct(string $host, string $user, string $pass, string $db) {
        $this->mysqli = new mysqli($host, $user, $pass, $db);
        if ($this->mysqli->connect_error) {
            throw new RuntimeException('Connection failed: ' . $this->mysqli->connect_error);
        }
        $this->mysqli->set_charset('utf8mb4');
    }

    public function prepare(string $sql): PDOStmtCompatible {
        return new PDOStmtCompatible($this->mysqli, $sql);
    }

    public function query(string $sql) {
        $result = $this->mysqli->query($sql);
        if (!$result) {
            throw new RuntimeException('Query error: ' . $this->mysqli->error);
        }
        // Return a wrapper that holds the result set for fetchAll/fetch calls
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        return new class($rows) {
            private array $rows;
            public function __construct(array $rows) { $this->rows = $rows; }
            public function fetchAll(int $mode = 0): array { return $this->rows; }
            public function fetch(int $mode = 0) { return $this->rows[0] ?? null; }
        };
    }

    public function lastInsertId(): int {
        return $this->mysqli->insert_id;
    }
}

class PDOStmtCompatible {
    private mysqli $mysqli;
    private string $sql;
    private mysqli_stmt $stmt;
    private bool $executed = false;

    public function __construct(mysqli $mysqli, string $sql) {
        $this->mysqli = $mysqli;
        $this->sql = $sql;
        $this->stmt = $mysqli->prepare($sql);
        if (!$this->stmt) {
            throw new RuntimeException('Prepare failed: ' . $mysqli->error);
        }
    }

    public function execute(?array $params = null): bool {
        if ($params) {
            $types = '';
            $values = [];
            foreach ($params as $p) {
                if (is_int($p)) { $types .= 'i'; }
                elseif (is_float($p)) { $types .= 'd'; }
                elseif (is_null($p)) { $types .= 's'; $p = null; }
                else { $types .= 's'; }
                $values[] = $p;
            }
            if ($types !== '') {
                $this->stmt->bind_param($types, ...$values);
            }
        }
        $this->executed = true;
        return $this->stmt->execute();
    }

    private function ensureExecuted(): void {
        if (!$this->executed) {
            $this->stmt->execute();
            $this->executed = true;
        }
    }

    public function fetch() {
        $this->ensureExecuted();
        $result = $this->stmt->get_result();
        return $result->fetch_assoc();
    }

    public function fetchAll() {
        $this->ensureExecuted();
        $result = $this->stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function bindParam(string|int $param, mixed &$var, int $type = 0): void {
        // Not needed - use positional params in execute()
    }

    public function closeCursor(): void {
        $this->stmt->close();
    }
}

try {
    $pdo = new PDOCompatible($host, $user, $pass, $db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// === Security helpers ===

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf(?string $token): bool {
    return $token && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(): void {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
    if (!validate_csrf($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF invalido.']);
        exit;
    }
}

function check_rate_limit(string $action, int $max = 10, int $window = 60): void {
    $key = 'rl_' . $action . '_' . ($_SESSION['user_id'] ?? $_SERVER['REMOTE_ADDR']);
    $now = time();
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => $now];
    if ($attempts['count'] >= $max && $now - $attempts['time'] < $window) {
        $retryAfter = $window - ($now - $attempts['time']);
        http_response_code(429);
        echo json_encode(['error' => "Demasiadas solicitudes. Intenta en $retryAfter segundos."]);
        exit;
    }
    if ($now - $attempts['time'] >= $window) {
        $attempts = ['count' => 0, 'time' => $now];
    }
    $attempts['count']++;
    $_SESSION[$key] = $attempts;
}

function is_admin(): bool {
    return ($_SESSION['user_rol'] ?? '') === 'admin';
}

function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado. Se requiere rol de administrador.']);
        exit;
    }
}

function log_actividad(string $accion, ?string $detalles = null): void {
    global $pdo;
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO actividad_log (id_usuario, accion, detalles) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $accion, $detalles]);
    } catch (Exception $e) {
        // Silent fail for logging
    }
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
