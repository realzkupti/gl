<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Company;

echo "=== Fix Company 2 Password ===" . PHP_EOL . PHP_EOL;

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

$newPassword = 'Bplus2025';

echo "Testing connection with password: Bplus2025" . PHP_EOL;

try {
    $dsn = "sqlsrv:Server={$company->db_host},{$company->db_port};Database={$company->db_name}";
    $pdo = new PDO($dsn, $company->db_username, $newPassword);
    $pdo->query("SELECT 1");
    echo "✓ Connection test successful!" . PHP_EOL;

    // Encrypt and save
    $company->db_password = encrypt($newPassword);
    $company->save();

    echo "✓ Password encrypted and saved successfully!" . PHP_EOL;

    // Verify
    $verifyCompany = Company::on('pgsql')->find(2);
    $decryptedPassword = decrypt($verifyCompany->db_password);

    if ($decryptedPassword === $newPassword) {
        echo "✓ Verification successful - password can be decrypted correctly!" . PHP_EOL;
    } else {
        echo "✗ Verification failed - decrypted password doesn't match!" . PHP_EOL;
    }

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Done!" . PHP_EOL;
