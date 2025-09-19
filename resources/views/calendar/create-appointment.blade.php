<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 animate-in fade-in duration-1000">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white shadow-sm border-b border-gray-100 animate-in slide-in-from-top duration-500">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('calendar.day', ['date' => $date]) }}" 
                           class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        <h1 class="text-lg font-semibold text-gray-900 animate-in slide-in-from-left duration-700">Новая запись</h1>
                    </div>
                </div>
                <!-- Mobile Date Info -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg p-3 text-white animate-in slide-in-from-bottom duration-500">
                    <div class="flex items-center space-x-2 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">
                            {{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('DD MMMM') }} в {{ $time }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden lg:block bg-white shadow-sm animate-in slide-in-from-top duration-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4 animate-in slide-in-from-left duration-700">
                        <a href="{{ route('calendar.day', ['date' => $date]) }}" 
                           class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-200 group">
                            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span class="font-medium">Назад к календарю</span>
                        </a>
                        <div class="h-6 w-px bg-gray-300"></div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent animate-in zoom-in duration-1000 delay-300">
                            📅 Новая запись
                        </h1>
                    </div>
                    <!-- Desktop Date Info -->
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-xl shadow-lg animate-in slide-in-from-right duration-700">
                        <div class="flex items-center space-x-2 text-sm font-medium">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('dddd, DD MMMM YYYY') }} в {{ $time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom duration-1000 delay-200">
                <div class="p-4 lg:p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-6 bg-gradient-to-r from-green-100 to-emerald-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm animate-in slide-in-from-top duration-500">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-gradient-to-r from-red-100 to-pink-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm animate-in slide-in-from-top duration-500">
                            <div class="flex items-start space-x-2">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-medium mb-2">Исправьте следующие ошибки:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('calendar.store-appointment') }}" class="space-y-6 animate-in fade-in duration-700 delay-300">
                        @csrf
                        
                        <!-- Hidden fields -->
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="time" value="{{ $time }}">

                        <!-- Мастер и Услуга -->
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-4 lg:p-6 animate-in slide-in-from-bottom duration-500 delay-400">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                                <span>👨‍💼</span>
                                <span>Основная информация</span>
                            </h3>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                                <!-- Мастер -->
                                <div class="animate-in slide-in-from-left duration-500 delay-500">
                                    <label for="master_id" class="block text-sm font-semibold text-gray-700 mb-2">Мастер *</label>
                                    <select name="master_id" id="master_id" 
                                            class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300" 
                                            required>
                                        <option value="">Выберите мастера</option>
                                        @foreach($masters as $master)
                                            <option value="{{ $master->id }}" {{ (old('master_id', $masterId) == $master->id) ? 'selected' : '' }}>
                                                {{ $master->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Услуга -->
                                <div class="animate-in slide-in-from-right duration-500 delay-600">
                                    <label for="service_id" class="block text-sm font-semibold text-gray-700 mb-2">Услуга *</label>
                                    <select name="service_id" id="service_id" 
                                            class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300" 
                                            required>
                                        <option value="">Выберите услугу</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" 
                                                    data-duration="{{ $service->duration }}" 
                                                    data-price="{{ $service->price }}"
                                                    {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }} ({{ number_format($service->price, 0, ',', ' ') }} €, {{ $service->duration }} мин)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Время и Дата -->
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-4 lg:p-6 animate-in slide-in-from-bottom duration-500 delay-700">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                                <span>⏰</span>
                                <span>Время проведения</span>
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                                <!-- Дата -->
                                <div class="animate-in slide-in-from-left duration-500 delay-800">
                                    <label for="date_display" class="block text-sm font-semibold text-gray-700 mb-2">Дата</label>
                                    <input type="date" id="date_display" 
                                           value="{{ $date }}" 
                                           class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 bg-gray-50 text-gray-600 cursor-not-allowed" 
                                           readonly>
                                </div>

                                <!-- Время начала -->
                                <div class="animate-in slide-in-from-bottom duration-500 delay-900">
                                    <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-2">Время начала *</label>
                                    <input type="time" name="start_time" id="start_time" 
                                           value="{{ old('start_time', $time) }}" 
                                           class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300" 
                                           required>
                                </div>

                                <!-- Время окончания -->
                                <div class="animate-in slide-in-from-right duration-500 delay-1000">
                                    <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-2">Время окончания *</label>
                                    <input type="time" name="end_time" id="end_time" 
                                           value="{{ old('end_time') }}"
                                           class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300" 
                                           required>
                                    <div class="mt-3">
                                        <span id="duration-display" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span id="duration-text" class="font-semibold">0 мин</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Клиент -->
                        <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-4 lg:p-6 animate-in slide-in-from-bottom duration-500 delay-1100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                                <span>👤</span>
                                <span>Информация о клиенте</span>
                            </h3>
                            <div class="animate-in slide-in-from-bottom duration-500 delay-1200">
                                <label for="client_select" class="block text-sm font-semibold text-gray-700 mb-2">Клиент</label>
                                <select id="client_select" name="client_id" 
                                        class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300">
                                    <option value="">Выберите клиента</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }} ({{ $client->phone }})
                                        </option>
                                    @endforeach
                                    <option value="new" {{ old('client_id') == 'new' ? 'selected' : '' }}>+ Новый клиент</option>
                                </select>
                            </div>

                            <!-- Форма нового клиента -->
                            <div id="new-client-form" class="{{ old('client_id') == 'new' ? '' : 'hidden' }} mt-6 border-2 border-dashed border-blue-200 rounded-xl p-4 lg:p-6 bg-blue-50 animate-in fade-in duration-300">
                                <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                                    <span>✨</span>
                                    <span>Данные нового клиента</span>
                                </h4>
                                
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <label for="client_name" class="block text-sm font-semibold text-gray-700 mb-2">Имя *</label>
                                        <input type="text" id="client_name" name="client_name" 
                                               value="{{ old('client_name') }}"
                                               class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300"
                                               placeholder="Введите имя клиента">
                                    </div>

                                    <div>
                                        <label for="client_phone" class="block text-sm font-semibold text-gray-700 mb-2">Телефон *</label>
                                        <input type="tel" id="client_phone" name="client_phone" 
                                               value="{{ old('client_phone') }}"
                                               class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300"
                                               placeholder="+372 12345678">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Заметки -->
                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-4 lg:p-6 animate-in slide-in-from-bottom duration-500 delay-1300">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                                <span>📝</span>
                                <span>Дополнительная информация</span>
                            </h3>
                            <div class="animate-in slide-in-from-bottom duration-500 delay-1400">
                                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Заметки</label>
                                <textarea name="notes" id="notes" rows="4" 
                                          class="block w-full border-2 border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300 resize-none"
                                          placeholder="Дополнительная информация о записи, пожелания клиента, особые требования...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-4 animate-in slide-in-from-bottom duration-500 delay-1500">
                            <a href="{{ route('calendar.day', ['date' => $date]) }}" 
                               class="w-full sm:w-auto bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 text-center flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>Отмена</span>
                            </a>
                            <button type="submit" 
                                    class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Создать запись</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calculate duration between start and end times
        function calculateDuration() {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime && endTime) {
                const [startHours, startMinutes] = startTime.split(':');
                const [endHours, endMinutes] = endTime.split(':');
                
                const start = new Date();
                start.setHours(parseInt(startHours), parseInt(startMinutes), 0, 0);
                
                const end = new Date();
                end.setHours(parseInt(endHours), parseInt(endMinutes), 0, 0);
                
                // Handle case where end time is next day
                if (end <= start) {
                    end.setDate(end.getDate() + 1);
                }
                
                const durationMs = end.getTime() - start.getTime();
                const durationMinutes = Math.round(durationMs / 60000);
                
                // Update badge color based on duration
                const badge = document.getElementById('duration-display');
                if (durationMinutes < 15) {
                    badge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-red-100 to-pink-100 text-red-800 shadow-sm';
                    badge.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="duration-text" class="font-semibold">' + durationMinutes + ' мин</span>';
                } else if (durationMinutes > 480) {
                    badge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-yellow-100 to-orange-100 text-yellow-800 shadow-sm';
                    badge.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="duration-text" class="font-semibold">' + durationMinutes + ' мин</span>';
                } else {
                    badge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 shadow-sm';
                    badge.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="duration-text" class="font-semibold">' + durationMinutes + ' мин</span>';
                }
                
                return durationMinutes;
            }
            
            const badge = document.getElementById('duration-display');
            badge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm';
            badge.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="duration-text" class="font-semibold">0 мин</span>';
            return 0;
        }

        // Auto-calculate end time based on service duration
        document.getElementById('service_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const duration = parseInt(selectedOption.getAttribute('data-duration')) || 60;
            const startTime = document.getElementById('start_time').value;
            
            if (startTime) {
                const [hours, minutes] = startTime.split(':');
                const start = new Date();
                start.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                
                const end = new Date(start.getTime() + duration * 60000);
                const endTimeString = end.toTimeString().substr(0, 5);
                
                document.getElementById('end_time').value = endTimeString;
                calculateDuration();
            }
        });

        // Update end time when start time changes (only if service is selected)
        document.getElementById('start_time').addEventListener('change', function() {
            const serviceSelect = document.getElementById('service_id');
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            
            if (selectedOption.value) {
                const duration = parseInt(selectedOption.getAttribute('data-duration')) || 60;
                
                const startTime = this.value;
                if (startTime) {
                    const [hours, minutes] = startTime.split(':');
                    const start = new Date();
                    start.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                    
                    const end = new Date(start.getTime() + duration * 60000);
                    const endTimeString = end.toTimeString().substr(0, 5);
                    
                    document.getElementById('end_time').value = endTimeString;
                }
            }
            
            calculateDuration();
        });

        // Update duration when end time changes manually
        document.getElementById('end_time').addEventListener('change', calculateDuration);

        // Show/hide new client form
        document.getElementById('client_select').addEventListener('change', function() {
            const newClientForm = document.getElementById('new-client-form');
            if (this.value === 'new') {
                newClientForm.classList.remove('hidden');
                document.getElementById('client_name').setAttribute('required', '');
                document.getElementById('client_phone').setAttribute('required', '');
            } else {
                newClientForm.classList.add('hidden');
                document.getElementById('client_name').removeAttribute('required');
                document.getElementById('client_phone').removeAttribute('required');
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateDuration();
            
            // If no end time is set and we have a start time, trigger change event
            if (document.getElementById('start_time').value && !document.getElementById('end_time').value) {
                document.getElementById('start_time').dispatchEvent(new Event('change'));
            }
            
            // Check if new client form should be shown on page load (for old values)
            const clientSelect = document.getElementById('client_select');
            if (clientSelect.value === 'new') {
                document.getElementById('client_name').setAttribute('required', '');
                document.getElementById('client_phone').setAttribute('required', '');
            }
        });
    </script>
</x-app-layout> 