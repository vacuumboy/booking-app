<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Клиент:') }} {{ $client->name }}
                </h2>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('К списку клиентов') }}
                </a>
                <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('Редактировать') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Информация о клиенте -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Основная информация -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Основная информация</h3>
                            
                            <div>
                                <x-input-label value="Имя клиента" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $client->name }}
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Телефон" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $client->phone }}
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Язык" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    @if($client->language == 'ru')
                                        Русский
                                    @elseif($client->language == 'lv')
                                        Латышский
                                    @elseif($client->language == 'en')
                                        English
                                    @else
                                        {{ $client->language }}
                                    @endif
                                </div>
                            </div>
                            
                            @if($client->notes)
                            <div>
                                <x-input-label value="Заметки" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm min-h-[80px] whitespace-pre-wrap">{{ $client->notes }}</div>
                            </div>
                            @endif
                        </div>

                        <!-- Дополнительная информация -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Дополнительная информация</h3>
                            
                            <div>
                                <x-input-label value="Общее количество записей" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $appointments->count() }} {{ trans_choice('запись|записи|записей', $appointments->count()) }}
                                </div>
                            </div>
                            
                            @if($appointments->count() > 0)
                            <div>
                                <x-input-label value="Последняя запись" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $appointments->first()->start_time->format('d.m.Y H:i') }}
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Общая сумма записей" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ number_format($appointments->sum('price'), 0, '.', ' ') }} €
                                </div>
                            </div>
                            @endif
                            
                            <div>
                                <x-input-label value="Дата добавления" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $client->created_at->format('d.m.Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Действия -->
                    <div class="border-t pt-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ __('Редактировать') }}
                            </a>
                            
                            <a href="{{ route('calendar.create-appointment', ['client_id' => $client->id]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                {{ __('Новая запись') }}
                            </a>
                            
                            <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить этого клиента? Все его записи также будут удалены.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    {{ __('Удалить') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- История записей -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">История записей</h3>
                    </div>
                    
                    @if($appointments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Дата и время
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Услуга
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Мастер
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Стоимость
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Статус
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($appointments as $appointment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $appointment->start_time->format('d.m.Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $appointment->service->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $appointment->master->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($appointment->price, 0, '.', ' ') }} €
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @switch($appointment->status)
                                                    @case('pending')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            Ожидает
                                                        </span>
                                                        @break
                                                    @case('confirmed')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Подтверждена
                                                        </span>
                                                        @break
                                                    @case('completed')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Выполнена
                                                        </span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Отменена
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $appointment->status }}
                                                        </span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Нет записей</h3>
                            <p class="mt-1 text-sm text-gray-500">У этого клиента пока нет записей</p>
                            <div class="mt-6">
                                <a href="{{ route('calendar.create-appointment', ['client_id' => $client->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Создать запись
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 