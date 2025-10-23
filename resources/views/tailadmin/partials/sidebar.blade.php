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
                    <!-- Menu Item Dashboard -->
                    <li>
                        <a
                            href="{{ route('tailadmin.dashboard') }}"
                            class="menu-item group"
                            :class="page === 'dashboard' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <svg
                                :class="page === 'dashboard' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                                    fill="currentColor"
                                />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'xl:hidden' : ''"
                            >
                                Dashboard
                            </span>
                        </a>
                    </li>

                    <!-- Menu Item งบทดลอง -->
                    <li>
                        <a
                            href="{{ route('trial-balance.branch') }}"
                            class="menu-item group"
                            :class="page === 'trial-balance' ? 'menu-item-active' : 'menu-item-inactive'"
                        >
                            <svg
                                :class="page === 'trial-balance' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5M12 12H15M12 16H15M9 12H9.01M9 16H9.01"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'xl:hidden' : ''"
                            >
                                งบทดลอง
                            </span>
                        </a>
                    </li>

                    <!-- Menu Item ระบบเช็ค (with submenu) -->
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

                    <!-- Menu Item ผู้ใช้และสิทธิ -->
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
        </nav>
    </div>
</aside>
