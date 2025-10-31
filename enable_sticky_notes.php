<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Menu;

echo "🔍 Checking System menus...\n\n";

// Find admin menus
$adminMenus = Menu::whereIn('key', ['admin_menus', 'admin_user_permissions', 'admin_department_permissions'])
    ->orWhere('route', 'like', 'admin.%')
    ->where('system_type', 1)
    ->orderBy('sort_order')
    ->get();

echo "Found " . $adminMenus->count() . " admin menus:\n";
foreach ($adminMenus as $menu) {
    echo sprintf(
        "ID: %d | Key: %s | Label: %s | Route: %s | Parent: %s | has_sticky_note: %s\n",
        $menu->id,
        $menu->key,
        $menu->label,
        $menu->route ?? 'NULL',
        $menu->parent_id ?? 'NULL',
        $menu->has_sticky_note ? 'YES' : 'NO'
    );
}

echo "\n📝 Enabling sticky notes for admin menus with routes...\n";

// Enable sticky notes for menus that have routes
$updated = Menu::whereIn('key', ['admin_menus', 'admin_user_permissions', 'admin_department_permissions'])
    ->whereNotNull('route')
    ->update(['has_sticky_note' => true]);

echo "✅ Updated $updated menu(s)\n\n";

// Show updated results
echo "📊 Updated menu status:\n";
$updatedMenus = Menu::whereIn('key', ['admin_menus', 'admin_user_permissions', 'admin_department_permissions'])
    ->orderBy('sort_order')
    ->get();

foreach ($updatedMenus as $menu) {
    echo sprintf(
        "ID: %d | Key: %s | Label: %s | Route: %s | has_sticky_note: %s\n",
        $menu->id,
        $menu->key,
        $menu->label,
        $menu->route ?? 'NULL',
        $menu->has_sticky_note ? 'YES ✓' : 'NO'
    );
}
