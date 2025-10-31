<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Company;

echo "=== Fix All Company Passwords ===" . PHP_EOL . PHP_EOL;

$companies = Company::on('pgsql')->get();

if ($companies->isEmpty()) {
    echo "No companies found!" . PHP_EOL;
    exit(1);
}

echo "Found {$companies->count()} companies" . PHP_EOL . PHP_EOL;

foreach ($companies as $company) {
    echo "Company {$company->id}: {$company->label}" . PHP_EOL;
    echo "  Key: {$company->key}" . PHP_EOL;
    echo "  Database: {$company->db_name}" . PHP_EOL;

    // Try to decrypt
    try {
        $password = decrypt($company->db_password);
        echo "  ✓ Password decryption: OK" . PHP_EOL;

        // Test connection
        try {
            $dsn = "sqlsrv:Server={$company->db_host},{$company->db_port};Database={$company->db_name}";
            $pdo = new PDO($dsn, $company->db_username, $password);
            $pdo->query("SELECT 1");
            echo "  ✓ Connection test: OK" . PHP_EOL;
        } catch (\Exception $e) {
            echo "  ✗ Connection test FAILED: {$e->getMessage()}" . PHP_EOL;
        }

    } catch (\Exception $e) {
        echo "  ✗ Password decryption FAILED: {$e->getMessage()}" . PHP_EOL;

        // Try using the encrypted string as plain text
        $plainPassword = $company->db_password;

        echo "  → Trying to use as plain text..." . PHP_EOL;

        try {
            $dsn = "sqlsrv:Server={$company->db_host},{$company->db_port};Database={$company->db_name}";
            $pdo = new PDO($dsn, $company->db_username, $plainPassword);
            $pdo->query("SELECT 1");
            echo "  ✓ Connection test with plain text: OK" . PHP_EOL;

            // Re-encrypt
            $company->db_password = encrypt($plainPassword);
            $company->save();

            echo "  ✓ Password re-encrypted successfully!" . PHP_EOL;

        } catch (\Exception $e) {
            echo "  ✗ Connection with plain text FAILED" . PHP_EOL;
            echo "  ✗ Please manually update password for this company" . PHP_EOL;
        }
    }

    echo PHP_EOL;
}

echo "Done!" . PHP_EOL;
