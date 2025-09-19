<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Записи') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Фильтры -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Фильтры</h3>
                        <form method="GET" action="{{ route('appointments.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Дата</label>
                                <input type="date" name="date" id="date" value="{{ request('date') }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="master_id" class="block text-sm font-medium text-gray-700">Мастер</label>
                                <select name="master_id" id="master_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Все мастера</option>
                                    @foreach($masters as $master)
                                        <option value="{{ $master->id }}" {{ request('master_id') == $master->id ? 'selected' : '' }}>
                                            {{ $master->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="client_id" class="block text-sm font-medium text-gray-700">Клиент</label>
                                <select name="client_id" id="client_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Все клиенты</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                    Применить фильтры
                                </button>
                                <a href="{{ route('appointments.index') }}" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                                    Сбросить
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Таблица записей -->
                    @if($appointments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Дата и время
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Клиент
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Мастер
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Услуга
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Цена
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Статус
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Действия
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div>
                                                    {{ \Carbon\Carbon::parse($appointment->start_time)->format('d.m.Y') }}
                                                </div>
                                                <div class="text-gray-500">
                                                    {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $appointment->client->name }}
                                                </div>
                                                @if($appointment->client->phone)
                                                    <div class="text-sm text-gray-500">
                                                        {{ $appointment->client->phone }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $appointment->master->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $appointment->service->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($appointment->price, 0, ',', ' ') }} ₽
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($appointment->status === 'confirmed') bg-green-100 text-green-800
                                                    @elseif($appointment->status === 'cancelled') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800
                                                    @endif">
                                                    @switch($appointment->status)
                                                        @case('confirmed')
                                                            Подтверждена
                                                            @break
                                                        @case('cancelled')
                                                            Отменена
                                                            @break
                                                        @default
                                                            {{ ucfirst($appointment->status) }}
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('appointments.edit', $appointment) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        Редактировать
                                                    </a>
                                                    @if($appointment->status !== 'cancelled')
                                                        <form action="{{ route('appointments.cancel', $appointment) }}" 
                                                              method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="text-red-600 hover:text-red-900"
                                                                    onclick="return confirm('Вы уверены, что хотите отменить эту запись?')">
                                                                Отменить
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @if($appointment->notes)
                                            <tr>
                                                <td colspan="7" class="px-6 py-2 text-sm text-gray-600 bg-gray-50">
                                                    <strong>Заметки:</strong> {{ $appointment->notes }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Пагинация -->
                        <div class="mt-6">
                            {{ $appointments->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 text-lg">
                                Записи не найдены
                            </div>
                            <div class="text-gray-400 text-sm mt-2">
                                Попробуйте изменить фильтры или создать новую запись
                            </div>
                        </div>
                    @endif

                    <!-- Кнопка создания записи -->
                    <div class="mt-6">
                        <a href="{{ route('calendar.day') }}" 
                           class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                            Создать запись
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> 