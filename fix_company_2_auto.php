<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Company;

echo "=== Fix Company 2 Password Encryption ===" . PHP_EOL . PHP_EOL;

$company = Company::on('pgsql')->find(2);

if (!$company) {
    echo "Company ID 2 not found!" . PHP_EOL;
    exit(1);
}

echo "Company: {$company->label}" . PHP_EOL;
echo "Key: {$company->key}" . PHP_EOL;
echo "Database: {$company->db_name}" . PHP_EOL;
echo "Host: {$company->db_host}" . PHP_EOL;
echo "Username: {$company->db_username}" . PHP_EOL;
echo PHP_EOL;

// Try to decrypt current password
try {
    $password = decrypt($company->db_password);
    echo "✓ Password decryption: OK" . PHP_EOL;
    echo "✓ Current password is already valid, no need to fix" . PHP_EOL;
    echo "✓ Password: " . str_repeat('*', strlen($password)) . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ Password decryption FAILED: {$e->getMessage()}" . PHP_EOL;
    echo PHP_EOL;

    // Get password from .env (assuming it's the same as DB_PASSWORD)
    $password = env('DB_PASSWORD', '');

    if (empty($password)) {
        echo "Please set DB_PASSWORD in .env file or modify this script to include the password" . PHP_EOL;
        exit(1);
    }

    echo "Using password from .env..." . PHP_EOL;

    // Test connection
    echo "Testing connection..." . PHP_EOL;

    try {
        $dsn = "sqlsrv:Server={$company->db_host},{$company->db_port};Database={$company->db_name}";
        $pdo = new PDO($dsn, $company->db_username, $password);
        $pdo->query("SELECT 1");
        echo "✓ Connection test successful!" . PHP_EOL;

        // Save encrypted password
        $company->db_password = encrypt($password);
        $company->save();

        echo "✓ Password updated and encrypted successfully!" . PHP_EOL;

    } catch (\Exception $e) {
        echo "✗ Connection test failed: {$e->getMessage()}" . PHP_EOL;
        echo "✗ Please check the password and try again" . PHP_EOL;
        exit(1);
    }
}

echo PHP_EOL . "Done!" . PHP_EOL;
