<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Добавление новой услуги') }}
                </h2>
            </div>
            <div>
                <a href="{{ route('services.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Назад') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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

                    <form method="POST" action="{{ route('services.store') }}">
                        @csrf
                        
                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Название услуги (основное)')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        
                        <!-- Multilingual Names -->
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Переводы названия</h3>
                            
                            <!-- Russian Name -->
                            <div class="mb-3">
                                <x-input-label for="name_ru" :value="__('Название на русском')" />
                                <x-text-input id="name_ru" class="block mt-1 w-full" type="text" name="name_ru" :value="old('name_ru')" />
                                <x-input-error :messages="$errors->get('name_ru')" class="mt-2" />
                            </div>
                            
                            <!-- Latvian Name -->
                            <div class="mb-3">
                                <x-input-label for="name_lv" :value="__('Название на латышском')" />
                                <x-text-input id="name_lv" class="block mt-1 w-full" type="text" name="name_lv" :value="old('name_lv')" />
                                <x-input-error :messages="$errors->get('name_lv')" class="mt-2" />
                            </div>
                            
                            <!-- English Name -->
                            <div class="mb-3">
                                <x-input-label for="name_en" :value="__('Название на английском')" />
                                <x-text-input id="name_en" class="block mt-1 w-full" type="text" name="name_en" :value="old('name_en')" />
                                <x-input-error :messages="$errors->get('name_en')" class="mt-2" />
                            </div>
                        </div>
                        
                        <!-- Price -->
                        <div class="mb-4">
                            <x-input-label for="price" :value="__('Цена')" />
                            <div class="flex">
                                <x-text-input id="price" class="block mt-1 w-full rounded-r-none" type="number" name="price" min="0" step="0.01" :value="old('price')" required />
                                <span class="inline-flex items-center px-3 mt-1 text-sm text-gray-900 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md">
                                    €
                                </span>
                            </div>
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>
                        
                        <!-- Duration -->
                        <div class="mb-4">
                            <x-input-label for="duration" :value="__('Длительность (в минутах)')" />
                            <div class="flex">
                                <x-text-input id="duration" class="block mt-1 w-full rounded-r-none" type="number" name="duration" min="5" max="480" :value="old('duration', 60)" required />
                                <span class="inline-flex items-center px-3 mt-1 text-sm text-gray-900 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md">
                                    мин.
                                </span>
                            </div>
                            <x-input-error :messages="$errors->get('duration')" class="mt-2" />
                        </div>
                        
                        <!-- Color -->
                        <div class="mb-4">
                            <x-input-label for="color" :value="__('Цвет услуги')" />
                            <div class="flex items-center">
                                <input type="color" id="color" name="color" value="{{ old('color', '#3B82F6') }}" class="block mt-1 w-16 h-10 border border-gray-300 rounded-md cursor-pointer">
                                <div class="ml-3 text-sm text-gray-600">
                                    Выберите цвет для отображения в календаре
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Описание')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        
                        <!-- Is Active -->
                        <div class="mb-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600">{{ __('Услуга активна') }}</span>
                            </label>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Сохранить') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 