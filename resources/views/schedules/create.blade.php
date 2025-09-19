<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 animate-in fade-in duration-1000">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white shadow-sm border-b border-gray-100 animate-in slide-in-from-top duration-500">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('schedules.index') }}" 
                       class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-lg font-semibold text-gray-900 animate-in slide-in-from-left duration-700">Создать расписание</h1>
                </div>
            </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden lg:block bg-white shadow-sm animate-in slide-in-from-top duration-700">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4 h-16 animate-in slide-in-from-left duration-700">
                    <a href="{{ route('schedules.index') }}" 
                       class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-200 group">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="font-medium">К расписанию</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent animate-in zoom-in duration-1000 delay-300">
                        📅 Создать расписание
                    </h1>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex items-center justify-center py-6 px-4">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom duration-700 delay-200">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-8 text-white text-center animate-in slide-in-from-top duration-500 delay-400">
                    <div class="animate-in zoom-in duration-700 delay-600">
                        <h2 class="text-3xl font-bold mb-2">📅 Новое расписание</h2>
                        <p class="text-green-100 text-lg">
                            на {{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('DD MMMM YYYY') }}
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-6 lg:p-8 space-y-6">
                    <form method="POST" action="{{ route('schedules.store') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Date Field -->
                        <div class="animate-in slide-in-from-left duration-500 delay-700">
                            <label for="date" class="block text-sm font-semibold text-gray-700 mb-2 animate-in fade-in duration-300 delay-800">
                                📅 Дата
                            </label>
                            <input type="date" 
                                   id="date" 
                                   name="date" 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-300 text-lg font-medium hover:border-green-300 animate-in slide-in-from-right duration-300 delay-900"
                                   value="{{ $date }}" 
                                   required>
                            @error('date')
                                <p class="mt-2 text-sm text-red-600 animate-in slide-in-from-top duration-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Day Type Toggle -->
                        <div class="animate-in slide-in-from-right duration-500 delay-800">
                            <label class="block text-sm font-semibold text-gray-700 mb-3 animate-in fade-in duration-300 delay-900">
                                🔄 Тип дня
                            </label>
                            <div class="bg-green-50 rounded-xl p-4 border-2 border-green-100 hover:border-green-200 transition-all duration-300 hover:shadow-lg animate-in zoom-in duration-300 delay-1000">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="relative">
                                            <input type="hidden" name="is_day_off" id="isDayOff" value="0">
                                            <button type="button" 
                                                    id="dayOffToggle" 
                                                    onclick="toggleDayOff()"
                                                    class="relative w-14 h-8 bg-gray-300 rounded-full transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-100 transform hover:scale-105">
                                                <div id="toggleSlider" 
                                                     class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md transform transition-all duration-300 flex items-center justify-center">
                                                    <span id="toggleIcon" class="text-xs animate-in spin-in duration-500">💼</span>
                                                </div>
                                            </button>
                                        </div>
                                        <div class="animate-in slide-in-from-left duration-300 delay-1100">
                                            <span id="dayOffLabel" class="text-lg font-medium text-gray-900">
                                                Рабочий день
                                            </span>
                                            <p class="text-sm text-gray-500">
                                                Переключите для выходного дня
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Working Hours -->
                        <div id="workingHours" class="animate-in slide-in-from-left duration-500 delay-900">
                            <label class="block text-sm font-semibold text-gray-700 mb-3 animate-in fade-in duration-300 delay-1000">
                                ⏰ Рабочие часы
                            </label>
                            <div class="bg-blue-50 rounded-xl p-4 border-2 border-blue-100 space-y-4 animate-in zoom-in duration-300 delay-1100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="animate-in slide-in-from-left duration-300 delay-1200">
                                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                            🌅 Время начала
                                        </label>
                                        <input type="time" 
                                               id="start_time" 
                                               name="start_time" 
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 text-lg font-mono hover:border-blue-300"
                                               value="09:00"
                                               required>
                                        @error('start_time')
                                            <p class="mt-2 text-sm text-red-600 animate-in slide-in-from-top duration-300">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                    <div class="animate-in slide-in-from-right duration-300 delay-1300">
                                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                            🌅 Время окончания
                                        </label>
                                        <input type="time" 
                                               id="end_time" 
                                               name="end_time" 
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 text-lg font-mono hover:border-blue-300"
                                               value="18:00"
                                               required>
                                        @error('end_time')
                                            <p class="mt-2 text-sm text-red-600 animate-in slide-in-from-top duration-300">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Options -->
                        <div id="recurringSection" class="animate-in slide-in-from-right duration-500 delay-1000">
                            <div class="bg-purple-50 rounded-xl p-4 border-2 border-purple-100 animate-in zoom-in duration-300 delay-1200">
                                <div class="flex items-center space-x-3 mb-4 animate-in slide-in-from-left duration-300 delay-1300">
                                    <input type="hidden" name="is_recurring" value="0">
                                    <input type="checkbox" 
                                           class="w-5 h-5 text-purple-600 border-2 border-gray-300 rounded focus:ring-purple-500 focus:ring-2 transition-all duration-200 transform hover:scale-110" 
                                           id="is_recurring" 
                                           name="is_recurring" 
                                           value="1" 
                                           onchange="toggleRecurring()">
                                    <label for="is_recurring" class="text-lg font-medium text-gray-900 cursor-pointer">
                                        🔄 Повторять еженедельно
                                    </label>
                                </div>
                                
                                <div id="recurringUntil" class="hidden animate-in slide-in-from-bottom duration-300">
                                    <label for="recurring_until" class="block text-sm font-medium text-gray-700 mb-2">
                                        📆 До какой даты повторять
                                    </label>
                                    <input type="date" 
                                           id="recurring_until" 
                                           name="recurring_until" 
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-300 hover:border-purple-300">
                                    @error('recurring_until')
                                        <p class="mt-2 text-sm text-red-600 animate-in slide-in-from-top duration-300">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="animate-in slide-in-from-left duration-500 delay-1100 mx-4">
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2 animate-in fade-in duration-300 delay-1200">
                                📝 Заметки (необязательно)
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="4"
                                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-gray-400 focus:ring-4 focus:ring-gray-100 transition-all duration-300 resize-none hover:border-gray-300 animate-in slide-in-from-right duration-300 delay-1300"
                                      placeholder="Дополнительная информация о рабочем дне...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600 animate-in slide-in-from-top duration-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6 animate-in slide-in-from-bottom duration-500 delay-1200">
                            <a href="{{ route('schedules.index') }}" 
                               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white text-center py-4 px-6 rounded-xl font-semibold transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg flex items-center justify-center space-x-2 animate-in slide-in-from-left duration-300 delay-1300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>Отмена</span>
                            </a>
                            <button type="submit" 
                                    class="flex-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white py-4 px-8 rounded-xl font-bold transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-xl flex items-center justify-center space-x-2 animate-in slide-in-from-right duration-300 delay-1400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Создать расписание</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDayOff() {
            const toggle = document.getElementById('dayOffToggle');
            const slider = document.getElementById('toggleSlider');
            const icon = document.getElementById('toggleIcon');
            const label = document.getElementById('dayOffLabel');
            const input = document.getElementById('isDayOff');
            const workingHours = document.getElementById('workingHours');
            const recurringSection = document.getElementById('recurringSection');
            
            const isCurrentlyOff = input.value === '1';
            
            if (isCurrentlyOff) {
                // Switch to working day
                toggle.classList.remove('bg-red-500');
                toggle.classList.add('bg-gray-300');
                slider.classList.remove('translate-x-6');
                icon.textContent = '💼';
                label.textContent = 'Рабочий день';
                input.value = '0';
                
                // Show working hours and recurring options with animations
                workingHours.classList.remove('opacity-50', 'pointer-events-none');
                workingHours.classList.add('animate-in', 'fade-in', 'duration-300');
                recurringSection.classList.remove('opacity-50', 'pointer-events-none');
                recurringSection.classList.add('animate-in', 'fade-in', 'duration-300');
                
                // Re-enable time fields
                document.getElementById('start_time').value = '09:00';
                document.getElementById('end_time').value = '18:00';
                document.getElementById('start_time').required = true;
                document.getElementById('end_time').required = true;
                
            } else {
                // Switch to day off
                toggle.classList.remove('bg-gray-300');
                toggle.classList.add('bg-red-500');
                slider.classList.add('translate-x-6');
                icon.textContent = '🏖️';
                label.textContent = 'Выходной день';
                input.value = '1';
                
                // Hide working hours and recurring options with animations
                workingHours.classList.add('opacity-50', 'pointer-events-none');
                workingHours.classList.add('animate-out', 'fade-out', 'duration-300');
                recurringSection.classList.add('opacity-50', 'pointer-events-none');
                recurringSection.classList.add('animate-out', 'fade-out', 'duration-300');
                
                // Clear and disable time fields
                document.getElementById('start_time').value = '';
                document.getElementById('end_time').value = '';
                document.getElementById('start_time').required = false;
                document.getElementById('end_time').required = false;
                
                // Disable recurring
                document.getElementById('is_recurring').checked = false;
                toggleRecurring();
            }
        }

        function toggleRecurring() {
            const checkbox = document.getElementById('is_recurring');
            const recurringUntil = document.getElementById('recurringUntil');
            const recurringUntilInput = document.getElementById('recurring_until');
            
            if (checkbox.checked) {
                recurringUntil.classList.remove('hidden');
                recurringUntil.classList.add('animate-in', 'slide-in-from-bottom', 'duration-300');
                recurringUntilInput.required = true;
            } else {
                recurringUntil.classList.add('animate-out', 'slide-out-to-bottom', 'duration-300');
                setTimeout(() => {
                    recurringUntil.classList.add('hidden');
                    recurringUntil.classList.remove('animate-out', 'slide-out-to-bottom');
                }, 300);
                recurringUntilInput.required = false;
            }
        }

        // Set minimum date for recurring_until
        document.getElementById('date').addEventListener('change', function() {
            const selectedDate = this.value;
            const recurringUntilInput = document.getElementById('recurring_until');
            if (selectedDate) {
                recurringUntilInput.min = selectedDate;
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            const recurringUntilInput = document.getElementById('recurring_until');
            if (dateInput.value) {
                recurringUntilInput.min = dateInput.value;
            }
            
            // Add loading animation to form elements
            const formElements = document.querySelectorAll('input, textarea, button');
            formElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 50}ms`;
            });
        });
    </script>
</x-app-layout> 