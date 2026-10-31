<?php

declare(strict_types=1);

// Validate configuration without connecting to or writing to the database.
$errors = [];
$key = (string) getenv('APP_KEY');
$decoded = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
if (! is_string($decoded) || strlen($decoded) !== 32) {
    $errors[] = 'Set a stable, private APP_KEY for this Preview environment.';
}

$url = (string) getenv('APP_URL');
if (! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https' || ! parse_url($url, PHP_URL_HOST)) {
    $errors[] = 'Set APP_URL to the HTTPS preview URL, or expose VERCEL_URL.';
}

foreach (['APP_DEBUG' => 'false', 'RASHFA_PREVIEW_READ_ONLY' => 'true', 'SESSION_DRIVER' => 'cookie', 'SESSION_SECURE_COOKIE' => 'true', 'DB_CONNECTION' => 'mysql', 'CACHE_STORE' => 'file'] as $name => $required) {
    if (getenv($name) !== $required) {
        $errors[] = "The preview requires {$name}={$required}.";
    }
}

foreach (['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $name) {
    if (getenv($name) === false || getenv($name) === '') {
        $errors[] = "Missing {$name}; supply the existing MySQL configuration securely.";
    }
}

// A provider-specific public CA can be injected without committing a file.
if ($encodedCa = getenv('MYSQL_SSL_CA_BASE64')) {
    $certificate = base64_decode($encodedCa, true);
    if ($certificate === false || ! openssl_x509_read($certificate)) {
        $errors[] = 'MYSQL_SSL_CA_BASE64 must contain a valid public CA certificate.';
    } else {
        $path = '/tmp/rashfa-mysql-ca.pem';
        if (file_put_contents($path, $certificate) === false || ! chmod($path, 0600)) {
            $errors[] = 'Unable to prepare the MySQL CA certificate.';
        }
    }
}

if (! $encodedCa && ! is_readable((string) getenv('MYSQL_ATTR_SSL_CA'))) {
    $errors[] = 'MYSQL_ATTR_SSL_CA must point to a readable trusted CA bundle.';
}

if (getenv('DB_URL')) {
    $errors[] = 'Use individual DB_* variables for the read-only preview; do not set DB_URL.';
}

$origin = getenv('RASHFA_MEDIA_BASE_URL');
if ($origin && (! filter_var($origin, FILTER_VALIDATE_URL) || parse_url($origin, PHP_URL_SCHEME) !== 'https')) {
    $errors[] = 'RASHFA_MEDIA_BASE_URL must be a valid HTTPS origin.';
}

if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, 'Preview configuration: '.$error.PHP_EOL);
    }
    exit(1);
}
