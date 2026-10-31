<?php

declare(strict_types=1);

// Run only after credentials are supplied. This performs SELECTs only and
// deliberately prints no credentials, database names, records or hostnames.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $db = Illuminate\Support\Facades\DB::connection();
    if ($db->getDriverName() !== 'mysql' || ! config('rashfa.preview_read_only')) {
        throw new RuntimeException('Use MySQL with read-only preview mode enabled.');
    }
    $db->selectOne('SELECT 1 AS connection_ok');
    $tls = $db->selectOne("SHOW SESSION STATUS LIKE 'Ssl_cipher'");
    if (! $tls || empty($tls->Value)) {
        throw new RuntimeException('The MySQL connection is not encrypted.');
    }
    foreach (['products', 'categories', 'media_library', 'product_images', 'ingredients', 'product_ingredients', 'product_variants', 'product_story_phases', 'site_settings', 'admins'] as $table) {
        $db->select('SELECT 1 FROM `'.$table.'` LIMIT 0');
    }
    fwrite(STDOUT, "MySQL connection, TLS and required table access: OK. No data changes performed.\n");
} catch (Throwable $exception) {
    // Driver exception messages can contain usernames/hosts. Never print them.
    fwrite(STDERR, "Database check failed. Check connectivity, TLS, SELECT grants and schema using private provider logs.\n");
    exit(1);
}
