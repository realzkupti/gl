<aside
    :class="sidebarToggle ? 'translate-x-0 xl:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 xl:static xl:translate-x-0 dark:border-gray-800 dark:bg-black"
    @click.outside="sidebarToggle = false"
>
    <!-- SIDEBAR HEADER -->
    <div class="sidebar-header flex flex-col gap-3 pt-8 pb-7">
        <!-- Logo -->
        <a href="{{ route('tailadmin.dashboard') }}" class="flex items-center justify-center">
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

        <!-- Current Company Display (Only for Bplus system users) -->
        @php
            $currentCompany = auth()->user()?->getCurrentCompany();
        @endphp
        @if($currentCompany)
            <button
                onclick="companySwitcher.openModal()"
                class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                :class="sidebarToggle ? 'hidden xl:flex xl:justify-center' : ''"
            >
                <svg class="w-5 h-5 text-brand-600 dark:text-brand-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <div class="flex-1 text-left" :class="sidebarToggle ? 'xl:hidden' : ''">
                    <div class="text-xs text-gray-500 dark:text-gray-400">บริษัทปัจจุบัน</div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $currentCompany->label }}</div>
                </div>
                <svg class="w-4 h-4 text-gray-400" :class="sidebarToggle ? 'xl:hidden' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                </svg>
            </button>
        @endif
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <!-- Sidebar Menu -->
        <nav x-data="{selected: $persist('Dashboard')}">
            @foreach($userMenus ?? [] as $groupName => $menus)
                <!-- Menu Group -->
                <div>
                    <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                        <span
                            class="menu-group-title"
                            :class="sidebarToggle ? 'xl:hidden' : ''"
                        >
                            {{ $groupName }}
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
                        @foreach($menus as $menu)
                            @if(empty($menu['children']))
                                <!-- Single Menu Item -->
                                <li>
                                    <a
                                        href="{{ ($menu['route'] && \Illuminate\Support\Facades\Route::has($menu['route'])) ? route($menu['route']) : ($menu['url'] ?? '#') }}"
                                        class="menu-item group"
                                        :class="page === '{{ $menu['key'] }}' ? 'menu-item-active' : 'menu-item-inactive'"
                                    >
                                        @php
                                            // Icons loaded from centralized config
                                            $iconName = $menu['icon'] ?? '';
                                            $icons = config('icons');
                                            $iconSvg = isset($icons[$iconName])
                                                ? '<path stroke-linecap="round" stroke-linejoin="round" d="' . $icons[$iconName]['path'] . '"/>'
                                                : '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>';
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
                                            // Icons loaded from centralized config
                                            $iconName = $menu['icon'] ?? '';
                                            $icons = config('icons');
                                            $iconSvg = isset($icons[$iconName])
                                                ? '<path stroke-linecap="round" stroke-linejoin="round" d="' . $icons[$iconName]['path'] . '"/>'
                                                : '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>';
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
                                                    href="{{ ($child['route'] && \Illuminate\Support\Facades\Route::has($child['route'])) ? route($child['route']) : ($child['url'] ?? '#') }}"
                                                    class="block rounded py-2 px-4 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                                    :class="page === '{{ $child['key'] }}' ? 'text-brand-500 font-medium' : 'text-gray-600 dark:text-gray-400'"
                                                >
                                                    <span class="inline-block w-5 shrink-0"></span>
                                                    {{ $child['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </div>
</aside>
