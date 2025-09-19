<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Клиенты') }}
                </h2>
            </div>
            <div>
                <a href="{{ route('clients.create') }}" class="btn-adaptive btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ __('Добавить клиента') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-adaptive-2xl">
        <div class="container-adaptive">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-adaptive-lg mb-adaptive-lg" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-adaptive-lg mb-adaptive-lg" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Search Bar -->
            <div class="card-adaptive mb-adaptive-xl">
                <div class="p-adaptive-xl">
                    <form action="{{ route('clients.index') }}" method="GET" class="flex-adaptive gap-adaptive-lg">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Поиск по имени или телефону..." class="flex-1 input-adaptive">
                        <button type="submit" class="btn-adaptive btn-primary">
                            Поиск
                        </button>
                        @if($search)
                            <a href="{{ route('clients.index') }}" class="btn-adaptive btn-secondary">
                                Сбросить
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card-adaptive">
                <div class="p-adaptive-xl">
                    @if ($clients->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-adaptive-lg mb-adaptive-xl">
                            <div class="flex-adaptive">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-adaptive-sm text-yellow-700">
                                        {{ __('У вас пока нет клиентов. Создайте нового клиента, нажав кнопку "Добавить клиента".') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Десктопная таблица -->
                        <div class="hidden lg:block table-adaptive">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-adaptive-lg py-adaptive-md text-left text-adaptive-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('clients.index', ['sort_by' => 'name', 'sort_dir' => $sortBy == 'name' && $sortDir == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center">
                                                Имя
                                                @if ($sortBy == 'name')
                                                    <span class="ml-1">
                                                        @if ($sortDir == 'asc')
                                                            ↑
                                                        @else
                                                            ↓
                                                        @endif
                                                    </span>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-adaptive-lg py-adaptive-md text-left text-adaptive-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('clients.index', ['sort_by' => 'phone', 'sort_dir' => $sortBy == 'phone' && $sortDir == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center">
                                                Телефон
                                                @if ($sortBy == 'phone')
                                                    <span class="ml-1">
                                                        @if ($sortDir == 'asc')
                                                            ↑
                                                        @else
                                                            ↓
                                                        @endif
                                                    </span>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-adaptive-lg py-adaptive-md text-left text-adaptive-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('clients.index', ['sort_by' => 'created_at', 'sort_dir' => $sortBy == 'created_at' && $sortDir == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center">
                                                Дата добавления
                                                @if ($sortBy == 'created_at')
                                                    <span class="ml-1">
                                                        @if ($sortDir == 'asc')
                                                            ↑
                                                        @else
                                                            ↓
                                                        @endif
                                                    </span>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-adaptive-lg py-adaptive-md text-right text-adaptive-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Действия
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($clients as $client)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ $client->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-gray-500">{{ $client->phone }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-gray-500">{{ $client->created_at->format('d.m.Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('clients.show', $client) }}" class="text-blue-600 hover:text-blue-900">
                                                        Просмотр
                                                    </a>
                                                    <a href="{{ route('clients.edit', $client) }}" class="text-green-600 hover:text-green-900">
                                                        Изменить
                                                    </a>
                                                    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого клиента?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            Удалить
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $clients->links() }}
                            </div>
                        </div>
                        <!-- Мобильный список -->
                        <div class="lg:hidden space-y-4">
                            @foreach ($clients as $client)
                                <div class="bg-white rounded-xl shadow p-4 flex flex-col gap-2 animate-in fade-in slide-in-from-bottom duration-500">
                                    <div>
                                        <div class="font-semibold text-lg text-gray-900">{{ $client->name }}</div>
                                        <div class="text-gray-500 text-sm">{{ $client->phone }}</div>
                                        <div class="text-gray-400 text-xs mt-1">Добавлен: {{ $client->created_at->format('d.m.Y') }}</div>
                                    </div>
                                    <!-- Кнопки действий -->
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <a href="{{ route('clients.show', $client) }}" class="flex-1 min-w-[110px] text-center px-3 py-2 rounded-lg bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition">Просмотр</a>
                                        <a href="{{ route('clients.edit', $client) }}" class="flex-1 min-w-[110px] text-center px-3 py-2 rounded-lg bg-green-50 text-green-700 font-semibold hover:bg-green-100 transition">Изменить</a>
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="flex-1 min-w-[110px] text-center" onsubmit="return confirm('Вы уверены, что хотите удалить этого клиента?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-50 text-red-700 font-semibold hover:bg-red-100 transition">Удалить</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                            <div class="mt-4">
                                {{ $clients->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 