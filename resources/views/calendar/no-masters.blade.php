<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation Header -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left side - Back button -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Center - Week navigation with days -->
                    <div class="flex items-center space-x-2">
                        @php
                            // Используем переданную из контроллера дату или сегодняшнюю как fallback
                            $selectedDate = $selectedDate ?? \Carbon\Carbon::today()->startOfDay();
                            
                            // Get start of week (Monday)
                            $startOfWeek = $selectedDate->copy()->startOfWeek()->startOfDay();
                            $endOfWeek = $selectedDate->copy()->endOfWeek()->startOfDay();
                            $previousWeek = $selectedDate->copy()->subWeek()->startOfDay();
                            $nextWeek = $selectedDate->copy()->addWeek()->startOfDay();
                        @endphp
                        
                        <!-- Previous week button -->
                        <a href="{{ route('calendar.day', ['date' => $previousWeek->format('Y-m-d')]) }}" 
                           class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        
                        <!-- Days of week -->
                        <div class="flex items-center space-x-1">
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $currentDay = $startOfWeek->copy()->addDays($i)->startOfDay();
                                    $isSelected = $currentDay->format('Y-m-d') === $selectedDate->format('Y-m-d');
                                    $isToday = $currentDay->isToday();
                                @endphp
                                <a href="{{ route('calendar.day', ['date' => $currentDay->format('Y-m-d')]) }}" 
                                   class="flex flex-col items-center px-3 py-2 rounded-lg transition-colors
                                          {{ $isSelected ? 'bg-blue-500 text-white' : ($isToday ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-100 text-gray-700') }}">
                                    <span class="text-xs font-medium">{{ $currentDay->locale('ru')->dayName }}</span>
                                    <span class="text-lg font-bold">{{ $currentDay->format('d') }}</span>
                                </a>
                            @endfor
                        </div>
                        
                        <!-- Next week button -->
                        <a href="{{ route('calendar.day', ['date' => $nextWeek->format('Y-m-d')]) }}" 
                           class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Right side - Empty since we can't add appointments without masters -->
                    <div class="w-32">
                        <!-- Placeholder to maintain layout -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @if($warning ?? false)
                @if(str_contains($warning, 'никто из мастеров не работает') || str_contains($warning, 'В выбранную дату'))
                    <!-- Message for no working masters on selected date -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
                        <div class="text-yellow-600 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">В выбранную дату никто из мастеров не работает.</h3>
                        <p class="text-gray-600 mb-6">Попробуйте выбрать другую дату или настройте расписание для мастеров.</p>
                        <div class="flex justify-center gap-4">
                            <a href="{{ route('calendar.day', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                                ПЕРЕЙТИ К СЕГОДНЯШНЕМУ ДНЮ
                            </a>
                            @if(Auth::user()->isSalon())
                                <a href="{{ route('salon.schedules.index') }}" 
                                   class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-medium">
                                    НАСТРОИТЬ РАСПИСАНИЕ
                                </a>
                            @elseif(Auth::user()->isMaster())
                                <a href="{{ route('schedules.index') }}" 
                                   class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-medium">
                                    НАСТРОИТЬ РАСПИСАНИЕ
                                </a>
                            @endif
                        </div>
                    </div>
                @elseif(str_contains($warning, 'Добавьте хотя бы одного мастера'))
                    <!-- Message for salons without masters -->
                    <div class="bg-white border border-gray-300 rounded-lg p-12 text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">У вас нет мастеров</h3>
                        <p class="text-gray-500 mb-6">Добавьте хотя бы одного мастера для просмотра календаря.</p>
                        <a href="{{ route('salon.masters.create') }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                            Добавить мастера
                        </a>
                    </div>
                @else
                    <!-- Generic message -->
                    <div class="bg-white border border-gray-300 rounded-lg p-12 text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $warning }}</h3>
                        <a href="{{ route('dashboard') }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                            Вернуться на главную
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout> 