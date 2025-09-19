<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Редактировать запись
            </h2>
            <a href="{{ route('calendar.day', ['date' => $appointment->start_time->format('Y-m-d')]) }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад к календарю
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong>Ошибки:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Клиент -->
                            <div>
                                <label for="client_id" class="block text-sm font-medium text-gray-700">Клиент</label>
                                <select name="client_id" id="client_id" 
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                        required>
                                    <option value="">Выберите клиента</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ $appointment->client_id == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }} ({{ $client->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Мастер -->
                            <div>
                                <label for="master_id" class="block text-sm font-medium text-gray-700">Мастер</label>
                                <select name="master_id" id="master_id" 
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                        required>
                                    <option value="">Выберите мастера</option>
                                    @foreach($masters as $master)
                                        <option value="{{ $master->id }}" {{ $appointment->master_id == $master->id ? 'selected' : '' }}>
                                            {{ $master->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Услуга -->
                            <div>
                                <label for="service_id" class="block text-sm font-medium text-gray-700">Услуга</label>
                                <select name="service_id" id="service_id" 
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                        required>
                                    <option value="">Выберите услугу</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ $appointment->service_id == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }} ({{ number_format($service->price, 0, ',', ' ') }} €, {{ $service->duration }} мин)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        
                            <!-- Дата -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Дата</label>
                                <input type="date" name="date" id="date" 
                                       value="{{ $appointment->start_time->format('Y-m-d') }}" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                       required>
                            </div>

                            <!-- Время начала -->
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Время начала</label>
                                <input type="time" name="start_time" id="start_time" 
                                       value="{{ $appointment->start_time->format('H:i') }}" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                       required>
                            </div>

                            <!-- Время окончания -->
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">Время окончания</label>
                                <input type="time" name="end_time" id="end_time" 
                                       value="{{ $appointment->end_time->format('H:i') }}" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                       required>
                            </div>
                        </div>

                        <!-- Заметки -->
                        <div class="mt-6 mx-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Заметки</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Дополнительная информация...">{{ old('notes', $appointment->notes) }}</textarea>
                        </div>

                        <!-- Напоминание клиенту -->
                        <div class="mt-6 mx-4 bg-blue-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-medium text-blue-900">Напоминание клиенту</h3>
                                    <p class="text-sm text-blue-700">Отправить уведомление {{ $appointment->client->name ?? 'клиенту' }} о записи</p>
                                </div>
                                <button type="button" 
                                        onclick="openReminderModal()"
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    Отправить уведомление
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-8">
                            <div>
                                @if($appointment->status !== 'cancelled')
                                    <button type="button" 
                                            onclick="cancelAppointment({{ $appointment->id }})"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                            id="cancel-btn-{{ $appointment->id }}">
                                        Отменить запись
                                    </button>
                                @else
                                    <div class="flex items-center text-red-600">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Запись отменена</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="space-x-3">
                                <a href="{{ route('calendar.day', ['date' => $appointment->start_time->format('Y-m-d')]) }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Отмена
                                </a>
                                <button type="submit" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal для отправки напоминания -->
    <div id="reminderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 backdrop-blur-md hidden flex items-center justify-center z-50 overflow-hidden" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" style="max-height: 90vh; overflow-y: auto;">
            <div class="p-6">
                <!-- Заголовок -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Отправить напоминание</h3>
                    <button type="button" onclick="closeReminderModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Выбор шаблона -->
                <div class="mb-4">
                    <label for="templateSelect" class="block text-sm font-medium text-gray-700 mb-2">
                        Выберите шаблон
                    </label>
                    <select id="templateSelect" 
                            class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            onchange="loadTemplate()">
                        <option value="">Выберите шаблон...</option>
                    </select>
                </div>

                <!-- Превью сообщения -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Предварительный просмотр
                    </label>
                    <div id="messagePreview" class="bg-gray-50 border border-gray-300 rounded-md p-3 min-h-[100px] text-sm">
                        <em class="text-gray-500">Выберите шаблон для предварительного просмотра</em>
                    </div>
                </div>

                <!-- Действия -->
                <div class="flex justify-between items-center space-x-3">
                    <div class="text-sm text-gray-600">
                        <div class="flex items-center space-x-4">
                            <span>📱 {{ $appointment->client->phone ?? 'Телефон не указан' }}</span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" 
                                onclick="closeReminderModal()" 
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Отмена
                        </button>
                        <x-click-spark-button 
                                type="button" 
                                onclick="copyMessage()" 
                                id="copyBtn"
                                spark-color="#3b82f6"
                                spark-count="8"
                                spark-size="6"
                                duration="300"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                            📋 Скопировать
                        </x-click-spark-button>
                        <x-click-spark-button 
                                type="button" 
                                onclick="sendSMS()" 
                                id="smsBtn"
                                spark-color="#10b981"
                                spark-count="10"
                                spark-size="8"
                                duration="350"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                            📱 SMS
                        </x-click-spark-button>
                        <x-click-spark-button 
                                type="button" 
                                onclick="sendWhatsApp()" 
                                id="whatsappBtn"
                                spark-color="#059669"
                                spark-count="12"
                                spark-size="10"
                                duration="400"
                                class="bg-green-600 hover:bg-green-800 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                            💬 WhatsApp
                        </x-click-spark-button>
                    </div>
                </div>

                <!-- Быстрая ссылка на управление шаблонами -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('reminder-templates.index') }}" 
                       target="_blank"
                       class="text-sm text-blue-600 hover:text-blue-800">
                        Управление шаблонами →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Переменные для работы с напоминаниями
        let currentMessage = '';
        let currentPhone = '{{ $appointment->client->phone ?? "" }}';
        
        // Переменные для блокировки скролла
        let scrollPosition = 0;
        let isModalOpen = false;

        // Функции для полной блокировки скролла
        function disableScroll() {
            scrollPosition = window.pageYOffset;
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollPosition}px`;
            document.body.style.width = '100%';
            document.documentElement.style.overflow = 'hidden';
            isModalOpen = true;
        }

        function enableScroll() {
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('position');
            document.body.style.removeProperty('top');
            document.body.style.removeProperty('width');
            document.documentElement.style.removeProperty('overflow');
            window.scrollTo(0, scrollPosition);
            isModalOpen = false;
        }

        // Блокировка скролла через события
        function preventScroll(e) {
            if (isModalOpen) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }

        // Функции для работы с modal напоминаний
        async function openReminderModal() {
            console.log('Открываю модальное окно напоминания');
            document.getElementById('reminderModal').classList.remove('hidden');
            disableScroll();
            
            // Добавляем обработчики событий для блокировки скролла
            document.addEventListener('wheel', preventScroll, { passive: false });
            document.addEventListener('touchmove', preventScroll, { passive: false });
            document.addEventListener('keydown', function(e) {
                if (isModalOpen && [32, 33, 34, 35, 36, 37, 38, 39, 40].includes(e.keyCode)) {
                    e.preventDefault();
                }
            });
            
            console.log('Блокирую скроллинг полностью');
            await loadTemplates();
        }

        function closeReminderModal() {
            console.log('Закрываю модальное окно напоминания');
            document.getElementById('reminderModal').classList.add('hidden');
            enableScroll();
            
            // Удаляем обработчики событий
            document.removeEventListener('wheel', preventScroll);
            document.removeEventListener('touchmove', preventScroll);
            
            console.log('Разблокирую скроллинг полностью');
            resetModal();
        }

        function resetModal() {
            document.getElementById('templateSelect').value = '';
            document.getElementById('messagePreview').innerHTML = '<em class="text-gray-500">Выберите шаблон для предварительного просмотра</em>';
            currentMessage = '';
            updateButtonStates();
        }

        async function loadTemplates() {
            try {
                const response = await fetch('{{ route("reminder-templates.active") }}?client_id={{ $appointment->client_id }}');
                const templates = await response.json();
                
                const select = document.getElementById('templateSelect');
                select.innerHTML = '<option value="">Выберите шаблон...</option>';
                
                if (templates.length === 0) {
                    select.innerHTML += '<option value="" disabled>Нет активных шаблонов</option>';
                    return;
                }
                
                templates.forEach(template => {
                    const option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = template.name;
                    option.dataset.body = template.body;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Ошибка при загрузке шаблонов:', error);
                alert('Ошибка при загрузке шаблонов');
            }
        }

        async function loadTemplate() {
            const select = document.getElementById('templateSelect');
            const selectedOption = select.options[select.selectedIndex];
            
            if (!selectedOption.value) {
                resetModal();
                return;
            }
            
            try {
                const response = await fetch(`{{ route("reminder-templates.preview", ":id") }}?appointment_id={{ $appointment->id }}`.replace(':id', selectedOption.value));
                const data = await response.json();
                
                currentMessage = data.preview;
                document.getElementById('messagePreview').innerHTML = currentMessage.replace(/\n/g, '<br>');
                updateButtonStates();
            } catch (error) {
                console.error('Ошибка при загрузке превью:', error);
                alert('Ошибка при загрузке превью шаблона');
            }
        }

        function updateButtonStates() {
            const hasMessage = currentMessage.trim() !== '';
            const hasPhone = currentPhone.trim() !== '';
            
            document.getElementById('copyBtn').disabled = !hasMessage;
            document.getElementById('smsBtn').disabled = !hasMessage || !hasPhone;
            document.getElementById('whatsappBtn').disabled = !hasMessage || !hasPhone;
        }

        async function copyMessage() {
            if (!currentMessage) {
                alert('Выберите шаблон для копирования');
                return;
            }
            
            try {
                await navigator.clipboard.writeText(currentMessage);
                
                const btn = document.getElementById('copyBtn');
                const originalText = btn.textContent;
                btn.textContent = '✅ Скопировано';
                btn.classList.add('bg-green-500');
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('bg-green-500');
                }, 2000);
            } catch (error) {
                console.error('Ошибка при копировании:', error);
                alert('Не удалось скопировать сообщение');
            }
        }

        function sendSMS() {
            if (!currentMessage || !currentPhone) {
                alert('Не выбран шаблон или не указан телефон клиента');
                return;
            }
            
            // Очищаем номер телефона от лишних символов
            const cleanPhone = currentPhone.replace(/[^\d+]/g, '');
            const smsUrl = `sms:${cleanPhone}?body=${encodeURIComponent(currentMessage)}`;
            
            window.open(smsUrl, '_blank');
        }

        function sendWhatsApp() {
            if (!currentMessage || !currentPhone) {
                alert('Не выбран шаблон или не указан телефон клиента');
                return;
            }
            
            // Очищаем номер телефона от лишних символов и добавляем код России если нужно
            let cleanPhone = currentPhone.replace(/[^\d+]/g, '');
            if (cleanPhone.startsWith('8')) {
                cleanPhone = '+7' + cleanPhone.substring(1);
            } else if (cleanPhone.startsWith('9') && cleanPhone.length === 10) {
                cleanPhone = '+7' + cleanPhone;
            }
            
            const whatsappUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(currentMessage)}`;
            
            window.open(whatsappUrl, '_blank');
        }

        // Закрытие modal при клике вне его
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('reminderModal');
            if (event.target === modal) {
                closeReminderModal();
            }
        });

        // Закрытие modal при нажатии Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && isModalOpen) {
                const modal = document.getElementById('reminderModal');
                if (!modal.classList.contains('hidden')) {
                    closeReminderModal();
                }
            }
        });

        function cancelAppointment(appointmentId) {
            console.log('cancelAppointment called for ID:', appointmentId);
            
            // Показываем подтверждение
            if (!confirm('Вы уверены, что хотите отменить эту запись?')) {
                console.log('User cancelled the action');
                return;
            }

            const button = document.getElementById('cancel-btn-' + appointmentId);
            const originalText = button.textContent;
            
            // Отключаем кнопку и меняем текст
            button.disabled = true;
            button.textContent = 'Отменяем...';
            button.classList.add('opacity-50', 'cursor-not-allowed');

            // Получаем CSRF токен
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            console.log('Sending AJAX request to cancel appointment');
            console.log('CSRF Token:', token);
            
            // Отправляем AJAX запрос
            fetch('{{ route("appointments.cancel", $appointment) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({})
            })
            .then(response => {
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Success:', data);
                
                // Перенаправляем на страницу календаря
                window.location.href = '{{ route("calendar.day", ["date" => $appointment->start_time->format("Y-m-d")]) }}';
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Восстанавливаем кнопку
                button.disabled = false;
                button.textContent = originalText;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                
                alert('Произошла ошибка при отмене записи. Попробуйте еще раз.');
            });
        }

        // Debug information
        @if(config('app.debug'))
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Page loaded, running debug checks...');
                console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
                console.log('Cancel button found:', document.querySelector('[id^="cancel-btn-"]') !== null);
            });
        @endif
    </script>
</x-app-layout> 