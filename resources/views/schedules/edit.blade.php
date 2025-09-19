<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-red-100 animate-in fade-in duration-1000">
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
                    <h1 class="text-lg font-semibold text-gray-900 animate-in slide-in-from-left duration-700">Редактировать</h1>
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
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent animate-in zoom-in duration-1000 delay-300">
                        ✏️ Редактировать расписание
                    </h1>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        @if(session('success'))
                            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                                <div class="font-medium">{{ session('success') }}</div>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                                <div class="font-medium">Ошибки при сохранении:</div>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('schedules.update', $schedule) }}" id="scheduleForm">
                            @csrf
                            @method('PUT')
                            <!-- Day Type Toggle -->
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">🔄 Тип дня</label>
                                <div class="bg-orange-50 rounded-xl p-4 border-2 border-orange-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="relative">
                                            <input type="hidden" name="is_day_off" id="is_day_off" value="{{ $schedule->is_day_off ? '1' : '0' }}">
                                            <button type="button" id="dayOffToggle" onclick="toggleDayOff()" class="relative w-14 h-8 {{ $schedule->is_day_off ? 'bg-red-500' : 'bg-gray-300' }} rounded-full transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-orange-100 transform hover:scale-105">
                                                <div id="toggleSlider" class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md transform transition-all duration-300 flex items-center justify-center {{ $schedule->is_day_off ? 'translate-x-6' : '' }}">
                                                    <span id="toggleIcon" class="text-xs">{{ $schedule->is_day_off ? '🏖️' : '💼' }}</span>
                                                </div>
                                            </button>
                                        </div>
                                        <span id="dayOffLabel" class="text-lg font-medium text-gray-900">
                                            {{ $schedule->is_day_off ? 'Выходной день' : 'Рабочий день' }}
                                        </span>
                                        <p class="text-sm text-gray-500">
                                            {{ $schedule->is_day_off ? 'Отмечено как выходной' : 'Переключите для выходного дня' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Working Hours -->
                            <div id="workingHours" class="mb-4 {{ $schedule->is_day_off ? 'opacity-50 pointer-events-none' : '' }}">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">⏰ Рабочие часы</label>
                                <div class="bg-blue-50 rounded-xl p-4 border-2 border-blue-100">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">🌅 Время начала</label>
                                            <input type="time" id="start_time" name="start_time" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 text-lg font-mono hover:border-blue-300" value="{{ $schedule->start_time }}" {{ $schedule->is_day_off ? '' : 'required' }}>
                                            @error('start_time')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">🌅 Время окончания</label>
                                            <input type="time" id="end_time" name="end_time" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 text-lg font-mono hover:border-blue-300" value="{{ $schedule->end_time }}" {{ $schedule->is_day_off ? '' : 'required' }}>
                                            @error('end_time')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Notes -->
                            <div class="mb-6 mx-4">
                                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">📝 Заметки (необязательно)</label>
                                <textarea id="notes" name="notes" rows="4" class="block mt-1 w-full border-gray-300 focus:border-gray-400 focus:ring-4 focus:ring-gray-100 rounded-xl shadow-sm">{{ $schedule->notes }}</textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center justify-end">
                                <a href="{{ route('schedules.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    К расписанию
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    Сохранить
                                </button>
                            </div>
                        </form>
                    </div>
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
            const input = document.getElementById('is_day_off');
            const workingHours = document.getElementById('workingHours');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            
            const isCurrentlyOff = input.value === '1';
            
            if (isCurrentlyOff) {
                // Switch to working day
                toggle.classList.remove('bg-red-500');
                toggle.classList.add('bg-gray-300');
                slider.classList.remove('translate-x-6');
                icon.textContent = '💼';
                label.textContent = 'Рабочий день';
                input.value = '0';
                
                // Show working hours with animations
                workingHours.classList.remove('opacity-50', 'pointer-events-none');
                workingHours.classList.add('animate-in', 'fade-in', 'duration-300');
                
                // Add required attributes and set default values if empty
                startTime.required = true;
                endTime.required = true;
                
                if (!startTime.value) {
                    startTime.value = '09:00';
                }
                if (!endTime.value) {
                    endTime.value = '18:00';
                }
                
            } else {
                // Switch to day off
                toggle.classList.remove('bg-gray-300');
                toggle.classList.add('bg-red-500');
                slider.classList.add('translate-x-6');
                icon.textContent = '🏖️';
                label.textContent = 'Выходной день';
                input.value = '1';
                
                // Hide working hours with animations
                workingHours.classList.add('opacity-50', 'pointer-events-none');
                workingHours.classList.add('animate-out', 'fade-out', 'duration-300');
                
                // Remove required attributes and clear values
                startTime.required = false;
                endTime.required = false;
                startTime.value = '';
                endTime.value = '';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure form submission includes all fields
            document.getElementById('scheduleForm').addEventListener('submit', function(e) {
                // Enable all disabled inputs before form submission
                document.querySelectorAll('input[disabled]').forEach(input => {
                    input.disabled = false;
                });
                
                return true;
            });

            // Add staggered animations to form elements
            const formElements = document.querySelectorAll('input, textarea, button');
            formElements.forEach((element, index) => {
                element.style.animationDelay = `${800 + index * 50}ms`;
            });

            // Add hover effects to interactive elements
            const interactiveElements = document.querySelectorAll('input, textarea, button, a');
            interactiveElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.classList.add('animate-in', 'zoom-in', 'duration-200');
                });
                
                element.addEventListener('mouseleave', function() {
                    this.classList.remove('animate-in', 'zoom-in', 'duration-200');
                });
            });
        });
    </script>
</x-app-layout> 