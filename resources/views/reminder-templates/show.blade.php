<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Просмотр шаблона: ') . $reminderTemplate->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('reminder-templates.edit', $reminderTemplate) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Редактировать
                </a>
                <a href="{{ route('reminder-templates.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Назад к списку
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Информация о шаблоне -->
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">
                                    Информация о шаблоне
                                </h3>
                                <div class="space-y-2">
                                    <div>
                                        <strong class="text-gray-700">Название:</strong>
                                        <span class="ml-2">{{ $reminderTemplate->name }}</span>
                                    </div>
                                    <div>
                                        <strong class="text-gray-700">Статус:</strong>
                                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $reminderTemplate->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $reminderTemplate->is_active ? 'Активен' : 'Неактивен' }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong class="text-gray-700">Создан:</strong>
                                        <span class="ml-2">{{ $reminderTemplate->created_at->format('d.m.Y H:i') }}</span>
                                    </div>
                                    <div>
                                        <strong class="text-gray-700">Обновлен:</strong>
                                        <span class="ml-2">{{ $reminderTemplate->updated_at->format('d.m.Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">
                                    Текст шаблона
                                </h3>
                                <div class="bg-white p-3 rounded border border-gray-300 text-sm">
                                    {!! nl2br(e($reminderTemplate->body)) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Превью и плейсхолдеры -->
                        <div class="space-y-4">
                            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">
                                    Превью с примерами данных
                                </h3>
                                <div id="preview" class="bg-white p-3 rounded border border-gray-300 text-sm">
                                    <!-- Превью будет заполнено JavaScript -->
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">
                                    Доступные плейсхолдеры
                                </h3>
                                <div class="space-y-2 text-sm">
                                    @foreach($placeholders as $placeholder => $description)
                                        <div class="flex justify-between items-center">
                                            <code class="bg-gray-200 px-2 py-1 rounded text-purple-600">
                                                {{ $placeholder }}
                                            </code>
                                            <span class="text-gray-600">{{ $description }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                <h4 class="text-sm font-medium text-yellow-800 mb-2">
                                    📋 Использование шаблона:
                                </h4>
                                <ul class="text-sm text-yellow-700 space-y-1">
                                    <li>• Шаблон можно использовать при редактировании записи</li>
                                    <li>• Плейсхолдеры автоматически заменятся на данные записи</li>
                                    <li>• Готовое сообщение можно скопировать или отправить</li>
                                    <li>• Поддерживается отправка через SMS и WhatsApp</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <form method="POST" action="{{ route('reminder-templates.destroy', $reminderTemplate) }}" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                    onclick="return confirm('Вы уверены, что хотите удалить этот шаблон?')">
                                Удалить шаблон
                            </button>
                        </form>
                        <a href="{{ route('reminder-templates.edit', $reminderTemplate) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Редактировать
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updatePreview() {
            const bodyText = @json($reminderTemplate->body);
            const previewDiv = document.getElementById('preview');
            
            // Заменяем плейсхолдеры примерами для превью
            let preview = bodyText;
                            preview = preview.replace(/{client_name}/g, 'Николь');
                preview = preview.replace(/{service_name}/g, 'Маникюр');
                preview = preview.replace(/{date_time}/g, '15.07.2025 в 14:30');
                preview = preview.replace(/{price}/g, '25 euro');
                preview = preview.replace(/{studio_address}/g, 'ул. Красная, 15');
            
            previewDiv.innerHTML = preview.replace(/\n/g, '<br>');
        }
        
        // Обновляем превью при загрузке страницы
        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</x-app-layout> 