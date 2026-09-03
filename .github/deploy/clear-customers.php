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
    @unlink(__DIR__.'/.ftp-clear-customers-state.json');
});

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$counts = Illuminate\Support\Facades\DB::transaction(function (): array {
    $before = [
        'users' => Illuminate\Support\Facades\DB::table('users')->count(),
        'sessions' => Illuminate\Support\Facades\DB::table('auth_sessions')->count(),
        'reset_tokens' => Illuminate\Support\Facades\DB::table('customer_password_reset_tokens')->count(),
    ];

    Illuminate\Support\Facades\DB::table('customer_password_reset_tokens')->delete();
    Illuminate\Support\Facades\DB::table('auth_sessions')->delete();
    Illuminate\Support\Facades\DB::table('users')->delete();

    return [
        'deleted' => $before,
        'remaining' => [
            'users' => Illuminate\Support\Facades\DB::table('users')->count(),
            'sessions' => Illuminate\Support\Facades\DB::table('auth_sessions')->count(),
            'reset_tokens' => Illuminate\Support\Facades\DB::table('customer_password_reset_tokens')->count(),
        ],
    ];
});

echo json_encode($counts, JSON_PRETTY_PRINT);
