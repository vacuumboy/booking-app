<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Редактировать шаблон: ') . $reminderTemplate->name }}
            </h2>
            <a href="{{ route('reminder-templates.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад к списку
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('reminder-templates.update', $reminderTemplate) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Форма редактирования -->
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Название шаблона
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name', $reminderTemplate->name) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           required>
                                </div>

                                <div>
                                    <label for="language" class="block text-sm font-medium text-gray-700">
                                        Язык шаблона
                                    </label>
                                    <select name="language" 
                                            id="language" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required>
                                        <option value="">Выберите язык</option>
                                        <option value="ru" {{ old('language', $reminderTemplate->language) == 'ru' ? 'selected' : '' }}>Русский</option>
                                        <option value="lv" {{ old('language', $reminderTemplate->language) == 'lv' ? 'selected' : '' }}>Латышский</option>
                                        <option value="en" {{ old('language', $reminderTemplate->language) == 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="body" class="block text-sm font-medium text-gray-700">
                                        Текст шаблона
                                    </label>
                                    <textarea name="body" 
                                              id="body" 
                                              rows="8"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                              placeholder="Введите текст шаблона, используя плейсхолдеры..."
                                              required>{{ old('body', $reminderTemplate->body) }}</textarea>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           value="1"
                                           {{ old('is_active', $reminderTemplate->is_active) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                        Активный шаблон
                                    </label>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <a href="{{ route('reminder-templates.index') }}" 
                                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Отмена
                                    </a>
                                    <button type="submit" 
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Сохранить изменения
                                    </button>
                                </div>
                            </div>

                            <!-- Справочник плейсхолдеров и превью -->
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900 mb-3">
                                        Доступные плейсхолдеры
                                    </h3>
                                    <div class="space-y-2 text-sm">
                                        @foreach($placeholders as $placeholder => $description)
                                            <div class="flex justify-between items-center">
                                                <code class="bg-gray-200 px-2 py-1 rounded text-purple-600 cursor-pointer hover:bg-gray-300"
                                                      onclick="insertPlaceholder('{{ $placeholder }}')">
                                                    {{ $placeholder }}
                                                </code>
                                                <span class="text-gray-600">{{ $description }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <h3 class="text-lg font-medium text-gray-900 mb-3">
                                        Превью шаблона
                                    </h3>
                                    <div id="preview" class="bg-white p-3 rounded border border-gray-300 text-sm">
                                        <em class="text-gray-500">Введите текст шаблона для предварительного просмотра</em>
                                    </div>
                                </div>

                                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                    <h4 class="text-sm font-medium text-yellow-800 mb-2">
                                        💡 Советы по созданию шаблонов:
                                    </h4>
                                    <ul class="text-sm text-yellow-700 space-y-1">
                                        <li>• Используйте вежливые обращения</li>
                                        <li>• Укажите важную информацию о записи</li>
                                        <li>• Добавьте контактные данные</li>
                                        <li>• Проверьте шаблон перед отправкой</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function insertPlaceholder(placeholder) {
            const bodyTextarea = document.getElementById('body');
            const cursorPosition = bodyTextarea.selectionStart;
            const textBefore = bodyTextarea.value.substring(0, cursorPosition);
            const textAfter = bodyTextarea.value.substring(bodyTextarea.selectionEnd);
            
            bodyTextarea.value = textBefore + placeholder + textAfter;
            bodyTextarea.selectionStart = cursorPosition + placeholder.length;
            bodyTextarea.selectionEnd = cursorPosition + placeholder.length;
            bodyTextarea.focus();
            
            updatePreview();
        }

        function updatePreview() {
            const bodyText = document.getElementById('body').value;
            const previewDiv = document.getElementById('preview');
            
            if (bodyText.trim() === '') {
                previewDiv.innerHTML = '<em class="text-gray-500">Введите текст шаблона для предварительного просмотра</em>';
                return;
            }
            
            // Заменяем плейсхолдеры примерами для превью
            let preview = bodyText;
                            preview = preview.replace(/{client_name}/g, 'Николь');
                preview = preview.replace(/{service_name}/g, 'Маникюр');
                preview = preview.replace(/{date_time}/g, '15.07.2025 в 14:30');
                preview = preview.replace(/{price}/g, '25 euro');
                preview = preview.replace(/{studio_address}/g, 'ул. Красная, 15');
            
            previewDiv.innerHTML = preview.replace(/\n/g, '<br>');
        }

        // Обновляем превью при изменении текста
        document.getElementById('body').addEventListener('input', updatePreview);
        
        // Обновляем превью при загрузке страницы
        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</x-app-layout> 