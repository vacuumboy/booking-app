<x-app-layout>
    <!-- Заголовок страницы (скрыт на мобильных, показан на десктопе) -->
    <x-slot name="header">
        <div class="hide-mobile">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ __('Личный кабинет') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-adaptive-lg md:py-adaptive-2xl">
        <div class="container-adaptive">
            <!-- Карточки информации пользователя -->
            <div class="dashboard-info-cards mb-adaptive-lg">
                <!-- Личная информация -->
                <div class="adaptive-card">
                    <div class="card-content">
                        <div class="flex items-center mb-adaptive-base">
                            @if(auth()->user()->photo_path)
                                <img src="{{ asset('storage/' . auth()->user()->photo_path) }}" 
                                    alt="Фото профиля" 
                                    class="w-adaptive-2xl h-adaptive-2xl md:w-adaptive-3xl md:h-adaptive-3xl rounded-full object-cover border-2 border-adaptive-surface shadow-adaptive-sm mr-adaptive-base">
                            @else
                                <div class="w-adaptive-2xl h-adaptive-2xl md:w-adaptive-3xl md:h-adaptive-3xl rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center text-adaptive-xl md:text-adaptive-2xl text-adaptive-text-secondary border-2 border-adaptive-surface shadow-adaptive-sm mr-adaptive-base">
                                    👤
                                </div>
                            @endif
                            <div>
                                <h3 class="adaptive-heading-3">{{ auth()->user()->name }}</h3>
                                <span class="inline-flex items-center px-adaptive-sm py-adaptive-xs rounded-full text-adaptive-xs font-medium bg-blue-100 text-blue-800">
                                    {{ auth()->user()->user_type == 'master' ? 'Мастер' : 'Салон' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-adaptive-sm md:space-y-adaptive-base flex-grow">
                            <div class="bg-adaptive-background rounded-adaptive-base p-adaptive-sm md:p-adaptive-base">
                                <div class="text-adaptive-xs uppercase font-semibold text-adaptive-text-secondary mb-adaptive-xs">Email</div>
                                <div class="text-adaptive-sm md:text-adaptive-base text-adaptive-text break-all">{{ auth()->user()->email }}</div>
                            </div>
                            
                            <div class="bg-adaptive-background rounded-adaptive-base p-adaptive-sm md:p-adaptive-base">
                                <div class="text-adaptive-xs uppercase font-semibold text-adaptive-text-secondary mb-adaptive-xs">Телефон</div>
                                <div class="text-adaptive-sm md:text-adaptive-base text-adaptive-text">{{ auth()->user()->phone ?? 'Не указан' }}</div>
                            </div>
                            
                            <div class="bg-adaptive-background rounded-adaptive-base p-adaptive-sm md:p-adaptive-base">
                                <div class="text-adaptive-xs uppercase font-semibold text-adaptive-text-secondary mb-adaptive-xs">Адрес</div>
                                <div class="text-adaptive-sm md:text-adaptive-base text-adaptive-text">{{ auth()->user()->address ?? 'Не указан' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="mt-adaptive-base">
                            <a href="{{ route('profile.edit') }}" class="btn-adaptive bg-adaptive-primary text-white hover:bg-adaptive-primary-hover">
                                Редактировать профиль
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Профессиональная информация -->
                <div class="adaptive-card">
                    <div class="card-content">
                        <h3 class="adaptive-heading-3 mb-adaptive-base">
                            @if(auth()->user()->user_type == 'master')
                                Информация о мастере
                            @else
                                Информация о салоне
                            @endif
                        </h3>
                        
                        <div class="space-y-adaptive-sm md:space-y-adaptive-base flex-grow">
                            @if(auth()->user()->user_type == 'master')
                                <div class="bg-adaptive-background rounded-adaptive-base p-adaptive-sm md:p-adaptive-base">
                                    <div class="text-adaptive-xs uppercase font-semibold text-adaptive-text-secondary mb-adaptive-xs">Специализация</div>
                                    <div class="text-adaptive-sm md:text-adaptive-base text-adaptive-text">{{ auth()->user()->specialization ?? 'Не указана' }}</div>
                                </div>
                                
                                <div class="bg-adaptive-background rounded-adaptive-base p-adaptive-sm md:p-adaptive-base">
                                    <div class="text-adaptive-xs uppercase font-semibold text-adaptive-text-secondary mb-adaptive-xs">Опыт работы</div>
                                    <div class="text-adaptive-sm md:text-adaptive-base text-adaptive-text">{{ auth()->user()->experience_years ? auth()->user()->experience_years . ' лет' : 'Не указан' }}</div>
                                </div>
                            @else
                                <div class="bg-adaptive-background rounded-adaptive-base p-adaptive-sm md:p-adaptive-base">
                                    <div class="text-adaptive-xs uppercase font-semibold text-adaptive-text-secondary mb-adaptive-xs">Название салона</div>
                                    <div class="text-adaptive-sm md:text-adaptive-base text-adaptive-text">{{ auth()->user()->salon_name ?? 'Не указано' }}</div>
                                </div>
                            @endif
                            
                            <div class="bg-adaptive-background rounded-adaptive-base p-adaptive-sm md:p-adaptive-base">
                                <div class="text-adaptive-xs uppercase font-semibold text-adaptive-text-secondary mb-adaptive-xs">Описание</div>
                                <div class="text-adaptive-sm md:text-adaptive-base text-adaptive-text">{{ auth()->user()->bio ?? 'Не указано' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <!-- Можно добавить дополнительные элементы в подвал карточки -->
                    </div>
                </div>
            </div>

            <!-- Навигационные карточки -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Быстрая навигация</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                    @if(auth()->user()->isSalon())
                        <div x-data="clickSpark()" 
                             x-init="init(); sparkColor = '#3b82f6'; sparkCount = 8; sparkSize = 8; duration = 350;"
                             @click="handleClick($event)"
                             class="relative">
                            <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                            <a href="{{ route('salon.masters.index') }}" class="relative z-20 bg-gradient-to-br from-blue-50 to-blue-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                                <div class="text-blue-600 text-xl md:text-2xl mb-2">👥</div>
                                <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Мастера</h4>
                                <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Управление мастерами салона</p>
                            </a>
                        </div>
                        
                        <div x-data="clickSpark()" 
                             x-init="init(); sparkColor = '#6366f1'; sparkCount = 8; sparkSize = 8; duration = 350;"
                             @click="handleClick($event)"
                             class="relative">
                            <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                            <a href="{{ route('salon.schedules.index') }}" class="relative z-20 bg-gradient-to-br from-indigo-50 to-indigo-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                                <div class="text-indigo-600 text-xl md:text-2xl mb-2">📅</div>
                                <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Расписание</h4>
                                <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Управление расписанием мастеров</p>
                            </a>
                        </div>
                    @endif
                    
                    <div x-data="clickSpark()" 
                         x-init="init(); sparkColor = '#a855f7'; sparkCount = 8; sparkSize = 8; duration = 350;"
                         @click="handleClick($event)"
                         class="relative">
                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                        <a href="{{ route('calendar.day', ['date' => now()->format('Y-m-d')]) }}" class="relative z-20 bg-gradient-to-br from-purple-50 to-purple-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                            <div class="text-purple-600 text-xl md:text-2xl mb-2">📆</div>
                            <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Календарь</h4>
                            <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Просмотр и управление записями</p>
                        </a>
                    </div>
                    
                    <div x-data="clickSpark()" 
                         x-init="init(); sparkColor = '#10b981'; sparkCount = 8; sparkSize = 8; duration = 350;"
                         @click="handleClick($event)"
                         class="relative">
                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                        <a href="{{ route('clients.index') }}" class="relative z-20 bg-gradient-to-br from-green-50 to-green-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                            <div class="text-green-600 text-xl md:text-2xl mb-2">👥</div>
                            <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Клиенты</h4>
                            <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Управление базой клиентов</p>
                        </a>
                    </div>
                    
                    <div x-data="clickSpark()" 
                         x-init="init(); sparkColor = '#f59e0b'; sparkCount = 8; sparkSize = 8; duration = 350;"
                         @click="handleClick($event)"
                         class="relative">
                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                        <a href="{{ route('services.index') }}" class="relative z-20 bg-gradient-to-br from-yellow-50 to-yellow-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                            <div class="text-yellow-600 text-xl md:text-2xl mb-2">💼</div>
                            <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Услуги</h4>
                            <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Управление услугами и ценами</p>
                        </a>
                    </div>
                    
                    <div x-data="clickSpark()" 
                         x-init="init(); sparkColor = '#f97316'; sparkCount = 8; sparkSize = 8; duration = 350;"
                         @click="handleClick($event)"
                         class="relative">
                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                        <a href="{{ route('analytics.index') }}" class="relative z-20 bg-gradient-to-br from-orange-50 to-orange-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                            <div class="text-orange-600 text-xl md:text-2xl mb-2">📊</div>
                            <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Статистика</h4>
                            <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Анализ работы и доходов</p>
                        </a>
                    </div>
                    
                    <div x-data="clickSpark()" 
                         x-init="init(); sparkColor = '#ef4444'; sparkCount = 8; sparkSize = 8; duration = 350;"
                         @click="handleClick($event)"
                         class="relative">
                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                        <a href="{{ route('profile.edit') }}" class="relative z-20 bg-gradient-to-br from-red-50 to-red-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                            <div class="text-red-600 text-xl md:text-2xl mb-2">⚙️</div>
                            <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Настройки</h4>
                            <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Управление профилем и настройками</p>
                        </a>
                    </div>
                    
                    <div x-data="clickSpark()" 
                         x-init="init(); sparkColor = '#14b8a6'; sparkCount = 8; sparkSize = 8; duration = 350;"
                         @click="handleClick($event)"
                         class="relative">
                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                        <a href="{{ route('reminder-templates.index') }}" class="relative z-20 bg-gradient-to-br from-teal-50 to-teal-100 p-3 md:p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow block h-full flex flex-col">
                            <div class="text-teal-600 text-xl md:text-2xl mb-2">💬</div>
                            <h4 class="font-semibold text-gray-900 text-sm md:text-base mb-1">Напоминания</h4>
                            <p class="text-xs md:text-sm text-gray-600 mt-auto hidden md:block">Шаблоны напоминаний клиентам</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
