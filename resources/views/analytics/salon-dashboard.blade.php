<x-app-layout>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Заголовок страницы -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Статистика салона</h1>
                        <p class="mt-2 text-gray-600">Анализ работы вашего салона за выбранный период</p>
                    </div>

                    <!-- Современный селектор периода на Alpine.js -->
                    <div 
                        x-data="periodSelector({
                            currentPeriod: '{{ $period }}',
                            dateFrom: '{{ $dateFrom->format('d.m.Y') }}',
                            dateTo: '{{ $dateTo->format('d.m.Y') }}',
                            customDateFrom: '{{ request('date_from', '') }}',
                            customDateTo: '{{ request('date_to', '') }}'
                        })"
                        class="mb-8"
                    >
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transition-all duration-300 hover:shadow-md">
                            <!-- Заголовок -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg" style="padding: 5px;">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Период анализа</h3>
                                        <p class="text-sm text-gray-500">Выберите период для отображения данных</p>
                                    </div>
                                </div>
                                
                                <!-- Время обновления -->
                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                    <svg 
                                        :class="{ 'animate-spin': isRefreshing }"
                                        class="w-4 h-4" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Обновлено: <span x-text="lastUpdated"></span></span>
                                </div>
                            </div>

                            <!-- Быстрые периоды -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                                <template x-for="period in availablePeriods" :key="period.key">
                                    <button
                                        @click="selectPeriod(period.key)"
                                        :class="{
                                            'bg-blue-50 border-blue-200 text-blue-700 ring-2 ring-blue-500 ring-opacity-30': selectedPeriod === period.key,
                                            'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100': selectedPeriod !== period.key
                                        }"
                                        class="relative p-4 rounded-lg border-2 transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                    >
                                        <div class="text-center">
                                            <div class="font-medium text-sm" x-text="period.label"></div>
                                            <div class="text-xs opacity-75 mt-1" x-text="period.description"></div>
                                        </div>
                                        
                                        <!-- Индикатор активного периода -->
                                        <div 
                                            x-show="selectedPeriod === period.key"
                                            class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-0"
                                            x-transition:enter-end="opacity-100 scale-100"
                                        ></div>
                                    </button>
                                </template>
                            </div>

                            <!-- Кнопка настройки и выбранный период -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                                <!-- Текущий период -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center space-x-2 px-4 py-2 bg-gray-50 rounded-lg">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">
                                            <span x-text="currentDateRange"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Кнопки действий -->
                                <div class="flex items-center space-x-3">
                                    <!-- Кнопка настройки -->
                                    <button
                                        @click="openCustomPicker"
                                        :class="{
                                            'bg-blue-600 text-white': selectedPeriod === 'custom',
                                            'bg-white text-gray-700 border-gray-300': selectedPeriod !== 'custom'
                                        }"
                                        class="inline-flex items-center px-4 py-2 border rounded-lg text-sm font-medium transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                                        </svg>
                                        Настроить
                                    </button>

                                    <!-- Кнопка обновления -->
                                    <div x-data="clickSpark()" 
                                         x-init="init(); sparkColor = '#10b981'; sparkCount = 12; sparkSize = 8; duration = 300;"
                                         @click="handleClick($event)"
                                         class="relative inline-block">
                                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                                        <button
                                            @click="refreshData"
                                            :disabled="isRefreshing"
                                            class="relative z-20 inline-flex items-center px-4 py-2 bg-green-600 text-white border border-transparent rounded-lg text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 hover:scale-105"
                                        >
                                            <svg 
                                                :class="{ 'animate-spin': isRefreshing }"
                                                class="w-4 h-4 mr-2" 
                                                fill="none" 
                                                stroke="currentColor" 
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <span x-show="!isRefreshing">Обновить</span>
                                            <span x-show="isRefreshing">Обновление...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Модальное окно для настройки периода -->
                            <div 
                                x-show="showCustomPicker"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
                                style="display: none;"
                            >
                                <div 
                                    @click.away="closeCustomPicker"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4"
                                >
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Настройка периода</h3>
                                        <button
                                            @click="closeCustomPicker"
                                            class="text-gray-400 hover:text-gray-600 transition-colors"
                                        >
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">От</label>
                                            <input
                                                x-model="customDateFrom"
                                                type="date"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">До</label>
                                            <input
                                                x-model="customDateTo"
                                                type="date"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end space-x-3 mt-6">
                                        <button
                                            @click="closeCustomPicker"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            Отмена
                                        </button>
                                        <button
                                            @click="applyCustomPeriod"
                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            Применить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Основные метрики -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Общее количество записей -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg text-white shadow-lg">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-blue-100 mb-2">Всего записей</h3>
                                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_appointments']) }}</p>
                                </div>
                                <div class="ml-3">
                                    <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Общий доход -->
                        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg text-white shadow-lg">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-green-100 mb-2">Общий доход</h3>
                                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} €</p>
                                </div>
                                <div class="ml-3">
                                    <svg class="w-8 h-8 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Активные клиенты -->
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white shadow-lg">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-purple-100 mb-2">Активные клиенты</h3>
                                    <p class="text-2xl font-bold text-white">{{ number_format($stats['active_clients']) }}</p>
                                </div>
                                <div class="ml-3">
                                    <svg class="w-8 h-8 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Средний чек -->
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 rounded-lg text-white shadow-lg">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-orange-100 mb-2">Средний чек</h3>
                                    <p class="text-2xl font-bold text-white">
                                        {{ $stats['total_appointments'] > 0 ? number_format($stats['total_revenue'] / $stats['total_appointments'], 0, ',', ' ') : 0 }} €
                                    </p>
                                </div>
                                <div class="ml-3">
                                    <svg class="w-8 h-8 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Дополнительные стили для устранения проблем с видимостью -->
                    <style>
                        /* Принудительно задаем цвета для метрик */
                        .analytics-metric-card {
                            color: black !important;
                        }
                        .analytics-metric-card h3 {
                            color: rgba(0, 0, 0, 0.8) !important;
                        }
                        .analytics-metric-card p {
                            color: black !important;
                        }
                        /* Убираем любые глобальные стили, которые могут мешать */
                        .analytics-metric-card * {
                            text-shadow: none !important;
                        }
                    </style>


                    
                    <!-- Кнопки экспорта и дополнительных настроек (Фаза 4) -->
                    <div class="mb-8">
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Экспорт и дополнительные настройки
                            </h3>
                            
                            <div class="flex flex-wrap gap-3">
                                <x-click-spark-button 
                                    spark-color="#dc2626" 
                                    spark-count="16" 
                                    spark-size="10"
                                    duration="600"
                                    onclick="exportToPdf()" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Экспорт в PDF
                                </x-click-spark-button>
                                

                                
                                
                                
                                <button onclick="openCustomPeriod()" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Кастомный период
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Графики аналитики -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Графики и аналитика</h2>
                        
                        <!-- График доходов -->
                        <div class="bg-white p-6 rounded-lg shadow mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">График доходов</h3>
                            <div class="h-64">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Графики услуг и мастеров -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <!-- График услуг -->
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Популярность услуг</h3>
                                <div class="h-64">
                                    <canvas id="servicesChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- График мастеров -->
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Статистика мастеров</h3>
                                <div class="h-64">
                                    <canvas id="mastersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика по мастерам -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Детальная статистика по мастерам</h2>
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                @foreach($masterStats as $masterStat)
                                    <li class="px-6 py-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span class="text-blue-600 font-medium">
                                                            {{ substr($masterStat['master']->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-lg font-medium text-gray-900">
                                                        {{ $masterStat['master']->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $masterStat['appointments_count'] }} записей
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-medium text-green-600">
                                                    {{ number_format($masterStat['revenue'], 0, ',', ' ') }} €
                                                </div>
                                                <div class="text-sm text-gray-500">доход</div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Статистика по услугам -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Популярные услуги</h2>
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                @foreach($serviceStats as $serviceStat)
                                    <li class="px-6 py-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-lg font-medium text-gray-900">
                                                    {{ $serviceStat['service']->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $serviceStat['appointments_count'] }} записей • 
                                                    средний чек: {{ number_format($serviceStat['avg_price'], 0, ',', ' ') }} €
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-medium text-green-600">
                                                    {{ number_format($serviceStat['revenue'], 0, ',', ' ') }} €
                                                </div>
                                                <div class="text-sm text-gray-500">доход</div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Клиентская аналитика -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Клиентская аналитика</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-gray-900">Всего клиентов</h3>
                                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $clientStats['total_clients'] }}</p>
                                <p class="text-sm text-gray-500 mt-2">в базе данных</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-gray-900">Новые клиенты</h3>
                                <p class="text-3xl font-bold text-green-600 mt-2">{{ $clientStats['new_clients'] }}</p>
                                <p class="text-sm text-gray-500 mt-2">за выбранный период</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-gray-900">Постоянные клиенты</h3>
                                <p class="text-3xl font-bold text-purple-600 mt-2">{{ $clientStats['returning_clients'] }}</p>
                                <p class="text-sm text-gray-500 mt-2">с повторными записями</p>
                            </div>
                        </div>
                    </div>

                    <!-- Информационный блок -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Информация о статистике</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Статистика обновляется в реальном времени и включает только подтвержденные записи. Отмененные записи не учитываются в расчетах.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Alpine.js компонент для селектора периода
        document.addEventListener('alpine:init', () => {
            Alpine.data('periodSelector', (config) => ({
                selectedPeriod: config.currentPeriod,
                dateFrom: config.dateFrom,
                dateTo: config.dateTo,
                showCustomPicker: false,
                customDateFrom: '',
                customDateTo: '',
                isRefreshing: false,
                lastUpdated: new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }),
                
                availablePeriods: [
                    {
                        key: 'week',
                        label: '7 дней',
                        description: 'Последние 7 дней'
                    },
                    {
                        key: 'month', 
                        label: 'Месяц',
                        description: 'Текущий месяц'
                    },
                    {
                        key: 'quarter',
                        label: 'Квартал', 
                        description: 'Текущий квартал'
                    },
                    {
                        key: 'year',
                        label: 'Год',
                        description: 'Текущий год'
                    }
                ],

                init() {
                    this.updateDateRange();
                    this.initializeCustomDates();
                    // Запускаем загрузку графиков
                    this.loadChartsData();
                },

                get currentDateRange() {
                    return `${this.dateFrom} - ${this.dateTo}`;
                },

                selectPeriod(period) {
                    this.selectedPeriod = period;
                    this.updatePeriodDates(period);
                    this.updateUrl();
                    this.refreshCharts();
                },

                updatePeriodDates(period) {
                    const now = new Date();
                    
                    switch(period) {
                        case 'week':
                            // Получаем 7 полных дней: 6 дней назад + сегодня = 7 дней
                            const sevenDaysAgo = new Date(now);
                            sevenDaysAgo.setDate(now.getDate() - 6);
                            this.dateFrom = this.formatDate(sevenDaysAgo);
                            this.dateTo = this.formatDate(now);
                            break;
                        case 'month':
                            this.dateFrom = this.formatDate(new Date(now.getFullYear(), now.getMonth(), 1));
                            this.dateTo = this.formatDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));
                            break;
                        case 'quarter':
                            const quarterStart = new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3, 1);
                            const quarterEnd = new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3 + 3, 0);
                            this.dateFrom = this.formatDate(quarterStart);
                            this.dateTo = this.formatDate(quarterEnd);
                            break;
                        case 'year':
                            this.dateFrom = this.formatDate(new Date(now.getFullYear(), 0, 1));
                            this.dateTo = this.formatDate(new Date(now.getFullYear(), 11, 31));
                            break;
                    }
                },

                formatDate(date) {
                    return date.toLocaleDateString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit', 
                        year: 'numeric'
                    });
                },

                formatDateForInput(date) {
                    return date.toISOString().split('T')[0];
                },

                initializeCustomDates() {
                    const now = new Date();
                    this.customDateFrom = this.formatDateForInput(new Date(now.getFullYear(), now.getMonth(), 1));
                    this.customDateTo = this.formatDateForInput(now);
                },

                openCustomPicker() {
                    this.showCustomPicker = true;
                    this.initializeCustomDates();
                },

                closeCustomPicker() {
                    this.showCustomPicker = false;
                },

                applyCustomPeriod() {
                    if (this.customDateFrom && this.customDateTo) {
                        const fromDate = new Date(this.customDateFrom);
                        const toDate = new Date(this.customDateTo);
                        
                        if (fromDate <= toDate) {
                            this.selectedPeriod = 'custom';
                            this.dateFrom = this.formatDate(fromDate);
                            this.dateTo = this.formatDate(toDate);
                            this.closeCustomPicker();
                            
                            // Для кастомного периода перезагружаем страницу с новыми параметрами
                            const url = new URL(window.location);
                            url.searchParams.set('period', 'custom');
                            url.searchParams.set('date_from', this.customDateFrom);
                            url.searchParams.set('date_to', this.customDateTo);
                            window.location.href = url.toString();
                        } else {
                            alert('Дата начала должна быть раньше даты окончания');
                        }
                    }
                },

                async refreshData() {
                    this.isRefreshing = true;
                    
                    try {
                        // Обновляем время
                        this.lastUpdated = new Date().toLocaleTimeString('ru-RU', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        // Перезагружаем страницу с новыми параметрами
                        window.location.reload();
                    } catch (error) {
                        console.error('Ошибка при обновлении данных:', error);
                    } finally {
                        this.isRefreshing = false;
                    }
                },

                updateUrl() {
                    const url = new URL(window.location);
                    url.searchParams.set('period', this.selectedPeriod);
                    
                    if (this.selectedPeriod === 'custom') {
                        url.searchParams.set('date_from', this.customDateFrom);
                        url.searchParams.set('date_to', this.customDateTo);
                    } else {
                        url.searchParams.delete('date_from');
                        url.searchParams.delete('date_to');
                    }
                    
                    window.history.pushState({}, '', url);
                },

                updateDateRange() {
                    // Обновляем отображение диапазона дат
                },

                loadChartsData() {
                    // Запускаем функцию загрузки графиков, если она есть
                    if (typeof loadCharts === 'function') {
                        loadCharts(this.selectedPeriod);
                    }
                },

                refreshCharts() {
                    // Обновляем графики
                    if (typeof loadCharts === 'function') {
                        loadCharts(this.selectedPeriod);
                    }
                }
            }));
        });

        // Глобальная переменная для доступа из всех функций
        window.currentPeriod = '{{ $period }}';
        
        document.addEventListener('DOMContentLoaded', function() {
            let currentPeriod = window.currentPeriod;
            
            // Цвета для графиков
            const primaryColor = '#3B82F6';
            const successColor = '#10B981';
            const warningColor = '#F59E0B';
            const dangerColor = '#EF4444';
            const infoColor = '#8B5CF6';
            
            // Объекты графиков
            let revenueChart, servicesChart, mastersChart;
            
            // Функция загрузки графиков
            function loadCharts(period = currentPeriod) {
                loadRevenueChart(period);
                loadServicesChart(period);
                loadMastersChart(period);
            }
            
            // График доходов
            function loadRevenueChart(period = currentPeriod) {
                return fetch(`/analytics/api/revenue-chart?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Revenue chart data:', data);
                        const ctx = document.getElementById('revenueChart').getContext('2d');
                        
                        if (revenueChart) {
                            revenueChart.destroy();
                        }
                        
                        revenueChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Доход (€)',
                                data: data.data,
                                borderColor: primaryColor,
                                backgroundColor: primaryColor + '20',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value + ' €';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Ошибка загрузки данных графика доходов:', error));
            }
            
            // График услуг
            function loadServicesChart(period = currentPeriod) {
                return fetch(`/analytics/api/services-chart?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Services chart data:', data);
                        const ctx = document.getElementById('servicesChart').getContext('2d');
                        
                        if (servicesChart) {
                            servicesChart.destroy();
                        }
                        
                        servicesChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Количество записей',
                                    data: data.appointmentsData,
                                    backgroundColor: primaryColor + '80',
                                    borderColor: primaryColor,
                                    borderWidth: 1,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Доход (€)',
                                    data: data.revenueData,
                                    backgroundColor: successColor + '80',
                                    borderColor: successColor,
                                    borderWidth: 1,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    display: true,
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Количество записей'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Доход (€)'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + ' €';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Ошибка загрузки данных графика услуг:', error));
            }
            
            // График мастеров
            function loadMastersChart(period = currentPeriod) {
                return fetch(`/analytics/api/masters-chart?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Masters chart data:', data);
                        const ctx = document.getElementById('mastersChart').getContext('2d');
                        
                        if (mastersChart) {
                            mastersChart.destroy();
                        }
                        
                        mastersChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Записи',
                                    data: data.appointmentsData,
                                    backgroundColor: primaryColor + '80',
                                    borderColor: primaryColor,
                                    borderWidth: 1,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Доход (€)',
                                    data: data.revenueData,
                                    backgroundColor: successColor + '80',
                                    borderColor: successColor,
                                    borderWidth: 1,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    display: true,
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Количество записей'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Доход (€)'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + ' €';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Ошибка загрузки данных графика мастеров:', error));
            }
            
            // Глобальные функции для управления графиками
            window.changePeriod = function(period) {
                currentPeriod = period;
                window.currentPeriod = period; // Обновляем глобальную переменную
                
                // Показываем индикатор загрузки
                showLoading();
                
                // Обновляем активную кнопку
                updateActivePeriod(period);
                
                // Перезагружаем графики
                Promise.all([
                    loadRevenueChart(),
                    loadServicesChart(),
                    loadMastersChart()
                ]).then(() => {
                    hideLoading();
                    updateLastUpdated();
                    
                    // Обновляем URL без перезагрузки страницы
                    const url = new URL(window.location);
                    url.searchParams.set('period', period);
                    window.history.pushState({}, '', url.toString());
                }).catch(() => {
                    hideLoading();
                    showError('Ошибка при загрузке данных');
                });
            };
            

            
            // Показать индикатор загрузки
            function showLoading() {
                const indicator = document.getElementById('loading-indicator');
                if (indicator) indicator.classList.remove('hidden');
            }
            
            // Скрыть индикатор загрузки
            function hideLoading() {
                const indicator = document.getElementById('loading-indicator');
                if (indicator) indicator.classList.add('hidden');
            }
            
            // Обновить активную кнопку периода
            function updateActivePeriod(activePeriod) {
                document.querySelectorAll('.period-btn').forEach(btn => {
                    if (btn.dataset.period === activePeriod) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }
            
            // Обновить время последнего обновления
            function updateLastUpdated() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('ru-RU', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                const element = document.getElementById('last-updated');
                if (element) element.textContent = timeString;
            }
            
            // Показать ошибку
            function showError(message) {
                const error = document.createElement('div');
                error.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
                error.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ${message}
                    </div>
                `;
                document.body.appendChild(error);
                
                setTimeout(() => {
                    if (error.parentNode) {
                        error.parentNode.removeChild(error);
                    }
                }, 3000);
            }
            
            // Открыть настройку кастомного периода
            window.openCustomPeriod = function() {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-lg p-6 w-96 max-w-90vw">
                        <h3 class="text-lg font-semibold mb-4">Настройка периода</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label>
                                <input type="date" id="custom-start-date" class="w-full border rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
                                <input type="date" id="custom-end-date" class="w-full border rounded-md px-3 py-2">
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button onclick="closeCustomPeriod()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                Отмена
                            </button>
                            <button onclick="applyCustomPeriod()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                Применить
                            </button>
                        </div>
                    </div>
                `;
                
                const today = new Date().toISOString().split('T')[0];
                modal.querySelector('#custom-end-date').value = today;
                
                const monthAgo = new Date();
                monthAgo.setMonth(monthAgo.getMonth() - 1);
                modal.querySelector('#custom-start-date').value = monthAgo.toISOString().split('T')[0];
                
                document.body.appendChild(modal);
                window.customPeriodModal = modal;
            };
            
            window.closeCustomPeriod = function() {
                if (window.customPeriodModal && window.customPeriodModal.parentNode) {
                    window.customPeriodModal.parentNode.removeChild(window.customPeriodModal);
                    window.customPeriodModal = null;
                }
            };
            
            window.applyCustomPeriod = function() {
                const startDate = document.getElementById('custom-start-date').value;
                const endDate = document.getElementById('custom-end-date').value;
                
                if (!startDate || !endDate) {
                    showError('Пожалуйста, выберите обе даты');
                    return;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    showError('Дата начала не может быть позже даты окончания');
                    return;
                }
                
                closeCustomPeriod();
                
                // Показываем индикатор загрузки
                showLoading();
                
                // Создаем кастомный период
                currentPeriod = 'custom';
                window.currentPeriod = 'custom';
                
                // Обновляем URL с кастомными датами и перезагружаем страницу
                const url = new URL(window.location);
                url.searchParams.set('period', 'custom');
                url.searchParams.set('date_from', startDate);
                url.searchParams.set('date_to', endDate);
                
                // Переходим на страницу с кастомным периодом
                window.location.href = url.toString();
            };
            
            // Загружаем графики при загрузке страницы
            loadCharts(currentPeriod);
        });
        
        // === ФУНКЦИИ ФАЗЫ 4: РАСШИРЕННАЯ АНАЛИТИКА (УПРОЩЕННЫЕ) ===
        
        // Экспорт в PDF (исправленная версия)
        window.exportToPdf = function() {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            // Показываем состояние загрузки
            button.innerHTML = `
                <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Генерируем PDF...
            `;
            button.disabled = true;
            
            // Создаем URL для скачивания с учетом кастомного периода
            let url = `/analytics/export/pdf?period=${window.currentPeriod}`;
            
            // Если кастомный период, добавляем даты из URL параметров
            if (window.currentPeriod === 'custom') {
                const urlParams = new URLSearchParams(window.location.search);
                const dateFrom = urlParams.get('date_from');
                const dateTo = urlParams.get('date_to');
                
                if (dateFrom && dateTo) {
                    url += `&date_from=${dateFrom}&date_to=${dateTo}`;
                } else {
                    // Восстанавливаем кнопку и показываем ошибку
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showError('Для экспорта кастомного периода требуются даты начала и окончания');
                    return;
                }
            }
            
            // Открываем в новой вкладке для скачивания
            window.open(url, '_blank');
            
            // Восстанавливаем кнопку через 3 секунды
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                console.log('PDF отчет запущен для генерации');
            }, 3000);
        };
        

        

        
        // Очистка кэша

        
                 // Показать сообщение об успехе
        function showSuccess(message) {
            const success = document.createElement('div');
            success.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
            success.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    ${message}
                </div>
            `;
            document.body.appendChild(success);
            
            setTimeout(() => {
                if (success.parentNode) {
                    success.parentNode.removeChild(success);
                }
            }, 3000);
        }
        

    </script>
</x-app-layout> 