<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$menus = \App\Models\Menu::select('id', 'label', 'key', 'parent_id', 'system_type', 'sort_order')
    ->orderBy('system_type')
    ->orderBy('parent_id', 'asc')
    ->orderBy('sort_order')
    ->get();

echo "=== Menu Structure ===\n\n";

// Group by system_type
$systemMenus = $menus->where('system_type', 1);
$bplusMenus = $menus->where('system_type', 2);

echo "System Menus (system_type=1):\n";
echo str_repeat('-', 80) . "\n";
foreach ($systemMenus as $menu) {
    $indent = $menu->parent_id ? '  └─ ' : '';
    $parent = $menu->parent_id ? " (parent_id: {$menu->parent_id})" : ' (PARENT)';
    echo sprintf("%s[%d] %s%s\n", $indent, $menu->id, $menu->label, $parent);
}

echo "\nBplus Menus (system_type=2):\n";
echo str_repeat('-', 80) . "\n";
foreach ($bplusMenus as $menu) {
    $indent = $menu->parent_id ? '  └─ ' : '';
    $parent = $menu->parent_id ? " (parent_id: {$menu->parent_id})" : ' (PARENT)';
    echo sprintf("%s[%d] %s%s\n", $indent, $menu->id, $menu->label, $parent);
}

echo "\n=== Summary ===\n";
echo "Total menus: " . $menus->count() . "\n";
echo "System menus: " . $systemMenus->count() . "\n";
echo "Bplus menus: " . $bplusMenus->count() . "\n";
echo "Parent menus: " . $menus->whereNull('parent_id')->count() . "\n";
echo "Child menus: " . $menus->whereNotNull('parent_id')->count() . "\n";
