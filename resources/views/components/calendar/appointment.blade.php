@props(['appointment', 'startRow' => 2, 'endRow' => 3, 'durationMinutes' => null])

<div class="appointment" 
     style="width: 100%; height: 100%; z-index: 10; pointer-events: auto;"
     data-appointment-id="{{ $appointment->id }}">
     
    @php
        // Determine color based on appointment status and service category
        if ($appointment->status === 'cancelled') {
            $colorClass = 'bg-gray-50 border-gray-200 text-gray-500 opacity-75';
        } else {
            $serviceCategory = strtolower($appointment->service->category ?? 'other');
            $colorClass = match($serviceCategory) {
                'fitness', 'спорт' => 'bg-emerald-100 border-emerald-300 text-emerald-800',
                'massage', 'массаж' => 'bg-purple-100 border-purple-300 text-purple-800', 
                'beauty', 'красота' => 'bg-amber-100 border-amber-300 text-amber-800',
                'medical', 'медицина' => 'bg-red-100 border-red-300 text-red-800',
                'training', 'тренировка' => 'bg-cyan-100 border-cyan-300 text-cyan-800',
                default => 'bg-gray-100 border-gray-300 text-gray-800'
            };
        }
    @endphp
    
    @php
        // Определяем длительность записи в минутах
        $startTime = Carbon\Carbon::parse($appointment->start_time);
        $endTime = Carbon\Carbon::parse($appointment->end_time);
        $durationMinutes = $startTime->diffInMinutes($endTime);
        $isShortAppointment = $durationMinutes <= 30;
    @endphp

    <div class="appointment-content {{ $colorClass }} border rounded-md {{ $isShortAppointment ? 'p-2' : 'p-3' }} shadow-sm hover:shadow transition-shadow h-full flex flex-col">
        @if($isShortAppointment)
            <!-- Компактная версия для 30-минутных записей -->
            <div class="flex justify-between items-start mb-1">
                <div class="appointment-time font-semibold text-xs">
                    {{ $startTime->format('H:i') }}-{{ $endTime->format('H:i') }}
                </div>
                <div class="flex gap-1">
                    @if($appointment->status !== 'cancelled')
                        <button onclick="cancelAppointmentInCalendar({{ $appointment->id }})" 
                                class="text-xs bg-red-500 text-white px-1 py-0.5 rounded hover:bg-red-600 transition-colors flex-shrink-0"
                                id="cancel-btn-cal-{{ $appointment->id }}">
                            ✕
                        </button>
                    @endif
                    <button onclick="editAppointment({{ $appointment->id }})" 
                            class="text-xs bg-blue-500 text-white px-1 py-0.5 rounded hover:bg-blue-600 transition-colors flex-shrink-0">
                        ✎
                    </button>
                </div>
            </div>
            
            <div class="flex-1 min-h-0">
                <div class="appointment-client font-medium text-xs mb-0.5 truncate">
                    {{ $appointment->client->name }}
                    @if($appointment->status === 'cancelled')
                        <span class="text-red-500 font-bold">(ОТМЕНЕНО)</span>
                    @endif
                </div>
                <div class="flex justify-between items-center">
                    <div class="appointment-service text-xs truncate flex-1">{{ $appointment->service->name }}</div>
                    <div class="text-xs font-semibold opacity-90 ml-1 flex-shrink-0">{{ number_format($appointment->price, 0, ',', ' ') }}€</div>
                </div>
            </div>
        @else
            <!-- Обычная версия для длинных записей -->
            <div class="appointment-time font-semibold text-sm mb-2">
                {{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}
            </div>
            
            <div class="flex-1 min-h-0">
                <div class="appointment-client font-medium text-sm mb-1">
                    {{ $appointment->client->name }}
                    @if($appointment->status === 'cancelled')
                        <div class="text-red-500 font-bold text-xs mt-1">ОТМЕНЕНО</div>
                    @endif
                </div>
                <div class="appointment-phone text-xs opacity-75 mb-2">{{ $appointment->client->phone }}</div>
                
                <div class="appointment-service text-xs font-medium mb-1">
                    {{ $appointment->service->name }}
                </div>
                <div class="text-xs font-semibold opacity-90">
                    {{ number_format($appointment->price, 0, ',', ' ') }} €
                </div>
            </div>
            
            <div class="mt-2 flex justify-end gap-2">
                @if($appointment->status !== 'cancelled')
                    <button onclick="cancelAppointmentInCalendar({{ $appointment->id }})" 
                            class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition-colors"
                            id="cancel-btn-cal-{{ $appointment->id }}">
                        Отменить
                    </button>
                @endif
                <button onclick="editAppointment({{ $appointment->id }})" 
                        class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition-colors">
                    Изменить
                </button>
            </div>
        @endif
    </div>
</div> 