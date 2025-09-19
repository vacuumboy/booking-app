@props(['calendar', 'startDate', 'endDate', 'selectedMaster'])

<div class="bg-white rounded-lg shadow-md p-6">
  <!-- Десктопная версия (скрыта на мобильных) -->
  <div class="hidden lg:block">
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-blue-500 text-white flex items-center justify-center rounded-md mr-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900">{{ $startDate->translatedFormat('F Y') }}</h3>
          <p class="text-sm text-gray-600">Расписание: {{ $selectedMaster->name }}</p>
        </div>
      </div>
      <div class="flex space-x-2">
        <a href="{{ route('salon.schedules.index', ['master_id' => $selectedMaster->id, 'month' => $startDate->copy()->subMonth()->month, 'year' => $startDate->copy()->subMonth()->year]) }}" 
           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium">
          ← Пред
        </a>
        <a href="{{ route('salon.schedules.index', ['master_id' => $selectedMaster->id, 'month' => $startDate->copy()->addMonth()->month, 'year' => $startDate->copy()->addMonth()->year]) }}" 
           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium">
          След →
        </a>
      </div>
    </div>

    <!-- Календарная сетка -->
    <div class="grid grid-cols-7 gap-1 mb-4">
      <!-- Заголовки дней недели -->
      <div class="text-center font-semibold py-2 text-gray-700">Пн</div>
      <div class="text-center font-semibold py-2 text-gray-700">Вт</div>
      <div class="text-center font-semibold py-2 text-gray-700">Ср</div>
      <div class="text-center font-semibold py-2 text-gray-700">Чт</div>
      <div class="text-center font-semibold py-2 text-gray-700">Пт</div>
      <div class="text-center font-semibold py-2 text-gray-700">Сб</div>
      <div class="text-center font-semibold py-2 text-gray-700">Вс</div>

      <!-- Дни календаря -->
      @foreach($calendar as $week)
        @foreach($week as $day)
          <div class="border rounded-lg p-2 h-24 {{ $day['is_current_month'] ? 'bg-white' : 'bg-gray-50' }} {{ $day['is_today'] ? 'ring-2 ring-blue-500' : '' }}">
            <div class="flex justify-between items-start mb-1">
              <span class="text-sm {{ $day['is_current_month'] ? 'text-gray-900' : 'text-gray-400' }}">
                {{ $day['date']->day }}
              </span>
              @if($day['is_current_month'])
                <a href="{{ route('salon.schedules.create', ['master_id' => $selectedMaster->id, 'date' => $day['date']->format('Y-m-d')]) }}" 
                   class="text-blue-500 hover:text-blue-700 text-xs">+</a>
              @endif
            </div>

            @if($day['has_schedule'])
              <div class="text-xs">
                @if($day['schedule']->is_day_off)
                  <div class="bg-red-100 text-red-800 px-1 rounded">
                    Выходной
                  </div>
                @else
                  <div class="bg-green-100 text-green-800 px-1 rounded">
                    {{ $day['schedule']->working_hours }}
                  </div>
                @endif
              </div>
            @endif
          </div>
        @endforeach
      @endforeach
    </div>


  </div>


</div> 