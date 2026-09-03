<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

if (! hash_equals('__DEPLOY_TOKEN__', (string) ($_GET['token'] ?? ''))) {
    http_response_code(404);
    echo json_encode(['error' => 'NOT_FOUND']);
    exit;
}

register_shutdown_function(static function (): void {
    @unlink(__FILE__);
    @unlink(__DIR__.'/.ftp-auth-inspector-state.json');
});

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$connection = Illuminate\Support\Facades\DB::connection();
$columns = collect(Illuminate\Support\Facades\Schema::getColumns('users'))
    ->map(fn (array $column): array => [
        'name' => $column['name'],
        'type' => $column['type_name'],
        'nullable' => $column['nullable'],
        'default' => $column['default'],
        'auto_increment' => $column['auto_increment'],
    ])->values();

$logFile = storage_path('logs/laravel.log');
$registrationError = null;
if (is_file($logFile)) {
    $handle = fopen($logFile, 'rb');
    if ($handle !== false) {
        $size = filesize($logFile) ?: 0;
        fseek($handle, max(0, $size - 100000));
        $tail = stream_get_contents($handle) ?: '';
        fclose($handle);
        $lines = preg_split('/\R/', $tail) ?: [];
        foreach (array_reverse($lines) as $line) {
            if (str_contains($line, 'Customer registration database failure')) {
                $registrationError = mb_substr($line, 0, 4000);
                break;
            }
        }
    }
}

echo json_encode([
    'connection' => [
        'driver' => $connection->getDriverName(),
        'database_fingerprint' => hash('sha256', (string) $connection->getDatabaseName()),
    ],
    'users_columns' => $columns,
    'auth_sessions_exists' => Illuminate\Support\Facades\Schema::hasTable('auth_sessions'),
    'password_resets_exists' => Illuminate\Support\Facades\Schema::hasTable('customer_password_reset_tokens'),
    'migration_recorded' => Illuminate\Support\Facades\DB::table('migrations')
        ->where('migration', '2026_09_03_000001_create_customer_auth_tables')->exists(),
    'last_registration_error' => $registrationError,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
