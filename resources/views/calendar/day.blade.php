<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
        
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white shadow-sm border-b border-gray-100">
            <div class="px-4 py-3">
                <!-- Top row with back button and add appointment -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('dashboard') }}" 
                           class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200 transform hover:scale-105"
                           data-nav="dashboard">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        <h1 class="text-lg font-semibold text-gray-900">Календарь</h1>
                    </div>
                    @if($masters->count() > 0)
                        <a href="{{ route('calendar.create-appointment', ['date' => $selectedDate->format('Y-m-d')]) }}" 
                           class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium shadow-md hover:shadow-lg transition-all duration-200"
                           data-nav="create-appointment">
                            <span class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Запись</span>
                            </span>
                        </a>
                    @else
                        <div class="px-3 py-1.5 bg-gray-300 text-gray-500 rounded-lg text-xs">
                            Нет мастеров
                        </div>
                    @endif
                </div>

                <!-- Week navigation -->
                <div class="flex items-center justify-center space-x-2">
                    @php
                        $startOfWeek = $selectedDate->copy()->startOfWeek()->startOfDay();
                        $previousWeek = $selectedDate->copy()->subWeek()->startOfDay();
                        $nextWeek = $selectedDate->copy()->addWeek()->startOfDay();
                    @endphp
                    
                    <a href="{{ route('calendar.day', ['date' => $previousWeek->format('Y-m-d')]) }}" 
                       class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200"
                       data-nav="previous-week">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    
                    <div class="flex space-x-1 overflow-x-auto scrollbar-hide">
                        @for ($i = 0; $i < 7; $i++)
                            @php
                                $currentDay = $startOfWeek->copy()->addDays($i)->startOfDay();
                                $isSelected = $currentDay->format('Y-m-d') === $selectedDate->format('Y-m-d');
                                $isToday = $currentDay->isToday();
                            @endphp
                            <a href="{{ route('calendar.day', ['date' => $currentDay->format('Y-m-d')]) }}" 
                               class="flex flex-col items-center px-2 py-2 rounded-lg transition-all duration-200 min-w-[50px] {{ $isSelected ? 'bg-blue-500 text-white shadow-lg' : ($isToday ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-100 text-gray-700') }}"
                               data-nav="day-{{ $i }}">
                                <span class="text-xs font-medium">{{ mb_substr($currentDay->locale('ru')->dayName, 0, 2) }}</span>
                                <span class="text-lg font-bold">{{ $currentDay->format('d') }}</span>
                            </a>
                        @endfor
                    </div>
                    
                    <a href="{{ route('calendar.day', ['date' => $nextWeek->format('Y-m-d')]) }}" 
                       class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200"
                       data-nav="next-week">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden lg:block bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left side - Back button -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-200 group"
                           data-nav="dashboard">
                            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span class="font-medium">Назад</span>
                        </a>
                        <div class="h-6 w-px bg-gray-300"></div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            📅 Календарь записей
                        </h1>
                    </div>

                    <!-- Center - Week navigation with days -->
                    <div class="flex items-center space-x-2">
                        @php
                            $startOfWeek = $selectedDate->copy()->startOfWeek()->startOfDay();
                            $previousWeek = $selectedDate->copy()->subWeek()->startOfDay();
                            $nextWeek = $selectedDate->copy()->addWeek()->startOfDay();
                        @endphp
                        
                        <a href="{{ route('calendar.day', ['date' => $previousWeek->format('Y-m-d')]) }}" 
                           class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200 transform hover:scale-105"
                           data-nav="previous-week">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        
                        <div class="flex items-center space-x-1">
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $currentDay = $startOfWeek->copy()->addDays($i)->startOfDay();
                                    $isSelected = $currentDay->format('Y-m-d') === $selectedDate->format('Y-m-d');
                                    $isToday = $currentDay->isToday();
                                @endphp
                                <a href="{{ route('calendar.day', ['date' => $currentDay->format('Y-m-d')]) }}" 
                                   class="flex flex-col items-center px-3 py-2 rounded-lg transition-all duration-200 transform hover:scale-105 {{ $isSelected ? 'bg-blue-500 text-white shadow-lg' : ($isToday ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-100 text-gray-700') }}"
                                   data-nav="day-{{ $i }}">
                                    <span class="text-xs font-medium">{{ $currentDay->locale('ru')->dayName }}</span>
                                    <span class="text-lg font-bold">{{ $currentDay->format('d') }}</span>
                                </a>
                            @endfor
                        </div>
                        
                        <a href="{{ route('calendar.day', ['date' => $nextWeek->format('Y-m-d')]) }}" 
                           class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200 transform hover:scale-105"
                           data-nav="next-week">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Right side - Add appointment button -->
                    <div>
                        @if($masters->count() > 0)
                            <a href="{{ route('calendar.create-appointment', ['date' => $selectedDate->format('Y-m-d')]) }}" 
                               class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center space-x-2"
                               data-nav="create-appointment">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>ДОБАВИТЬ ЗАПИСЬ</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6" style="padding: 1%;">
            @if($masters->count() > 0)
                
                <!-- Desktop Calendar View -->
                <div class="hidden lg:block">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <div class="overflow-x-auto">
                            <div class="grid" style="grid-template-columns: 80px repeat({{ count($masters) }}, 1fr); min-width: 800px;">
                                <!-- Time Column Header -->
                                <div class="bg-gradient-to-r from-gray-100 to-gray-200 border-r border-gray-300 border-b border-gray-300 p-3 text-center font-bold text-gray-700 text-sm">
                                    Время
                                </div>
                                
                                <!-- Master Headers -->
                                @foreach($masters as $master)
                                    @php 
                                        $masterData = $calendarData[$master->id] ?? null;
                                        $workingHours = '';
                                        if($masterData && $masterData['schedule'] && !$masterData['schedule']->is_day_off) {
                                            $workingHours = $masterData['schedule']->start_time . '-' . $masterData['schedule']->end_time;
                                        }
                                    @endphp
                                    <div class="bg-gradient-to-r from-gray-100 to-gray-200 border-r border-gray-300 border-b border-gray-300 p-3 text-center">
                                        <div class="font-bold text-gray-900 text-sm">{{ $master->name }}</div>
                                        @if($workingHours)
                                            <div class="text-xs text-gray-600 mt-1 bg-blue-100 px-2 py-1 rounded-full">{{ $workingHours }}</div>
                                        @endif
                                    </div>
                                @endforeach

                                <!-- Time Grid -->
                                @foreach($timeSlots as $slotIndex => $slot)
                                    <!-- Time Label -->
                                    <div class="bg-white border-r border-gray-200 border-b border-gray-200 p-2 text-center text-sm text-gray-600 font-medium" style="height: 60px; display: flex; align-items: center; justify-content: center;">
                                        {{ $slot }}
                                    </div>
                                    
                                    <!-- Master Columns -->
                                    @foreach($masters as $master)
                                        @php 
                                            $masterData = $calendarData[$master->id] ?? null;
                                            $appointmentStartsHere = null;
                                            $slotIsOccupied = false;
                                            
                                            if($masterData) {
                                                foreach($masterData['appointments'] as $appointmentData) {
                                                    $appointment = $appointmentData['appointment'];
                                                    $appointmentStart = \Carbon\Carbon::parse($appointment->start_time);
                                                    $appointmentEnd = \Carbon\Carbon::parse($appointment->end_time);
                                                    $slotTime = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                                    $slotEndTime = $slotTime->copy()->addMinutes(30);
                                                    
                                                    if($appointmentStart->format('H:i') == $slot) {
                                                        $appointmentStartsHere = $appointment;
                                                        break;
                                                    }
                                                    
                                                    if($appointmentStart < $slotEndTime && $appointmentEnd > $slotTime) {
                                                        $slotIsOccupied = true;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="relative border-r border-gray-200 border-b border-gray-200 bg-white hover:bg-gray-50 transition-colors duration-200" style="height: 60px;">
                                            @if($appointmentStartsHere)
                                                @php
                                                    $appointment = $appointmentStartsHere;
                                                    $startTime = \Carbon\Carbon::parse($appointment->start_time);
                                                    $endTime = \Carbon\Carbon::parse($appointment->end_time);
                                                    $durationMinutes = $startTime->diffInMinutes($endTime);
                                                    $heightInPixels = ($durationMinutes / 30) * 60;
                                                    
                                                    $slotStartTime = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                                    $minutesFromSlotStart = $startTime->diffInMinutes($slotStartTime);
                                                    $topOffsetPixels = ($minutesFromSlotStart / 30) * 60;
                                                    
                                                    if ($appointment->status === 'cancelled') {
                                                        $bgColor = 'bg-gray-100 border-gray-300 text-gray-600';
                                                    } else {
                                                        if (isset($appointment->service->color) && $appointment->service->color) {
                                                            $serviceColor = $appointment->service->color;
                                                            $bgColor = "border-gray-300 text-gray-800";
                                                            $customColor = $serviceColor;
                                                        } else {
                                                            $colors = [
                                                                'bg-blue-50 border-blue-200 text-blue-800',
                                                                'bg-green-50 border-green-200 text-green-800',
                                                                'bg-amber-50 border-amber-200 text-amber-800',
                                                                'bg-purple-50 border-purple-200 text-purple-800',
                                                                'bg-pink-50 border-pink-200 text-pink-800'
                                                            ];
                                                            $colorIndex = $appointment->id % count($colors);
                                                            $bgColor = $colors[$colorIndex];
                                                            $customColor = null;
                                                        }
                                                    }
                                                @endphp
                                                
                                                <!-- Appointment Card -->
                                                <div class="absolute inset-x-0 {{ $bgColor }} border rounded-lg p-2 text-xs z-10 shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer overflow-hidden group appointment-card"
                                                     style="top: {{ $topOffsetPixels }}px; height: {{ $heightInPixels }}px; min-height: 40px; {{ $customColor ? 'background-color: ' . $customColor . ';' : '' }}"
                                                     data-appointment-url="{{ route('appointments.edit', $appointment) }}">
                                                    
                                                    <div class="pb-6">
                                                        <div class="font-bold text-sm leading-tight">
                                                            {{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}
                                                        </div>
                                                        <div class="font-bold text-sm mt-0.5 leading-tight">
                                                            {{ $appointment->client->name }}
                                                            @if($appointment->status === 'cancelled')
                                                                <span class="text-red-600 font-black">(ОТМЕНЕНО)</span>
                                                            @endif
                                                        </div>
                                                        <div class="font-medium text-xs mt-0.5 leading-tight">
                                                            {{ $appointment->service->name }}
                                                        </div>
                                                        <div class="font-bold text-sm mt-0.5 leading-tight">
                                                            {{ number_format($appointment->price, 0) }} €
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="absolute bottom-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                        @if($appointment->status !== 'cancelled')
                                                            <form action="{{ route('appointments.cancel', $appointment) }}" method="POST" class="inline cancel-form">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" 
                                                                        class="bg-red-500 hover:bg-red-600 text-white px-1.5 py-0.5 rounded text-xs font-medium transition-colors duration-200 cancel-appointment">
                                                                    ✕
                                                                </button>
                                                            </form>
                                                        @endif
                                                        
                                                        <a href="{{ route('appointments.edit', $appointment) }}" 
                                                           class="bg-blue-500 hover:bg-blue-600 text-white px-1.5 py-0.5 rounded text-xs font-medium transition-colors duration-200 edit-appointment">
                                                            ✏️
                                                        </a>
                                                    </div>
                                                </div>
                                            @elseif(!$slotIsOccupied)
                                                <div class="absolute inset-0 cursor-pointer hover:bg-blue-50 transition-colors group empty-slot"
                                                     data-create-url="{{ route('calendar.create-appointment', ['date' => $selectedDate->format('Y-m-d'), 'master_id' => $master->id, 'time' => $slot]) }}">
                                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                        <span class="text-blue-500 text-xs font-medium">+ Добавить</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="absolute inset-0 bg-gray-50"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Calendar View -->
                <div class="lg:hidden px-4">
                    <!-- Controls -->
                    

                    <!-- Grid without horizontal scroll: times stacked, masters wrap in 2 columns -->
                    @php
                        $now = \Carbon\Carbon::now();
                        $roundedNow = $now->copy()->minute($now->minute >= 30 ? 30 : 0)->second(0);
                        $currentSlot = $roundedNow->format('H:i');
                    @endphp
                    <div id="mobileGridCompact" class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <!-- Headers (no horizontal scroll) -->
                        <div class="grid" style="grid-template-columns: 48px repeat({{ count($masters) }}, 1fr);">
                            <div class="bg-gray-50 border-b border-gray-200 p-2 text-center text-[10px] font-semibold text-gray-600">Вр.</div>
                            @foreach($masters as $master)
                                <div class="bg-gray-50 border-b border-l border-gray-200 p-2 text-center text-[10px] font-semibold text-gray-800 mobile-master-header" data-master-id="{{ $master->id }}">{{ $master->name }}</div>
                            @endforeach
                        </div>

                        <!-- Time rows -->
                        @php $baseHeight = 28; @endphp
                        @foreach($timeSlots as $slotIndex => $slot)
                            <div class="grid" style="grid-template-columns: 48px repeat({{ count($masters) }}, 1fr);">
                                <!-- Time label -->
                                <div class="border-b border-gray-200 text-center text-[10px] font-medium {{ $slot === $currentSlot ? 'bg-yellow-50 text-yellow-800' : 'bg-white text-gray-600' }} flex items-center justify-center" style="height: {{ $baseHeight }}px;">{{ $slot }}</div>

                                <!-- Master cells -->
                                @foreach($masters as $master)
                                    @php 
                                        $masterData = $calendarData[$master->id] ?? null;
                                        $appointmentStartsHere = null;
                                        $slotIsOccupied = false;
                                        if($masterData) {
                                            foreach($masterData['appointments'] as $appointmentData) {
                                                $appointment = $appointmentData['appointment'];
                                                $appointmentStart = \Carbon\Carbon::parse($appointment->start_time);
                                                $appointmentEnd = \Carbon\Carbon::parse($appointment->end_time);
                                                $slotTime = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                                $slotEndTime = $slotTime->copy()->addMinutes(30);
                                                if($appointmentStart->format('H:i') == $slot) { $appointmentStartsHere = $appointment; break; }
                                                if($appointmentStart < $slotEndTime && $appointmentEnd > $slotTime) { $slotIsOccupied = true; }
                                            }
                                        }
                                    @endphp
                                    <div class="relative border-l border-b border-gray-200 bg-white mobile-master-cell" data-master-id="{{ $master->id }}" style="height: {{ $baseHeight }}px;">
                                        @if($appointmentStartsHere)
                                            @php
                                                $appointment = $appointmentStartsHere;
                                                $startTime = \Carbon\Carbon::parse($appointment->start_time);
                                                $endTime = \Carbon\Carbon::parse($appointment->end_time);
                                                $durationMinutes = $startTime->diffInMinutes($endTime);
                                                $slotStartTime = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                                $minutesFromSlotStart = $startTime->diffInMinutes($slotStartTime);
                                                $heightInPixels = ($durationMinutes / 30) * $baseHeight;
                                                $topOffsetPixels = ($minutesFromSlotStart / 30) * $baseHeight;
                                                if ($appointment->status === 'cancelled') {
                                                    $bgColor = 'bg-gray-50 border-gray-300 text-gray-600';
                                                    $customColor = null;
                                                } else {
                                                    if (isset($appointment->service->color) && $appointment->service->color) {
                                                        $bgColor = 'border-gray-300 text-gray-800';
                                                        $customColor = $appointment->service->color;
                                                    } else {
                                                        $colors = [
                                                            'bg-blue-50 border-blue-200 text-blue-800',
                                                            'bg-green-50 border-green-200 text-green-800',
                                                            'bg-amber-50 border-amber-200 text-amber-800',
                                                            'bg-purple-50 border-purple-200 text-purple-800',
                                                            'bg-pink-50 border-pink-200 text-pink-800'
                                                        ];
                                                        $bgColor = $colors[$appointment->id % count($colors)];
                                                        $customColor = null;
                                                    }
                                                }
                                            @endphp
                                            <div class="absolute inset-x-0 {{ $bgColor }} border rounded px-1.5 py-0.5 text-[10px] z-10 overflow-hidden whitespace-nowrap text-ellipsis mobile-appointment"
                                                 style="top: {{ $topOffsetPixels }}px; height: {{ $heightInPixels }}px; min-height: 20px; {{ $customColor ? 'background-color: ' . $customColor . ';' : '' }}"
                                                 data-appointment-url="{{ route('appointments.edit', $appointment) }}">
                                                <span class="font-semibold">{{ $startTime->format('H:i') }}–{{ $endTime->format('H:i') }}</span>
                                                <span class="ml-1">{{ \Illuminate\Support\Str::limit($appointment->client->name, 10) }}</span>
                                            </div>
                                        @elseif(!$slotIsOccupied)
                                            <div class="absolute inset-0 cursor-pointer hover:bg-blue-50 transition-colors group empty-slot" data-create-url="{{ route('calendar.create-appointment', ['date' => $selectedDate->format('Y-m-d'), 'master_id' => $master->id, 'time' => $slot]) }}">
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                    <span class="text-blue-500 text-[10px] font-medium">+ Добавить</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="absolute inset-0 bg-gray-50"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

            @else
                <!-- No Masters Message -->
                <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div class="text-yellow-500 mb-6">
                        <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">В выбранную дату никто из мастеров не работает</h3>
                    <p class="text-gray-600 mb-8">Попробуйте выбрать другую дату или настройте расписание для мастеров</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('calendar.day', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" 
                           class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-8 py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 no-masters-today">
                            ПЕРЕЙТИ К СЕГОДНЯШНЕМУ ДНЮ
                        </a>
                        <a href="{{ route('salon.schedules.index') }}" 
                           class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-8 py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 no-masters-schedule">
                            НАСТРОИТЬ РАСПИСАНИЕ
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Styles -->
    <style>
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        .grid {
            display: grid;
        }
        
        @media (max-width: 1023px) {
            .max-w-7xl {
                max-width: 100%;
            }
        }
    </style>

    <!-- Fixed JavaScript with proper event listeners -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Calendar loaded successfully');

            // Removed master filter on mobile per request

            // Navigation links
            document.querySelectorAll('[data-nav]').forEach(link => {
                link.addEventListener('click', function(e) {
                    console.log('Navigation clicked:', this.getAttribute('data-nav'), this.href);
                    // Default behavior handles navigation
                });
            });

            // Desktop appointment cards
            document.querySelectorAll('.appointment-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't navigate if clicking buttons
                    if (e.target.closest('button, a')) return;
                    
                    const url = this.getAttribute('data-appointment-url');
                    console.log('Appointment card clicked:', url);
                    window.location.href = url;
                });
            });

            // Empty slots for creating appointments
            document.querySelectorAll('.empty-slot').forEach(slot => {
                slot.addEventListener('click', function(e) {
                    const url = this.getAttribute('data-create-url');
                    console.log('Empty slot clicked:', url);
                    window.location.href = url;
                });
            });

            // Mobile appointments
            document.querySelectorAll('.mobile-appointment').forEach(appointment => {
                appointment.addEventListener('click', function(e) {
                    // Don't navigate if clicking buttons
                    if (e.target.closest('button, a')) return;
                    
                    const url = this.getAttribute('data-appointment-url');
                    console.log('Mobile appointment clicked:', url);
                    window.location.href = url;
                });
            });

            // Cancel appointment buttons (prevent event bubbling)
            document.querySelectorAll('.cancel-appointment, .mobile-cancel-appointment').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (!confirm('Отменить запись?')) {
                        e.preventDefault();
                    } else {
                        console.log('Appointment cancelled');
                    }
                });
            });

            // Edit appointment links (prevent event bubbling)
            document.querySelectorAll('.edit-appointment, .mobile-edit-appointment').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.stopPropagation();
                    console.log('Edit appointment clicked:', this.href);
                });
            });

            // Mobile create appointment buttons
            document.querySelectorAll('.mobile-create-appointment, .mobile-create-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    console.log('Mobile create appointment clicked:', this.href);
                });
            });

            // No masters buttons
            document.querySelectorAll('.no-masters-today, .no-masters-schedule').forEach(link => {
                link.addEventListener('click', function(e) {
                    console.log('No masters button clicked:', this.href);
                });
            });

            // Toggle mobile views
            const mobileGridView = document.getElementById('mobileGridView');
            const mobileListView = document.getElementById('mobileListView');
            const btnGrid = document.getElementById('mobileViewGrid');
            const btnList = document.getElementById('mobileViewList');
            const masterFilter = document.getElementById('mobileMasterFilter');

            if (btnGrid && btnList && mobileGridView && mobileListView) {
                btnGrid.addEventListener('click', () => {
                    btnGrid.classList.add('bg-white', 'shadow');
                    btnGrid.classList.remove('text-gray-600');
                    btnList.classList.remove('bg-white', 'shadow');
                    btnList.classList.add('text-gray-600');
                    mobileGridView.classList.remove('hidden');
                    mobileListView.classList.add('hidden');
                });
                btnList.addEventListener('click', () => {
                    btnList.classList.add('bg-white', 'shadow');
                    btnList.classList.remove('text-gray-600');
                    btnGrid.classList.remove('bg-white', 'shadow');
                    btnGrid.classList.add('text-gray-600');
                    mobileListView.classList.remove('hidden');
                    mobileGridView.classList.add('hidden');
                });
            }

            // Filter masters in list view
            if (masterFilter) {
                masterFilter.addEventListener('change', (e) => {
                    const value = e.target.value;
                    document.querySelectorAll('.mobile-master-list').forEach(el => {
                        const id = el.getAttribute('data-master-id');
                        if (value === 'all' || value === id) {
                            el.classList.remove('hidden');
                        } else {
                            el.classList.add('hidden');
                        }
                    });
                });
            }

            console.log('All event listeners attached successfully');
        });
    </script>
</x-app-layout> 