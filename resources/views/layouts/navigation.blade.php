<nav x-data="{ open: false }" class="bg-white shadow-sm border-b">
    <!-- Desktop Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 hidden md:block">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-blue-600">
                    @auth
                        {{ Auth::user()->name }}
                    @else
                        Aurum studio
                    @endauth
                </a>

                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-8 ml-10">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                        {{ request()->routeIs('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ __('Главная') }}
                    </a>
                    
                    @if (Auth::user() && Auth::user()->isSalon())
                        <a href="{{ route('salon.masters.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                            {{ request()->routeIs('salon.masters.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Мастера') }}
                        </a>
                        <a href="{{ route('salon.schedules.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                            {{ request()->routeIs('salon.schedules.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Расписание') }}
                        </a>
                    @endif
                    
                    @if (Auth::user() && Auth::user()->isMaster())
                        <a href="{{ route('schedules.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                            {{ request()->routeIs('schedules.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Расписание') }}
                        </a>
                    @endif
                    
                    @if (Auth::user())
                        <a href="{{ route('calendar.day', ['date' => now()->format('Y-m-d')]) }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                            {{ request()->routeIs('calendar.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Календарь') }}
                        </a>
                        <a href="{{ route('clients.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                            {{ request()->routeIs('clients.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Клиенты') }}
                        </a>
                        <a href="{{ route('services.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                            {{ request()->routeIs('services.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Услуги') }}
                        </a>
                        <a href="{{ route('analytics.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200
                            {{ request()->routeIs('analytics.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Статистика') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden md:flex md:items-center">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Профиль') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Выйти') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 px-4 py-2 border border-transparent rounded-md hover:bg-gray-100">Войти</a>
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-white bg-blue-600 px-4 py-2 rounded-md hover:bg-blue-700">Регистрация</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="md:hidden">
        <!-- Mobile Header - НЕ попадает под aria-hidden -->
        <div class="flex items-center justify-between h-16 px-4">
            <!-- Hamburger Menu Button -->
            <button @click="open = !open" 
                    x-show="!open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Current Page Title -->
            <div class="flex-1 text-center">
                <h1 class="text-lg font-semibold text-gray-900">
                    @if(request()->routeIs('dashboard'))
                        Главная
                    @elseif(request()->routeIs('salon.masters.*'))
                        Мастера
                    @elseif(request()->routeIs('salon.schedules.*'))
                        Расписание
                    @elseif(request()->routeIs('schedules.*'))
                        Расписание
                    @elseif(request()->routeIs('calendar.*'))
                        Календарь
                    @elseif(request()->routeIs('clients.*'))
                        Клиенты
                    @elseif(request()->routeIs('services.*'))
                        Услуги
                    @elseif(request()->routeIs('analytics.*'))
                        Статистика
                    @elseif(request()->routeIs('profile.*'))
                        Профиль
                    @else
                        @auth
                            {{ Auth::user()->name }}
                        @else
                            Aurum studio
                        @endauth
                    @endif
                </h1>
            </div>

            <!-- Profile Button -->
            @auth
                <button @click="open = !open" 
                        x-show="!open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </button>
            @else
                <a href="{{ route('login') }}" class="text-sm text-blue-600 px-3 py-2 border border-blue-600 rounded-md hover:bg-blue-50">Войти</a>
            @endauth
        </div>

        <!-- Mobile Menu Panel - ТОЛЬКО ЭТА ЧАСТЬ имеет x-trap -->
        <template x-teleport="body">
            <div 
                x-show="open"
                x-trap="open"
                x-init="$watch('open', value => {
                    if (value) {
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = '';
                    }
                })"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                @click.outside="open = false"
                @keydown.escape="open = false"
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display: none;"
            >
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black bg-opacity-50"></div>
                
                <!-- Menu Panel -->
                <div class="relative w-64 h-full bg-white shadow-xl">
                    <!-- Mobile Menu Header -->
                    <div class="flex items-center justify-between h-16 px-4 bg-blue-600 text-white">
                        <div class="text-lg font-semibold">
                            @auth
                                {{ Auth::user()->name }}
                            @else
                                Aurum studio
                            @endauth
                        </div>
                        <button @click="open = false" class="text-white hover:text-gray-200 p-1">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Menu Items -->
                    <div class="py-2">
                        <a href="{{ route('dashboard') }}" 
                           @click="open = false"
                           class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                            <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            {{ __('Главная') }}
                        </a>
                        
                        @if (Auth::user() && Auth::user()->isSalon())
                            <a href="{{ route('salon.masters.index') }}" 
                               @click="open = false"
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('salon.masters.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                {{ __('Мастера') }}
                            </a>
                            <a href="{{ route('salon.schedules.index') }}" 
                               @click="open = false"
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('salon.schedules.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ __('Расписание') }}
                            </a>
                        @endif
                        
                        @if (Auth::user() && Auth::user()->isMaster())
                            <a href="{{ route('schedules.index') }}" 
                               @click="open = false"
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('schedules.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ __('Расписание') }}
                            </a>
                        @endif
                        
                        @if (Auth::user())
                            <a href="{{ route('calendar.day', ['date' => now()->format('Y-m-d')]) }}" 
                               @click="open = false"
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('calendar.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ __('Календарь') }}
                            </a>
                            <a href="{{ route('clients.index') }}" 
                               @click="open = false"
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('clients.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                {{ __('Клиенты') }}
                            </a>
                            <a href="{{ route('services.index') }}" 
                               @click="open = false"
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('services.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ __('Услуги') }}
                            </a>
                            <a href="{{ route('analytics.index') }}" 
                               @click="open = false"
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('analytics.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                {{ __('Статистика') }}
                            </a>
                        @endif

                        <!-- Mobile User Menu -->
                        @auth
                            <div class="border-t border-gray-200 mt-2 pt-2">
                                <a href="{{ route('profile.edit') }}" 
                                   @click="open = false"
                                   class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200 {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ __('Профиль') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-3 text-gray-700 hover:bg-gray-100 text-left transition-colors duration-200">
                                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        {{ __('Выйти') }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="border-t border-gray-200 mt-2 pt-2">
                                <a href="{{ route('login') }}" 
                                   @click="open = false"
                                   class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    {{ __('Войти') }}
                                </a>
                                <a href="{{ route('register') }}" 
                                   @click="open = false"
                                   class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    {{ __('Регистрация') }}
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </template>
    </div>
</nav>
