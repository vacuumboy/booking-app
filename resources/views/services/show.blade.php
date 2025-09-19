<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Услуга:') }} {{ $service->name }}
                </h2>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('services.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('К списку услуг') }}
                </a>
                <a href="{{ route('services.edit', $service) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('Редактировать') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Информация об услуге -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Основная информация -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Основная информация</h3>
                            
                            <div>
                                <x-input-label value="Название услуги" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $service->name }}
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Цена" />
                                <div class="flex">
                                    <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-l-md text-sm">
                                        {{ number_format($service->price, 0, '.', ' ') }}
                                    </div>
                                    <span class="inline-flex items-center px-3 mt-1 text-sm text-gray-900 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md">
                                        €
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Длительность" />
                                <div class="flex">
                                    <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-l-md text-sm">
                                        {{ $service->duration }}
                                    </div>
                                    <span class="inline-flex items-center px-3 mt-1 text-sm text-gray-900 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md">
                                        мин.
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Статус" />
                                <div class="mt-1 flex items-center">
                                    @if($service->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-2 h-2 mr-1 fill-current" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3"/>
                                            </svg>
                                            Активна
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-2 h-2 mr-1 fill-current" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3"/>
                                            </svg>
                                            Неактивна
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($service->description)
                            <div>
                                <x-input-label value="Описание" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm min-h-[100px]">
                                    {{ $service->description }}
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Дополнительная информация -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Дополнительная информация</h3>
                            
                            <div>
                                <x-input-label value="Использование" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    @if($appointmentsCount > 0)
                                        Используется в {{ $appointmentsCount }} {{ trans_choice('записи|записях|записях', $appointmentsCount) }}
                                    @else
                                        Ещё не используется в записях
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Дата добавления" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $service->created_at->format('d.m.Y') }}
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label value="Последнее обновление" />
                                <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm">
                                    {{ $service->updated_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Действия -->
                    <div class="border-t pt-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('services.edit', $service) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ __('Редактировать') }}
                            </a>
                            
                            <form action="{{ route('services.toggle-status', $service) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    @if($service->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        {{ __('Деактивировать') }}
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ __('Активировать') }}
                                    @endif
                                </button>
                            </form>
                            
                            @if($appointmentsCount === 0)
                                <form action="{{ route('services.destroy', $service) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить эту услугу?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ __('Удалить') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 