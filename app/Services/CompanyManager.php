<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class CompanyManager
{
    protected static $cache;

    public static function listCompanies(): array
    {
        if (!is_null(self::$cache)) return self::$cache;

        $path = base_path('config/companies.json');
        if (!file_exists($path)) return [];
        $raw = file_get_contents($path);
        $data = json_decode($raw, true) ?: [];

        // Expand ${ENV} placeholders using current env values
        foreach ($data as $key => &$cfg) {
            foreach ($cfg as $k => $v) {
                if (is_string($v)) {
                    $cfg[$k] = preg_replace_callback('/\$\{([A-Z0-9_]+)\}/i', function ($m) {
                        return env($m[1], '');
                    }, $v);
                }
            }
        }
        unset($cfg);

        self::$cache = $data;
        return $data;
    }

    // Allow external callers to clear cached company list
    public static function reset(): void
    {
        self::$cache = null;
    }

    public static function getSelectedKey(?string $fallback = 'default'): ?string
    {
        return session('company.key', $fallback);
    }

    public static function getSelectedLabel(): ?string
    {
        $key = self::getSelectedKey();
        $all = self::listCompanies();
        return $all[$key]['label'] ?? $key;
    }

    public static function apply(?string $key): void
    {
        $companies = self::listCompanies();
        if (!$key || !isset($companies[$key])) {
            $key = 'default';
        }

        $cfg = $companies[$key];
        $driver = $cfg['driver'] ?? 'mysql';

        $connectionName = 'company_'. $key;

        $connection = [
            'driver' => $driver,
            'host' => $cfg['host'] ?? '127.0.0.1',
            'port' => $cfg['port'] ?? null,
            'database' => $cfg['database'] ?? null,
            'username' => $cfg['username'] ?? null,
            'password' => $cfg['password'] ?? null,
            'charset' => $cfg['charset'] ?? ($driver === 'mysql' ? 'utf8mb4' : 'utf8'),
            'collation' => $cfg['collation'] ?? null,
            'prefix' => '',
            'prefix_indexes' => true,
        ];

        // Merge typical driver defaults
        if ($driver === 'mysql') {
            $connection += [
                'unix_socket' => env('DB_SOCKET', ''),
                'strict' => true,
                'engine' => null,
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]) : [],
            ];
        } elseif ($driver === 'pgsql') {
            $connection += [
                'sslmode' => 'prefer',
            ];
        } elseif ($driver === 'sqlsrv') {
            // สำคัญสำหรับ ODBC Driver 18 (ค่า dev/test ให้ต่อได้ทันที)
            $connection += [
                // ใช้ค่าจาก companies.json ถ้ามี มิฉะนั้นใช้ค่าเริ่มต้นที่ต่อได้เลย
                'encrypt'                  => $cfg['encrypt']                  ?? 'yes',
                'TrustServerCertificate'   => $cfg['TrustServerCertificate']   ?? true,
                'MultipleActiveResultSets' => $cfg['MultipleActiveResultSets'] ?? true,
                // (ออปชัน) ติด tag แอปไว้ช่วย debug connection บนเซิร์ฟเวอร์
                'application_name'         => $cfg['application_name']         ?? config('app.name', 'Laravel'),
            ];
        }

        // Register and set default connection for this request
        Config::set('database.connections.' . $connectionName, $connection);
        Config::set('database.default', $connectionName);

        // Remember selection in session
        session(['company.key' => $key, 'company.connection' => $connectionName]);
    }
}
