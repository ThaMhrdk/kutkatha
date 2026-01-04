<?php
/**
 * DEBUG FILE - HAPUS SETELAH TROUBLESHOOTING!
 *
 * File ini untuk debug masalah 403 di server
 * Akses via: https://kutkatha.sisteminformasikotacerdas.id/debug-auth.php
 */

// Cegah caching
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Debug Authentication & Session</h1>";
echo "<hr>";

// 1. Cek PHP Version
echo "<h2>1. PHP Environment</h2>";
echo "<p><b>PHP Version:</b> " . phpversion() . "</p>";
echo "<p><b>Server Software:</b> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</p>";
echo "<p><b>Document Root:</b> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</p>";

// 2. Cek Session Configuration
echo "<h2>2. Session Configuration</h2>";
echo "<p><b>session.save_handler:</b> " . ini_get('session.save_handler') . "</p>";
echo "<p><b>session.save_path:</b> " . ini_get('session.save_path') . "</p>";
echo "<p><b>session.cookie_secure:</b> " . (ini_get('session.cookie_secure') ? 'true' : 'false') . "</p>";
echo "<p><b>session.cookie_httponly:</b> " . (ini_get('session.cookie_httponly') ? 'true' : 'false') . "</p>";
echo "<p><b>session.cookie_samesite:</b> " . ini_get('session.cookie_samesite') . "</p>";

// 3. Cek file permissions
echo "<h2>3. File Permissions</h2>";
$paths = [
    '../storage' => 'Storage folder',
    '../storage/framework' => 'Framework folder',
    '../storage/framework/sessions' => 'Sessions folder',
    '../storage/logs' => 'Logs folder',
    '../bootstrap/cache' => 'Bootstrap cache',
];

// Untuk struktur production (folder terpisah)
$altPaths = [
    '../kutkatha/storage' => 'Storage folder (production)',
    '../kutkatha/storage/framework' => 'Framework folder (production)',
    '../kutkatha/storage/framework/sessions' => 'Sessions folder (production)',
    '../kutkatha/storage/logs' => 'Logs folder (production)',
    '../kutkatha/bootstrap/cache' => 'Bootstrap cache (production)',
];

function checkPath($path, $label) {
    $fullPath = __DIR__ . '/' . $path;
    if (file_exists($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $writable = is_writable($fullPath) ? '✅ Writable' : '❌ Not Writable';
        echo "<p><b>{$label}:</b> {$perms} - {$writable} ({$fullPath})</p>";
        return true;
    }
    return false;
}

$foundStructure = false;
foreach ($paths as $path => $label) {
    if (checkPath($path, $label)) {
        $foundStructure = true;
    }
}

if (!$foundStructure) {
    echo "<p><i>Standard paths not found, checking production structure...</i></p>";
    foreach ($altPaths as $path => $label) {
        checkPath($path, $label);
    }
}

// 4. Cek .env
echo "<h2>4. Environment Check</h2>";
$envPaths = [
    '../.env' => 'Standard .env',
    '../kutkatha/.env' => 'Production .env'
];

foreach ($envPaths as $path => $label) {
    $fullPath = __DIR__ . '/' . $path;
    if (file_exists($fullPath)) {
        echo "<p><b>{$label}:</b> ✅ EXISTS</p>";

        // Baca beberapa config penting (JANGAN tampilkan password!)
        $env = file_get_contents($fullPath);

        // Parse beberapa variable penting
        preg_match('/APP_ENV=(.*)/', $env, $appEnv);
        preg_match('/APP_DEBUG=(.*)/', $env, $appDebug);
        preg_match('/SESSION_DRIVER=(.*)/', $env, $sessionDriver);
        preg_match('/SESSION_DOMAIN=(.*)/', $env, $sessionDomain);
        preg_match('/SESSION_SECURE_COOKIE=(.*)/', $env, $sessionSecure);

        echo "<ul>";
        echo "<li>APP_ENV: " . ($appEnv[1] ?? 'NOT SET') . "</li>";
        echo "<li>APP_DEBUG: " . ($appDebug[1] ?? 'NOT SET') . "</li>";
        echo "<li>SESSION_DRIVER: " . ($sessionDriver[1] ?? 'NOT SET') . "</li>";
        echo "<li>SESSION_DOMAIN: " . ($sessionDomain[1] ?? 'NOT SET') . "</li>";
        echo "<li>SESSION_SECURE_COOKIE: " . ($sessionSecure[1] ?? 'NOT SET') . "</li>";
        echo "</ul>";
    }
}

// 5. Cek Cookies yang diterima
echo "<h2>5. Cookies Received</h2>";
if (!empty($_COOKIE)) {
    echo "<ul>";
    foreach ($_COOKIE as $name => $value) {
        // Jangan tampilkan value penuh untuk keamanan
        $displayValue = strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value;
        echo "<li><b>{$name}:</b> {$displayValue}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ No cookies received!</p>";
}

// 6. Cek Headers
echo "<h2>6. Request Headers</h2>";
echo "<ul>";
$importantHeaders = ['HTTP_HOST', 'HTTPS', 'REQUEST_SCHEME', 'HTTP_X_FORWARDED_PROTO'];
foreach ($importantHeaders as $header) {
    $value = $_SERVER[$header] ?? 'NOT SET';
    echo "<li><b>{$header}:</b> {$value}</li>";
}
echo "</ul>";

// 7. Test Session
echo "<h2>7. Session Test</h2>";
session_start();
$_SESSION['test_time'] = date('Y-m-d H:i:s');
$_SESSION['test_value'] = 'debug_' . rand(1000, 9999);
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session test value set: " . $_SESSION['test_value'] . "</p>";
echo "<p>Session file/data should be saved now.</p>";

// 8. Cek Database Connection (tanpa Laravel)
echo "<h2>8. Database Connection Test</h2>";
// Parse .env untuk DB credentials
$envPath = file_exists(__DIR__ . '/../.env') ? __DIR__ . '/../.env' : __DIR__ . '/../kutkatha/.env';
if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    preg_match('/DB_HOST=(.*)/', $env, $dbHost);
    preg_match('/DB_DATABASE=(.*)/', $env, $dbName);
    preg_match('/DB_USERNAME=(.*)/', $env, $dbUser);
    preg_match('/DB_PASSWORD="?(.*?)"?\s*$/', $env, $dbPass);

    try {
        $host = trim($dbHost[1] ?? 'localhost');
        $name = trim($dbName[1] ?? '');
        $user = trim($dbUser[1] ?? '');
        $pass = trim($dbPass[1] ?? '');

        $pdo = new PDO("mysql:host={$host};dbname={$name}", $user, $pass);
        echo "<p>✅ Database connection successful!</p>";

        // Cek tabel sessions
        $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Sessions table exists</p>";

            // Hitung jumlah session
            $count = $pdo->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
            echo "<p>Total sessions in DB: {$count}</p>";
        } else {
            echo "<p>❌ Sessions table NOT FOUND! Run: php artisan session:table && php artisan migrate</p>";
        }

        // Cek tabel users
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            echo "<p>✅ Users table exists ({$count} users)</p>";
        }

    } catch (PDOException $e) {
        echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<p style='color:red;'><b>⚠️ HAPUS FILE INI SETELAH SELESAI DEBUG!</b></p>";
echo "<p>File: " . __FILE__ . "</p>";
