<aside
    :class="sidebarToggle ? 'translate-x-0 xl:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 xl:static xl:translate-x-0 dark:border-gray-800 dark:bg-black"
    @click.outside="sidebarToggle = false"
>
    <!-- SIDEBAR HEADER -->
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="sidebar-header flex items-center gap-2 pt-8 pb-7"
    >
        <a href="{{ route('tailadmin.dashboard') }}">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="dark:hidden" src="{{ asset('tailadmin/src/images/logo/logo.svg') }}" alt="Logo" />
                <img
                    class="hidden dark:block"
                    src="{{ asset('tailadmin/src/images/logo/logo-dark.svg') }}"
                    alt="Logo"
                />
            </span>

            <img
                class="logo-icon"
                :class="sidebarToggle ? 'xl:block' : 'hidden'"
                src="{{ asset('tailadmin/src/images/logo/logo-icon.svg') }}"
                alt="Logo"
            />
        </a>
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <!-- Sidebar Menu -->
        <nav x-data="{selected: $persist('Dashboard')}">
            <!-- Menu Group -->
            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span
                        class="menu-group-title"
                        :class="sidebarToggle ? 'xl:hidden' : ''"
                    >
                        เมนู
                    </span>

                    <svg
                        :class="sidebarToggle ? 'xl:block hidden' : 'hidden'"
                        class="menu-group-icon mx-auto fill-current"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                            fill="currentColor"
                        />
                    </svg>
                </h3>

                <ul class="mb-6 flex flex-col gap-1">
                    @foreach($userMenus ?? [] as $menu)
                        @if(empty($menu['children']))
                            <!-- Single Menu Item -->
                            <li>
                                <a
                                    href="{{ $menu['route'] ? route($menu['route']) : ($menu['url'] ?? '#') }}"
                                    class="menu-item group"
                                    :class="page === '{{ $menu['key'] }}' ? 'menu-item-active' : 'menu-item-inactive'"
                                >
                                    @php
                                        $iconName = $menu['icon'] ?? '';
                                        $iconSvg = match($iconName) {
                                            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                                            'dashboard' => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                                            'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>',
                                            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
                                            'chart' => '<path d="M3 3v18h18M7 16l4-4 4 4 6-6"/>',
                                            'document' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>',
                                            'folder' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
                                            'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
                                            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
                                            'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
                                            'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
                                            'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
                                            'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>',
                                            'calculator' => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 6h8M8 10h8M8 14h4M8 18h6"/>',
                                            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
                                            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
                                            'trash' => '<path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
                                            'edit' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
                                            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
                                            'filter' => '<path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>',
                                            'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
                                            'check' => '<path d="M20 6 9 17l-5-5"/>',
                                            'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
                                            'plus' => '<path d="M5 12h14M12 5v14"/>',
                                            'minus' => '<path d="M5 12h14"/>',
                                            'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
                                            'eye-off' => '<path d="m9.88 9.88a3 3 0 1 0 4.24 4.24M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61M2 2l20 20"/>',
                                            'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                                            'unlock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>',
                                            'star' => '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
                                            'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
                                            'bookmark' => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
                                            'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/>',
                                            'tag' => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82zM7 7h.01"/>',
                                            'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/>',
                                            'server' => '<rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><path d="M6 6h.01M6 18h.01"/>',
                                            'shopping-cart' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
                                            'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>',
                                            'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                                            'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
                                            'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
                                            'printer' => '<path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/>',
                                            'location' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
                                            'globe' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/>',
                                            'refresh' => '<path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>',
                                            'cube' => '<path d="m12.83 2.18 8.5 3.69a1 1 0 0 1 0 1.83L12.83 11.4a1 1 0 0 1-.66 0L3.67 7.7a1 1 0 0 1 0-1.83l8.5-3.69a1 1 0 0 1 .66 0z"/><path d="M12 12v10"/><path d="m3 7 9 4m9-4-9 4"/>',
                                            'lightning' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
                                            'pencil' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
                                            'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                                            'cog' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
                                            'code' => '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
                                            'terminal' => '<path d="m4 17 6-6-6-6M12 19h8"/>',
                                            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>',
                                            'alert-circle' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
                                            'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
                                            'help-circle' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>',
                                            default => '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>',
                                        };
                                    @endphp
                                    <svg
                                        :class="page === '{{ $menu['key'] }}' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                        {!! $iconSvg !!}
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                        {{ $menu['label'] }}
                                    </span>
                                </a>
                            </li>
                        @else
                            <!-- Menu with Submenu -->
                            <li x-data="{open: false}">
                                <button
                                    @click="open = !open"
                                    class="menu-item group w-full"
                                    :class="page === '{{ $menu['key'] }}' ? 'menu-item-active' : 'menu-item-inactive'"
                                >
                                    @php
                                        $iconName = $menu['icon'] ?? '';
                                        $iconSvg = match($iconName) {
                                            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                                            'dashboard' => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                                            'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>',
                                            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
                                            'chart' => '<path d="M3 3v18h18M7 16l4-4 4 4 6-6"/>',
                                            'document' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>',
                                            'folder' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
                                            'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
                                            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
                                            'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
                                            'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
                                            'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
                                            'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>',
                                            'calculator' => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 6h8M8 10h8M8 14h4M8 18h6"/>',
                                            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
                                            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
                                            'trash' => '<path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
                                            'edit' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
                                            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
                                            'filter' => '<path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>',
                                            'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
                                            'check' => '<path d="M20 6 9 17l-5-5"/>',
                                            'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
                                            'plus' => '<path d="M5 12h14M12 5v14"/>',
                                            'minus' => '<path d="M5 12h14"/>',
                                            'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
                                            'eye-off' => '<path d="m9.88 9.88a3 3 0 1 0 4.24 4.24M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61M2 2l20 20"/>',
                                            'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                                            'unlock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>',
                                            'star' => '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
                                            'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
                                            'bookmark' => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
                                            'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/>',
                                            'tag' => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82zM7 7h.01"/>',
                                            'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/>',
                                            'server' => '<rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><path d="M6 6h.01M6 18h.01"/>',
                                            'shopping-cart' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
                                            'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>',
                                            'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                                            'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
                                            'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
                                            'printer' => '<path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/>',
                                            'location' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
                                            'globe' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/>',
                                            'refresh' => '<path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>',
                                            'cube' => '<path d="m12.83 2.18 8.5 3.69a1 1 0 0 1 0 1.83L12.83 11.4a1 1 0 0 1-.66 0L3.67 7.7a1 1 0 0 1 0-1.83l8.5-3.69a1 1 0 0 1 .66 0z"/><path d="M12 12v10"/><path d="m3 7 9 4m9-4-9 4"/>',
                                            'lightning' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
                                            'pencil' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
                                            'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                                            'cog' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
                                            'code' => '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
                                            'terminal' => '<path d="m4 17 6-6-6-6M12 19h8"/>',
                                            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>',
                                            'alert-circle' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
                                            'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
                                            'help-circle' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>',
                                            default => '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>',
                                        };
                                    @endphp
                                    <svg
                                        :class="page === '{{ $menu['key'] }}' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                        {!! $iconSvg !!}
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                        {{ $menu['label'] }}
                                    </span>
                                    <svg :class="[open ? 'rotate-180' : '', sidebarToggle ? 'xl:hidden' : '']" class="ml-auto fill-current transition-transform duration-200" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z" fill="currentColor"/>
                                    </svg>
                                </button>
                                <ul x-show="open" x-collapse class="mt-2 mb-2 ml-8 flex flex-col gap-1">
                                    @foreach($menu['children'] as $child)
                                        <li>
                                            <a
                                                href="{{ $child['route'] ? route($child['route']) : ($child['url'] ?? '#') }}"
                                                class="block rounded py-2 px-4 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                                :class="page === '{{ $child['key'] }}' ? 'text-brand-500 font-medium' : 'text-gray-600 dark:text-gray-400'"
                                            >
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach

                    <!-- Legacy: Keep cheque menu for now (will be replaced by database menu) -->
                    @php( $canCheque = \App\Support\Perm::can('cheque','view') )
                    @if ($canCheque && !collect($userMenus ?? [])->where('key', 'cheque')->count())
                    <li x-data="{open: {{ in_array($page ?? '', ['cheque-print', 'cheque-designer', 'cheque-reports', 'cheque-branches', 'cheque-settings']) ? 'true' : 'false' }} }">
                        <button
                            @click="open = !open"
                            class="menu-item group w-full"
                            :class="['cheque-print', 'cheque-designer', 'cheque-reports', 'cheque-branches', 'cheque-settings'].includes(page) ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <svg
                                :class="['cheque-print', 'cheque-designer', 'cheque-reports', 'cheque-branches', 'cheque-settings'].includes(page) ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M3 10H21M7 15H8M12 15H13M6 19H18C19.6569 19 21 17.6569 21 16V8C21 6.34315 19.6569 5 18 5H6C4.34315 5 3 6.34315 3 8V16C3 17.6569 4.34315 19 6 19Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'xl:hidden' : ''"
                            >
                                ระบบเช็ค
                            </span>

                            <svg
                                :class="[open ? 'rotate-180' : '', sidebarToggle ? 'xl:hidden' : '']"
                                class="ml-auto fill-current transition-transform duration-200"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </button>

                        <!-- Submenu -->
                        <ul x-show="open" x-collapse class="mt-2 mb-2 ml-8 flex flex-col gap-1">
                            <li>
                                <a
                                    href="{{ route('cheque.print') }}"
                                    class="block rounded py-2 px-4 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    :class="page === 'cheque-print' ? 'text-brand-500 font-medium' : 'text-gray-600 dark:text-gray-400'"
                                >
                                    พิมพ์เช็ค
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('cheque.designer') }}"
                                    class="block rounded py-2 px-4 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    :class="page === 'cheque-designer' ? 'text-brand-500 font-medium' : 'text-gray-600 dark:text-gray-400'"
                                >
                                    ออกแบบ & ปรับแต่ง
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('cheque.reports') }}"
                                    class="block rounded py-2 px-4 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    :class="page === 'cheque-reports' ? 'text-brand-500 font-medium' : 'text-gray-600 dark:text-gray-400'"
                                >
                                    รายงาน
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('cheque.branches') }}"
                                    class="block rounded py-2 px-4 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    :class="page === 'cheque-branches' ? 'text-brand-500 font-medium' : 'text-gray-600 dark:text-gray-400'"
                                >
                                    จัดการสาขา
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('cheque.settings') }}"
                                    class="block rounded py-2 px-4 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    :class="page === 'cheque-settings' ? 'text-brand-500 font-medium' : 'text-gray-600 dark:text-gray-400'"
                                >
                                    ตั้งค่าระบบ
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    <!-- Menu Item ผู้ใช้และสิทธิ -->
                    @php( $isAdmin = auth()->check() && (auth()->user()->email === 'admin@local') )
                    @if ($isAdmin)
                    <li>
                        <a
                            href="{{ route('admin.users') }}"
                            class="menu-item group"
                            :class="page === 'users' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <svg
                                :class="page === 'users' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.3503 17.623 3.8507 18.1676 4.55231C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89317 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'xl:hidden' : ''"
                            >
                                ผู้ใช้และสิทธิ
                            </span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('admin.user-approvals') }}"
                            class="menu-item group"
                            :class="page === 'user-approvals' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <svg
                                :class="page === 'user-approvals' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">อนุมัติผู้ใช้</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('admin.menus') }}"
                            class="menu-item group"
                            :class="page === 'menus' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <svg
                                :class="page === 'menus' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path d="M4 6H20M4 12H20M4 18H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">ตั้งค่าเมนู</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Accounting Group -->
            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">บัญชี</span>
                </h3>

                <ul class="mb-6 flex flex-col gap-1">
                    <li>
                        <a href="{{ route('trial-balance.plain') }}" class="menu-item group" :class="page === 'trial-balance-plain' ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="page === 'trial-balance-plain' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 6C4 4.89543 4.89543 4 6 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V6Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 8H16M8 12H16M8 16H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">งบทดลอง (ธรรมดา)</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Demo Components Group -->
            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span
                        class="menu-group-title"
                        :class="sidebarToggle ? 'xl:hidden' : ''"
                    >
                        Demo Components
                    </span>
                </h3>

                <ul class="mb-6 flex flex-col gap-1">
                    <li>
                        <a
                            href="{{ route('tailadmin.analytics') }}"
                            class="menu-item group"
                            :class="page === 'analytics' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('tailadmin.alerts') }}"
                            class="menu-item group"
                            :class="page === 'alerts' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Alerts</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('tailadmin.buttons') }}"
                            class="menu-item group"
                            :class="page === 'buttons' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Buttons</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('tailadmin.cards') }}"
                            class="menu-item group"
                            :class="page === 'cards' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Cards</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('tailadmin.tables') }}"
                            class="menu-item group"
                            :class="page === 'tables' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Tables</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('tailadmin.forms') }}"
                            class="menu-item group"
                            :class="page === 'forms' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Forms</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Admin Group -->
            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">ผู้ดูแล</span>
                </h3>
                <ul class="mb-6 flex flex-col gap-1">
                    <li>
                        <a href="{{ route('admin.user-approvals') }}" class="menu-item group" :class="page === 'admin-user-approvals' ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="page === 'admin-user-approvals' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7a5 5 0 1 1 10 0A5 5 0 0 1 5 7Zm-3 14a8 8 0 1 1 16 0H2Z" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">อนุมัติผู้ใช้</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>
