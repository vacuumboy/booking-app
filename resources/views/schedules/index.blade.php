<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 animate-in fade-in duration-1000">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white shadow-sm border-b border-gray-100 animate-in slide-in-from-top duration-500">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" 
                       class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-lg font-semibold text-gray-900 animate-in slide-in-from-left duration-700">Расписание</h1>
                </div>
                <a href="{{ route('schedules.create') }}" 
                   class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 animate-in slide-in-from-right duration-500">
                    <span class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Создать</span>
                    </span>
                </a>
            </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden lg:block bg-white shadow-sm animate-in slide-in-from-top duration-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4 animate-in slide-in-from-left duration-700">
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-200 group">
                            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span class="font-medium">Назад</span>
                        </a>
                        <div class="h-6 w-px bg-gray-300"></div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent animate-in zoom-in duration-1000 delay-300">
                            📅 Расписание
                        </h1>
                    </div>
                    <a href="{{ route('schedules.create') }}" 
                       class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-6 py-2 rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center space-x-2 animate-in slide-in-from-right duration-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Создать расписание</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6" style="padding: 1%;">
            <!-- Desktop Calendar View -->
            <div>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom duration-1000 delay-200">
                    <!-- Calendar Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('schedules.index', ['month' => $startDate->copy()->subMonth()->month, 'year' => $startDate->copy()->subMonth()->year]) }}" 
                               class="flex items-center space-x-2 text-white hover:text-blue-100 transition-all duration-200 group transform hover:scale-105">
                                <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                <span class="font-medium">{{ $startDate->copy()->subMonth()->translatedFormat('F Y') }}</span>
                            </a>
                            <h2 class="text-xl font-bold text-white animate-in zoom-in duration-700 delay-500">
                                {{ $startDate->translatedFormat('F Y') }}
                            </h2>
                            <a href="{{ route('schedules.index', ['month' => $startDate->copy()->addMonth()->month, 'year' => $startDate->copy()->addMonth()->year]) }}" 
                               class="flex items-center space-x-2 text-white hover:text-blue-100 transition-all duration-200 group transform hover:scale-105">
                                <span class="font-medium">{{ $startDate->copy()->addMonth()->translatedFormat('F Y') }}</span>
                                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="p-6" style="padding: 1%;">
                        <!-- Desktop Version -->
                        <div class="hidden lg:block">
                            <!-- Weekday Headers -->
                            <div class="grid grid-cols-7 gap-2 mb-4">
                                @foreach(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $index => $day)
                                    <div class="text-center py-3 text-sm font-semibold text-gray-600 bg-gray-50 rounded-lg animate-in slide-in-from-top duration-500" style="animation-delay: {{ $index * 50 }}ms">
                                        {{ $day }}
                                    </div>
                                @endforeach
                            </div>

                            <!-- Calendar Days -->
                            <div class="grid grid-cols-7 gap-2">
                                @foreach($calendar as $weekIndex => $week)
                                    @foreach($week as $dayIndex => $day)
                                        <div class="relative group min-h-[120px] border-2 rounded-xl p-3 transition-all duration-300 hover:shadow-lg animate-in fade-in slide-in-from-bottom duration-500 {{ $day['is_current_month'] ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 hover:bg-gray-100' }} {{ $day['is_today'] ? 'ring-2 ring-blue-500 ring-opacity-50 shadow-lg' : 'border-gray-200' }}" 
                                             style="animation-delay: {{ ($weekIndex * 7 + $dayIndex) * 30 }}ms">
                                        <!-- Day Number and Add Button -->
                                        <div class="flex items-start justify-between mb-2">
                                            <span class="text-lg font-semibold {{ $day['is_current_month'] ? ($day['is_today'] ? 'text-blue-600' : 'text-gray-900') : 'text-gray-400' }}">
                                                {{ $day['date']->day }}
                                            </span>
                                            @if($day['is_current_month'])
                                                <a href="{{ route('schedules.create', ['date' => $day['date']->format('Y-m-d')]) }}" 
                                                   class="opacity-0 group-hover:opacity-100 w-6 h-6 bg-blue-500 hover:bg-blue-600 text-white rounded-full flex items-center justify-center text-xs transition-all duration-200 transform hover:scale-110 animate-in zoom-in duration-300">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>

                                        <!-- Schedule Status -->
                                        @if($day['has_schedule'])
                                            <div class="space-y-1">
                                                @if($day['schedule']->is_day_off)
                                                    <div class="bg-gradient-to-r from-red-100 to-pink-100 text-red-700 px-2 py-1 rounded-lg text-xs font-medium flex items-center space-x-1 animate-in slide-in-from-left duration-300">
                                                        <span>🏖️</span>
                                                        <span>Выходной</span>
                                                    </div>
                                                @else
                                                    <div class="bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 px-2 py-1 rounded-lg text-xs font-medium flex items-center space-x-1 animate-in slide-in-from-left duration-300">
                                                        <span>💼</span>
                                                        <span>{{ $day['schedule']->working_hours }}</span>
                                                    </div>
                                                @endif
                                                @if($day['schedule']->notes)
                                                    <div class="text-gray-600 text-xs bg-gray-100 px-2 py-1 rounded truncate animate-in fade-in duration-300 delay-100">
                                                        💭 {{ Str::limit($day['schedule']->notes, 15) }}
                                                    </div>
                                                @endif
                                                <!-- Edit Button -->
                                                <div class="flex space-x-1 mt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                    <a href="{{ route('schedules.edit', $day['schedule']) }}" 
                                                       class="bg-gray-500 hover:bg-gray-600 text-white text-xs px-2 py-1 rounded transition-all duration-200 transform hover:scale-105">
                                                        ✏️
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                        </div>

                        <!-- Mobile Version -->
                        <div class="lg:hidden px-4" style="padding: 1%;">
                            <!-- Weekday Headers -->
                            <div class="flex w-full mb-2">
                                @foreach(['П', 'В', 'С', 'Ч', 'П', 'С', 'В'] as $index => $day)
                                    <div class="flex-1 text-center py-2 text-xs font-medium text-gray-600 bg-gray-100 rounded-sm mx-px">
                                        {{ $day }}
                                    </div>
                                @endforeach
                            </div>

                            <!-- Calendar Days -->
                            <div class="flex flex-wrap w-full">
                                @foreach($calendar as $weekIndex => $week)
                                    @foreach($week as $dayIndex => $day)
                                        <div class="p-px" style="width: 14.2857%;">
                                            @if($day['is_current_month'])
                                                <a href="{{ route('schedules.create', ['date' => $day['date']->format('Y-m-d')]) }}" 
                                                   class="block relative border rounded-sm transition-all duration-200 {{ $day['is_today'] ? 'ring-2 ring-blue-500 bg-blue-50' : 'border-gray-200' }} bg-white hover:bg-gray-50 hover:shadow-md active:bg-gray-100" 
                                                   style="aspect-ratio: 1; min-height: 48px;">
                                                    <!-- Day Number -->
                                                    <div class="p-1 h-full flex flex-col">
                                                        <div class="flex items-start space-x-1">
                                                            <div class="text-xs font-medium {{ $day['is_today'] ? 'text-blue-600' : 'text-gray-900' }}">
                                                                {{ $day['date']->day }}
                                                            </div>
                                                            @if(!$day['has_schedule'])
                                                                <div class="w-3 h-3 bg-blue-500 text-white rounded-full flex items-center justify-center opacity-60">
                                                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        
                                                        <!-- Schedule Status -->
                                                        @if($day['has_schedule'])
                                                            <div class="mt-1 flex-1 flex flex-col justify-center">
                                                                @if($day['schedule']->is_day_off)
                                                                    <div class="bg-red-100 text-red-600 text-xs px-1 py-0.5 rounded text-center">
                                                                        🏖️
                                                                    </div>
                                                                @else
                                                                    <div class="bg-green-100 text-green-600 text-xs px-1 py-0.5 rounded text-center">
                                                                        💼
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </a>
                                            @else
                                                <div class="relative border rounded-sm bg-gray-50 border-gray-200" 
                                                     style="aspect-ratio: 1; min-height: 48px;">
                                                    <div class="p-1 h-full flex flex-col">
                                                        <div class="text-xs font-medium text-gray-400">
                                                            {{ $day['date']->day }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout> 