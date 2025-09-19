<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">Редактирование расписания</h1>
                        <div>
                            <a href="{{ route('salon.schedules.index', ['master_id' => $master->id]) }}"
                               class="block w-full sm:w-auto text-center bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded text-sm sm:text-base whitespace-normal break-words transition">
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
                            <div class="text-gray-600">Редактирование расписания на {{ $schedule->date->format('d.m.Y') }}</div>
                        </div>

                        <form action="{{ route('salon.schedules.update', $schedule) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="master_id" value="{{ $master->id }}">
                            <div class="mb-6">
                                <label class="flex items-center p-3 border rounded-lg bg-gray-50 hover:bg-gray-100 transition-all cursor-pointer">
                                    <input type="hidden" name="is_day_off" value="0">
                                    <input type="checkbox" name="is_day_off" id="is_day_off" value="1" class="w-5 h-5 mr-3" onchange="toggleWorkingHours()" {{ $schedule->is_day_off ? 'checked' : '' }}>
                                    <span class="text-gray-700 font-medium">Выходной день</span>
                                </label>
                            </div>
                            <div id="working_hours_section" class="mb-6 {{ $schedule->is_day_off ? 'hidden' : '' }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Время начала работы <span class="text-red-600">*</span></label>
                                        <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $schedule->start_time) }}" {{ $schedule->is_day_off ? '' : 'required' }}
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">Время окончания работы <span class="text-red-600">*</span></label>
                                        <input type="time" id="end_time" name="end_time" value="{{ old('end_time', $schedule->end_time) }}" {{ $schedule->is_day_off ? '' : 'required' }}
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-6 mx-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Заметки</label>
                                <textarea id="notes" name="notes" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $schedule->notes) }}</textarea>
                            </div>
                            <div class="border-t pt-6 flex space-x-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg transition-all">
                                    Сохранить изменения
                                </button>
                                <form action="{{ route('salon.schedules.destroy', $schedule) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить это расписание?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="master_id" value="{{ $master->id }}">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-6 rounded-lg transition-all">
                                        Удалить расписание
                                    </button>
                                </form>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
    </script>
</x-app-layout> 