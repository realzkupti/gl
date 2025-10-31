<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Company;
use Illuminate\Support\Facades\DB;

echo "=== Fix All Company Passwords (Quick Fix) ===" . PHP_EOL . PHP_EOL;

// Update all companies at once using raw SQL
DB::connection('pgsql')->statement("
    UPDATE sys_companies
    SET db_password = ?
    WHERE id IN (2, 3)
", [encrypt('Bplus2025')]);

echo "✓ Updated passwords for companies 2 and 3" . PHP_EOL . PHP_EOL;

// Verify
$companies = Company::on('pgsql')->whereIn('id', [2, 3])->get();

foreach ($companies as $company) {
    echo "Company {$company->id}: {$company->label}" . PHP_EOL;

    try {
        $password = decrypt($company->db_password);
        echo "  ✓ Decrypt: OK" . PHP_EOL;

        if ($password === 'Bplus2025') {
            echo "  ✓ Password matches: Bplus2025" . PHP_EOL;
        }

        // Test connection
        try {
            $dsn = "sqlsrv:Server={$company->db_host},{$company->db_port};Database={$company->db_name}";
            $pdo = new PDO($dsn, $company->db_username, $password);
            $pdo->query("SELECT 1");
            echo "  ✓ Connection: OK" . PHP_EOL;
        } catch (\Exception $e) {
            echo "  ✗ Connection failed: {$e->getMessage()}" . PHP_EOL;
        }

    } catch (\Exception $e) {
        echo "  ✗ Decrypt failed: {$e->getMessage()}" . PHP_EOL;
    }

    echo PHP_EOL;
}

echo "Done! Please refresh the page." . PHP_EOL;
