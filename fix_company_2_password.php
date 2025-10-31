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

// Try to decrypt
try {
    $password = decrypt($company->db_password);
    echo "✓ Password decryption: OK" . PHP_EOL;
    echo "✓ Current password is valid" . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ Password decryption FAILED: {$e->getMessage()}" . PHP_EOL;
    echo PHP_EOL;

    // Ask for new password
    echo "Please enter the database password for this company: ";
    $newPassword = trim(fgets(STDIN));

    if (empty($newPassword)) {
        echo "Password cannot be empty!" . PHP_EOL;
        exit(1);
    }

    // Test connection with new password
    echo PHP_EOL . "Testing connection..." . PHP_EOL;

    try {
        $dsn = "sqlsrv:Server={$company->db_host},{$company->db_port};Database={$company->db_name}";
        $pdo = new PDO($dsn, $company->db_username, $newPassword);
        $pdo->query("SELECT 1");
        echo "✓ Connection test successful!" . PHP_EOL;

        // Save encrypted password
        $company->db_password = encrypt($newPassword);
        $company->save();

        echo "✓ Password updated and encrypted successfully!" . PHP_EOL;

    } catch (\Exception $e) {
        echo "✗ Connection test failed: {$e->getMessage()}" . PHP_EOL;
        exit(1);
    }
}

echo PHP_EOL . "Done!" . PHP_EOL;
