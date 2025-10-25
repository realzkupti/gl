<!DOCTYPE html>
<html>
<head>
    <title>Debug Menus</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h1 { color: #4ec9b0; }
        h2 { color: #569cd6; margin-top: 30px; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .info { background: #1e3a5f; padding: 10px; border-left: 4px solid #4ec9b0; margin: 10px 0; }
        .error { background: #5f1e1e; padding: 10px; border-left: 4px solid #f48771; margin: 10px 0; }
        .success { background: #1e5f1e; padding: 10px; border-left: 4px solid #4ec97a; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #3e3e3e; }
        th { background: #2d2d2d; color: #4ec9b0; }
        tr:nth-child(even) { background: #252525; }
    </style>
</head>
<body>
    <h1>🔍 Debug Menu System</h1>

    @php
        $userId = Auth::id();
        $userEmail = Auth::user()->email ?? 'N/A';
        $isAdmin = $userEmail === 'admin@local';
        $conn = DB::connection('pgsql');
    @endphp

    <div class="info">
        <strong>User Info:</strong><br>
        ID: {{ $userId }}<br>
        Email: {{ $userEmail }}<br>
        Is Admin: {{ $isAdmin ? 'Yes' : 'No' }}
    </div>

    <h2>1. User Roles</h2>
    @php
        $roles = $conn->table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $userId)
            ->select('r.id', 'r.name')
            ->get();
    @endphp

    @if($roles->count() > 0)
        <div class="success">Found {{ $roles->count() }} role(s)</div>
        <table>
            <tr><th>Role ID</th><th>Role Name</th></tr>
            @foreach($roles as $role)
                <tr><td>{{ $role->id }}</td><td>{{ $role->name }}</td></tr>
            @endforeach
        </table>
    @else
        <div class="error">No roles found for this user</div>
    @endif

    <h2>2. Menu Groups</h2>
    @php
        $menuGroups = $conn->table('menu_groups')->orderBy('sort_order')->get();
    @endphp

    <div class="success">Found {{ $menuGroups->count() }} menu group(s)</div>
    <table>
        <tr><th>ID</th><th>Key</th><th>Label</th><th>Sort Order</th><th>Active</th><th>Default</th></tr>
        @foreach($menuGroups as $group)
            <tr>
                <td>{{ $group->id }}</td>
                <td>{{ $group->key }}</td>
                <td>{{ $group->label }}</td>
                <td>{{ $group->sort_order }}</td>
                <td>{{ $group->is_active ? '✓' : '✗' }}</td>
                <td>{{ $group->is_default ? '✓' : '✗' }}</td>
            </tr>
        @endforeach
    </table>

    <h2>3. All Menus (with group info)</h2>
    @php
        $allMenus = $conn->table('menus as m')
            ->leftJoin('menu_groups as g', 'g.id', '=', 'm.menu_group_id')
            ->select('m.id', 'm.key', 'm.label', 'm.route', 'm.menu_group_id', 'g.label as group_label', 'm.is_active')
            ->orderBy('m.id')
            ->get();
    @endphp

    <div class="success">Found {{ $allMenus->count() }} menu(s)</div>
    <table>
        <tr><th>ID</th><th>Key</th><th>Label</th><th>Route</th><th>Group ID</th><th>Group Label</th><th>Active</th></tr>
        @foreach($allMenus as $menu)
            <tr style="{{ is_null($menu->menu_group_id) ? 'background: #5f1e1e;' : '' }}">
                <td>{{ $menu->id }}</td>
                <td>{{ $menu->key }}</td>
                <td>{{ $menu->label }}</td>
                <td>{{ $menu->route ?? 'N/A' }}</td>
                <td>{{ $menu->menu_group_id ?? 'NULL ⚠️' }}</td>
                <td>{{ $menu->group_label ?? 'N/A' }}</td>
                <td>{{ $menu->is_active ? '✓' : '✗' }}</td>
            </tr>
        @endforeach
    </table>    <h2>4. Role Menu Permissions</h2>
    @php
        $rolePerms = [];
        if ($roles->count() > 0) {
            $roleIds = $roles->pluck('id');
            $rolePerms = $conn->table('role_menu_permissions as rmp')
                ->join('menus as m', 'm.id', '=', 'rmp.menu_id')
                ->whereIn('rmp.role_id', $roleIds->all())
                ->select('m.key', 'm.label', 'rmp.can_view', 'rmp.can_create', 'rmp.can_update', 'rmp.can_delete')
                ->get();
        }
    @endphp

    @if(count($rolePerms) > 0)
        <div class="success">Found {{ count($rolePerms) }} role permission(s)</div>
        <table>
            <tr><th>Menu Key</th><th>Menu Label</th><th>View</th><th>Create</th><th>Update</th><th>Delete</th></tr>
            @foreach($rolePerms as $perm)
                <tr>
                    <td>{{ $perm->key }}</td>
                    <td>{{ $perm->label }}</td>
                    <td>{{ $perm->can_view ? '✓' : '✗' }}</td>
                    <td>{{ $perm->can_create ? '✓' : '✗' }}</td>
                    <td>{{ $perm->can_update ? '✓' : '✗' }}</td>
                    <td>{{ $perm->can_delete ? '✓' : '✗' }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="error">No role permissions found</div>
    @endif    <h2>5. User Menu Permissions (Individual)</h2>
    @php
        $userPerms = $conn->table('user_menu_permissions as ump')
            ->join('menus as m', 'm.id', '=', 'ump.menu_id')
            ->where('ump.user_id', $userId)
            ->select('m.key', 'm.label', 'ump.can_view', 'ump.can_create', 'ump.can_update', 'ump.can_delete')
            ->get();
    @endphp

    @if($userPerms->count() > 0)
        <div class="success">Found {{ $userPerms->count() }} user permission(s)</div>
        <table>
            <tr><th>Menu Key</th><th>Menu Label</th><th>View</th><th>Create</th><th>Update</th><th>Delete</th></tr>
            @foreach($userPerms as $perm)
                <tr>
                    <td>{{ $perm->key }}</td>
                    <td>{{ $perm->label }}</td>
                    <td>{{ $perm->can_view ? '✓' : '✗' }}</td>
                    <td>{{ $perm->can_create ? '✓' : '✗' }}</td>
                    <td>{{ $perm->can_update ? '✓' : '✗' }}</td>
                    <td>{{ $perm->can_delete ? '✓' : '✗' }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="info">No individual user permissions found (user relies on role permissions only)</div>
    @endif    <h2>6. Final User Menus (from Perm::getUserMenus())</h2>
    @php
        $userMenus = App\Support\Perm::getUserMenus();
    @endphp

    @if(count($userMenus) > 0)
        <div class="success">Found {{ count($userMenus) }} menu group(s) with {{ collect($userMenus)->flatten(1)->count() }} total menu(s)</div>
        <pre>{{ json_encode($userMenus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    @else
        <div class="error">❌ getUserMenus() returned empty array! This is why the sidebar is empty.</div>
    @endif

    <h2>7. Quick Test: Perm::can()</h2>
    @php
        $testMenus = ['dashboard', 'home', 'admin_users', 'trial_balance'];
        $testResults = [];
        foreach ($testMenus as $menuKey) {
            $testResults[$menuKey] = App\Support\Perm::can($menuKey, 'view');
        }
    @endphp

    <table>
        <tr><th>Menu Key</th><th>Can View?</th></tr>
        @foreach($testResults as $key => $result)
            <tr>
                <td>{{ $key }}</td>
                <td style="color: {{ $result ? '#4ec97a' : '#f48771' }}">{{ $result ? '✓ YES' : '✗ NO' }}</td>
            </tr>
        @endforeach
    </table>

    <div style="margin-top: 40px; padding: 20px; background: #2d2d2d; border-radius: 5px;">
        <strong>🔧 Troubleshooting Checklist:</strong>
        <ol>
            <li>User must have at least one role (Section 1)</li>
            <li>All menus must have menu_group_id (check Section 3 for NULL values)</li>
            <li>Role must have permissions with can_view=true (Section 4) OR user has individual permissions (Section 5)</li>
            <li>Menu groups must be active (Section 2)</li>
            <li>Menus must be active (Section 3)</li>
            <li>Final result should appear in Section 6</li>
        </ol>
    </div>
</body>
</html>
