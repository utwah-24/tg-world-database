<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

if (! hash_equals('__DEPLOY_TOKEN__', (string) ($_GET['token'] ?? ''))) {
    http_response_code(404);
    echo 'NOT_FOUND';
    exit;
}

register_shutdown_function(static function (): void {
    @unlink(__FILE__);
    @unlink(__DIR__.'/.ftp-release-runner-state.json');
});

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($kernel->call('migrate', ['--force' => true]) !== 0) {
    http_response_code(500);
    echo "MIGRATION_FAILED\n";
    echo substr($kernel->output(), -4000);
    exit;
}

echo 'RELEASE_OK';
