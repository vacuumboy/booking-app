<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Услуги') }}
                </h2>
            </div>
            <div>
                <a href="{{ route('services.create') }}" class="btn-adaptive btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ __('Добавить услугу') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-adaptive-2xl">
        <div class="container-adaptive" >
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
                    <form action="{{ route('services.index') }}" method="GET" class="flex flex-wrap gap-4">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Поиск по названию или описанию..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Поиск
                        </button>
                        
                        @if($search)
                            <a href="{{ route('services.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Сбросить
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($services->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('У вас пока нет услуг. Создайте новую услугу, нажав кнопку "Добавить услугу".') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Десктопная таблица -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('services.index', ['sort_by' => 'name', 'sort_dir' => $sortBy == 'name' && $sortDir == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center">
                                                Название
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
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('services.index', ['sort_by' => 'price', 'sort_dir' => $sortBy == 'price' && $sortDir == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center">
                                                Цена
                                                @if ($sortBy == 'price')
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
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('services.index', ['sort_by' => 'duration', 'sort_dir' => $sortBy == 'duration' && $sortDir == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center">
                                                Длительность
                                                @if ($sortBy == 'duration')
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
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Статус
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Действия
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($services as $service)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ $service->name }}</div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    @if($service->name_ru)
                                                        <span class="inline-block mr-2"><strong>RU:</strong> {{ $service->name_ru }}</span>
                                                    @endif
                                                    @if($service->name_lv)
                                                        <span class="inline-block mr-2"><strong>LV:</strong> {{ $service->name_lv }}</span>
                                                    @endif
                                                    @if($service->name_en)
                                                        <span class="inline-block mr-2"><strong>EN:</strong> {{ $service->name_en }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-gray-500">{{ number_format($service->price, 0, '.', ' ') }} €</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-gray-500">{{ $service->duration }} мин.</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form action="{{ route('services.toggle-status', $service) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="flex items-center">
                                                        @if($service->is_active)
                                                            <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-2"></span> Активна
                                                        @else
                                                            <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-2"></span> Не активна
                                                        @endif
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('services.show', $service) }}" class="text-blue-600 hover:text-blue-900">
                                                        Просмотр
                                                    </a>
                                                    <a href="{{ route('services.edit', $service) }}" class="text-green-600 hover:text-green-900">
                                                        Изменить
                                                    </a>
                                                    <form method="POST" action="{{ route('services.destroy', $service) }}" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту услугу?');">
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
                                {{ $services->links() }}
                            </div>
                        </div>
                        <!-- Мобильный список -->
                        <div class="lg:hidden space-y-4">
                            @foreach ($services as $service)
                                <div class="bg-white rounded-xl shadow p-4 flex flex-col gap-2 animate-in fade-in slide-in-from-bottom duration-500">
                                    <div>
                                        <div class="font-semibold text-lg text-gray-900">{{ $service->name }}</div>
                                        <div class="text-gray-500 text-sm mt-1">
                                            @if($service->name_ru)
                                                <span class="inline-block mr-2"><strong>RU:</strong> {{ $service->name_ru }}</span>
                                            @endif
                                            @if($service->name_lv)
                                                <span class="inline-block mr-2"><strong>LV:</strong> {{ $service->name_lv }}</span>
                                            @endif
                                            @if($service->name_en)
                                                <span class="inline-block mr-2"><strong>EN:</strong> {{ $service->name_en }}</span>
                                            @endif
                                        </div>
                                        <div class="text-gray-500 text-sm mt-1">Цена: {{ number_format($service->price, 0, '.', ' ') }} €</div>
                                        <div class="text-gray-500 text-sm">Длительность: {{ $service->duration }} мин.</div>
                                        <div class="text-xs mt-1">
                                            @if($service->is_active)
                                                <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1 align-middle"></span> <span class="text-green-600">Активна</span>
                                            @else
                                                <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-1 align-middle"></span> <span class="text-red-600">Не активна</span>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Кнопки действий -->
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <a href="{{ route('services.show', $service) }}" class="flex-1 min-w-[110px] text-center px-3 py-2 rounded-lg bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition">Просмотр</a>
                                        <a href="{{ route('services.edit', $service) }}" class="flex-1 min-w-[110px] text-center px-3 py-2 rounded-lg bg-green-50 text-green-700 font-semibold hover:bg-green-100 transition">Изменить</a>
                                        <form method="POST" action="{{ route('services.destroy', $service) }}" class="flex-1 min-w-[110px] text-center" onsubmit="return confirm('Вы уверены, что хотите удалить эту услугу?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-50 text-red-700 font-semibold hover:bg-red-100 transition">Удалить</button>
                                        </form>
                                        <form action="{{ route('services.toggle-status', $service) }}" method="POST" class="flex-1 min-w-[110px] text-center">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full px-3 py-2 rounded-lg font-semibold transition
                                                @if($service->is_active)
                                                    bg-green-50 text-green-700 hover:bg-green-100
                                                @else
                                                    bg-yellow-50 text-yellow-700 hover:bg-yellow-100
                                                @endif
                                            ">
                                                @if($service->is_active)
                                                    Деактивировать
                                                @else
                                                    Активировать
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                            <div class="mt-4">
                                {{ $services->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 