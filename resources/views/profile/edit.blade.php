<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Редактирование профиля') }}
                </h2>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Назад') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- Basic Information -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="text-2xl mr-3">👤</div>
                        <h3 class="text-lg font-semibold text-gray-900">Основная информация</h3>
                    </div>
                    
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                                <input id="name" name="name" type="text" required 
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                       value="{{ old('name', $user->name) }}">
                                @error('name')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input id="email" name="email" type="email" required 
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                       value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                                <input id="phone" name="phone" type="tel" 
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Адрес</label>
                                <input id="address" name="address" type="text" 
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                       value="{{ old('address', $user->address) }}">
                                @error('address')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Сохранить основную информацию
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="text-2xl mr-3">
                            @if($user->isMaster())
                                🎨
                            @else
                                🏢
                            @endif
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            @if($user->isMaster())
                                Информация о мастере
                            @else
                                Информация о салоне
                            @endif
                        </h3>
                    </div>
                    
                    <form method="POST" action="{{ route('profile.professional.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')
                        
                        <!-- Photo Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Фотография</label>
                            <div class="flex items-center space-x-6">
                                <div class="shrink-0">
                                    @if($user->photo_path)
                                        <img src="{{ asset('storage/' . $user->photo_path) }}" alt="Фото профиля" 
                                             class="h-20 w-20 object-cover rounded-full border-2 border-gray-200">
                                    @else
                                        <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center text-2xl text-gray-400">
                                            📷
                                        </div>
                                    @endif
                                </div>
                                <label for="photo" class="block">
                                    <span class="sr-only">Выбрать фото профиля</span>
                                    <input type="file" id="photo" name="photo" accept="image/*" 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Максимум 2MB, форматы: JPG, PNG, GIF</p>
                            @error('photo')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($user->isMaster())
                                <div>
                                    <label for="specialization" class="block text-sm font-medium text-gray-700 mb-2">Специализация</label>
                                    <input id="specialization" name="specialization" type="text" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                           value="{{ old('specialization', $user->specialization) }}" 
                                           placeholder="Например: Стрижки, Окрашивание">
                                    @error('specialization')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-2">Опыт работы (лет)</label>
                                    <input id="experience_years" name="experience_years" type="number" min="0" max="50" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                           value="{{ old('experience_years', $user->experience_years) }}">
                                    @error('experience_years')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div class="md:col-span-2">
                                    <label for="salon_name" class="block text-sm font-medium text-gray-700 mb-2">Название салона</label>
                                    <input id="salon_name" name="salon_name" type="text" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                           value="{{ old('salon_name', $user->salon_name) }}">
                                    @error('salon_name')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div class="mt-6">
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                @if($user->isMaster())
                                    О себе
                                @else
                                    О салоне
                                @endif
                            </label>
                            <textarea id="bio" name="bio" rows="4" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" 
                                      placeholder="Расскажите о себе или своем салоне...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Сохранить профессиональную информацию
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Account -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="text-2xl mr-3">🗑️</div>
                        <h3 class="text-lg font-semibold text-gray-900">Удаление аккаунта</h3>
                    </div>
                    
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    После удаления аккаунта все ваши данные будут безвозвратно удалены. Пожалуйста, подтвердите удаление паролем.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Вы уверены, что хотите удалить аккаунт? Это действие нельзя отменить.')">
                        @csrf
                        @method('delete')
                        
                        <div class="max-w-md">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Пароль для подтверждения</label>
                            <input id="password" name="password" type="password" required 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50" 
                                   placeholder="Введите пароль">
                            @error('password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Удалить аккаунт
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
