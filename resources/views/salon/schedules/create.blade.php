<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Создание расписания для мастера</h1>
            <div>
                <a href="{{ route('salon.schedules.index', ['master_id' => $master->id]) }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                    Назад к расписанию
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4">
                <div class="font-medium">Ошибки при заполнении формы:</div>
                <ul class="mt-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6 border-b pb-4">
                <h2 class="text-lg font-semibold mb-2">Мастер: {{ $master->name }}</h2>
                <div class="text-gray-600">Создание расписания на {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</div>
            </div>

            <form action="{{ route('salon.schedules.store') }}" method="POST">
                @csrf
                <input type="hidden" name="master_id" value="{{ $master->id }}">
                <input type="hidden" name="date" value="{{ $date }}">
                
                <div class="mb-6">
                    <label class="flex items-center p-3 border rounded-lg bg-gray-50 hover:bg-gray-100 transition-all cursor-pointer">
                        <input type="hidden" name="is_day_off" value="0">
                        <input type="checkbox" name="is_day_off" id="is_day_off" value="1" class="w-5 h-5 mr-3" onchange="toggleWorkingHours()">
                        <span class="text-gray-700 font-medium">Выходной день</span>
                    </label>
                </div>
                
                <div id="working_hours_section" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Время начала работы <span class="text-red-600">*</span></label>
                            <input type="time" id="start_time" name="start_time" value="{{ old('start_time', '09:00') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">Время окончания работы <span class="text-red-600">*</span></label>
                            <input type="time" id="end_time" name="end_time" value="{{ old('end_time', '18:00') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 mx-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Заметки</label>
                    <textarea id="notes" name="notes" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center p-3 border rounded-lg bg-gray-50 hover:bg-gray-100 transition-all cursor-pointer">
                        <input type="hidden" name="is_recurring" value="0">
                        <input type="checkbox" name="is_recurring" id="is_recurring" value="1" class="w-5 h-5 mr-3" onchange="toggleRecurring()">
                        <span class="text-gray-700 font-medium">Повторять еженедельно</span>
                    </label>
                </div>
                
                <div id="recurring_section" class="mb-6 hidden p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-blue-800 font-medium">Настройки повторения</span>
                    </div>
                    <label for="recurring_until" class="block text-sm font-medium text-gray-700 mb-2">Повторять до <span class="text-red-600">*</span></label>
                    <input type="date" id="recurring_until" name="recurring_until" value="{{ old('recurring_until', \Carbon\Carbon::parse($date)->addMonths(1)->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="border-t pt-6">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg transition-all">
                        Создать расписание
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleWorkingHours() {
            const isDayOff = document.getElementById('is_day_off').checked;
            const workingHoursSection = document.getElementById('working_hours_section');
            
            if (isDayOff) {
                workingHoursSection.classList.add('hidden');
                document.getElementById('start_time').removeAttribute('required');
                document.getElementById('end_time').removeAttribute('required');
            } else {
                workingHoursSection.classList.remove('hidden');
                document.getElementById('start_time').setAttribute('required', 'required');
                document.getElementById('end_time').setAttribute('required', 'required');
            }
        }
        
        function toggleRecurring() {
            const isRecurring = document.getElementById('is_recurring').checked;
            const recurringSection = document.getElementById('recurring_section');
            
            if (isRecurring) {
                recurringSection.classList.remove('hidden');
                document.getElementById('recurring_until').setAttribute('required', 'required');
            } else {
                recurringSection.classList.add('hidden');
                document.getElementById('recurring_until').removeAttribute('required');
            }
        }
    </script>
</x-app-layout> 