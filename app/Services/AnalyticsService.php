<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Master;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Получить статистику для салона
     */
    public function getSalonStats(User $salon, string $period = 'month'): array
    {
        return $this->calculateSalonStats($salon, $period);
    }
    
    /**
     * Получить статистику для мастера
     */
    public function getMasterStats(Master $master, string $period = 'month'): array
    {
        return $this->calculateMasterStats($master, $period);
    }
    

    
    /**
     * Получить статистику для кастомного периода
     */
    public function getCustomPeriodStats(User $user, Carbon $dateFrom, Carbon $dateTo): array
    {
        if ($user->isSalon()) {
            return $this->calculateSalonStatsForCustomPeriod($user, $dateFrom, $dateTo);
        } else {
            $master = Master::where('user_id', $user->id)->first();
            if (!$master) {
                $master = Master::where('email', $user->email)->first();
            }
            
            if (!$master) {
                throw new \Exception('Профиль мастера не найден');
            }
            
            return $this->calculateMasterStatsForCustomPeriod($master, $dateFrom, $dateTo);
        }
    }
    
    /**
     * Расчет статистики салона (без кэширования)
     */
    private function calculateSalonStats(User $salon, string $period): array
    {
        \Log::debug('getSalonStats called', [
            'salon_id' => $salon->id,
            'salon_name' => $salon->name,
            'period' => $period
        ]);
        
        $dateFrom = $this->getDateFrom($period);
        $dateTo = $this->getDateTo($period);
            
        // Получаем мастеров салона через связь многие-ко-многим
        $masters = $salon->masters()->get();
        $masterIds = $masters->pluck('id')->toArray();
        
        // Отладка
        \Log::debug('Salon analytics debug:', [
            'salon_id' => $salon->id,
            'masters_count' => $masters->count(),
            'master_ids' => $masterIds,
            'period' => $period,
        ]);
        
        if (empty($masterIds)) {
            \Log::debug('No masters found, returning empty stats');
            return $this->getEmptyStats($period);
        }
        
        // Базовая статистика - фильтруем только записи мастеров этого салона
        $stats = [
            'total_appointments' => $this->getTotalAppointmentsForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
            'total_revenue' => $this->getTotalRevenueForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
            'active_clients' => $this->getActiveClientsForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
            'popular_services' => $this->getPopularServicesForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
        ];

        // Временная отладка расхождения количества записей
        try {
            $byStatus = DB::table('appointments')
                ->select(['status', DB::raw('COUNT(*) as cnt')])
                ->whereIn('master_id', $masterIds)
                ->whereBetween('start_time', [$dateFrom, $dateTo])
                ->groupBy('status')
                ->get();
            \Log::debug('Analytics appointments breakdown', [
                'salon_id' => $salon->id,
                'period' => $period,
                'date_from' => (string) $dateFrom,
                'date_to' => (string) $dateTo,
                'master_ids' => $masterIds,
                'by_status' => $byStatus,
                'total_count' => $byStatus->sum('cnt'),
            ]);
        } catch (\Throwable $e) {
            // no-op
        }
        
        // Статистика по мастерам
        $masterStats = $this->getMastersStatsForSalon($masters, $dateFrom, $dateTo);
        
        // Статистика по услугам
        $serviceStats = $this->getServiceStatsForSalon($salon->id, $masterIds, $dateFrom, $dateTo);
        
        // Клиентская аналитика
        $clientStats = $this->getClientStatsForSalon($salon->id, $masterIds, $dateFrom, $dateTo);
        
        $result = [
            'stats' => $stats,
            'masterStats' => $masterStats,
            'serviceStats' => $serviceStats,
            'clientStats' => $clientStats,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
        
        \Log::debug('getSalonStats result', [
            'stats_total_appointments' => $result['stats']['total_appointments'] ?? 'MISSING',
            'stats_total_revenue' => $result['stats']['total_revenue'] ?? 'MISSING',
            'masters_count' => count($result['masterStats']),
            'services_count' => count($result['serviceStats']),
        ]);
        
        return $result;
    }
    
    /**
     * Расчет статистики мастера (без кэширования)
     */
    private function calculateMasterStats(Master $master, string $period): array
    {
        $dateFrom = $this->getDateFrom($period);
        $dateTo = $this->getDateTo($period);
            
        // Базовая статистика
        $stats = [
            'total_appointments' => $this->getTotalAppointments([$master->id], $dateFrom, $dateTo),
            'total_revenue' => $this->getTotalRevenue([$master->id], $dateFrom, $dateTo),
            'active_clients' => $this->getMasterActiveClients($master->id, $dateFrom, $dateTo),
        ];
        
        // Клиентская база мастера
        $clientStats = $this->getMasterClientStats($master->id, $dateFrom, $dateTo);
        
        // Аналитика услуг мастера
        $serviceStats = $this->getMasterServiceStats($master->id, $dateFrom, $dateTo);
        
        // Производительность мастера
        $performanceStats = $this->getMasterPerformanceStats($master->id, $dateFrom, $dateTo);
        
        return [
            'stats' => $stats,
            'clientStats' => $clientStats,
            'serviceStats' => $serviceStats,
            'performanceStats' => $performanceStats,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'master' => $master,
        ];
    }
    
    /**
     * Получение даты начала периода
     */
    private function getDateFrom(string $period): Carbon
    {
        return match($period) {
            'week' => Carbon::now()->subDays(6)->startOfDay(), // Ровно 7 дней: 6 дней назад + сегодня
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }

    /**
     * Получение даты окончания периода
     * Для аналитики используем конец выбранного периода,
     * чтобы учитывать все записи (включая будущие в текущем периоде)
     */
    private function getDateTo(string $period): Carbon
    {
        return match($period) {
            'week' => Carbon::now()->endOfDay(),
            'month' => Carbon::now()->endOfMonth(),
            'quarter' => Carbon::now()->endOfQuarter(),
            'year' => Carbon::now()->endOfYear(),
            default => Carbon::now()->endOfMonth(),
        };
    }
    
    /**
     * Получение общего количества записей (оптимизированный запрос)
     */
    private function getTotalAppointments(array $masterIds, Carbon $dateFrom, Carbon $dateTo): int
    {
        if (empty($masterIds)) {
            return 0;
        }
        
        return DB::table('appointments')
            ->whereIn('master_id', $masterIds)
            ->whereBetween('start_time', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->count();
    }
    
    /**
     * Получение общего дохода (оптимизированный запрос)
     */
    private function getTotalRevenue(array $masterIds, Carbon $dateFrom, Carbon $dateTo): float
    {
        if (empty($masterIds)) {
            return 0.0;
        }
        
        return (float) DB::table('appointments')
            ->whereIn('master_id', $masterIds)
            ->whereBetween('start_time', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->sum('price');
    }
    
    /**
     * Получение количества активных клиентов
     */
    private function getActiveClients(int $userId, Carbon $dateFrom, Carbon $dateTo): int
    {
        return DB::table('clients')
            ->where('user_id', $userId)
            ->whereExists(function ($query) use ($dateFrom, $dateTo) {
                $query->select(DB::raw(1))
                    ->from('appointments')
                    ->whereColumn('appointments.client_id', 'clients.id')
                    ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                    ->where('appointments.status', '!=', 'cancelled');
            })
            ->count();
    }
    
    /**
     * Получение популярных услуг
     */
    private function getPopularServices(int $userId, Carbon $dateFrom, Carbon $dateTo): array
    {
        return DB::table('services')
            ->select([
                'services.*',
                DB::raw('COUNT(appointments.id) as appointments_count')
            ])
            ->leftJoin('appointments', function ($join) use ($dateFrom, $dateTo) {
                $join->on('services.id', '=', 'appointments.service_id')
                    ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                    ->where('appointments.status', '!=', 'cancelled');
            })
            ->where('services.user_id', $userId)
            ->groupBy('services.id')
            ->orderBy('appointments_count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    /**
     * Получение статистики по мастерам салона (оптимизированный запрос)
     */
    private function getMastersStatsForSalon($masters, Carbon $dateFrom, Carbon $dateTo): array
    {
        $masterIds = $masters->pluck('id')->toArray();
        
        if (empty($masterIds)) {
            return [];
        }
        
        // Получаем статистику одним запросом
        $stats = DB::table('appointments')
            ->select([
                'master_id',
                DB::raw('COUNT(*) as appointments_count'),
                DB::raw('SUM(CASE WHEN status != "cancelled" THEN price ELSE 0 END) as revenue')
            ])
            ->whereIn('master_id', $masterIds)
            ->whereBetween('start_time', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->groupBy('master_id')
            ->get()
            ->keyBy('master_id');
        
        return $masters->map(function ($master) use ($stats) {
            $masterStat = $stats->get($master->id);
            
            return [
                'master' => $master,
                'appointments_count' => $masterStat ? $masterStat->appointments_count : 0,
                'revenue' => $masterStat ? (float) $masterStat->revenue : 0.0,
            ];
        })->toArray();
    }
    
    /**
     * Получение статистики по услугам (оптимизированный запрос)
     */
    private function getServiceStats(int $userId, Carbon $dateFrom, Carbon $dateTo): array
    {
        return DB::table('services')
            ->select([
                'services.*',
                DB::raw('COUNT(appointments.id) as appointments_count'),
                DB::raw('SUM(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE 0 END) as revenue'),
                DB::raw('AVG(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE NULL END) as avg_price')
            ])
            ->leftJoin('appointments', function ($join) use ($dateFrom, $dateTo) {
                $join->on('services.id', '=', 'appointments.service_id')
                    ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                    ->where('appointments.status', '!=', 'cancelled');
            })
            ->where('services.user_id', $userId)
            ->groupBy('services.id')
            ->orderBy('appointments_count', 'desc')
            ->get()
            ->map(function ($service) {
                return [
                    'service' => $service,
                    'appointments_count' => (int) $service->appointments_count,
                    'revenue' => (float) $service->revenue,
                    'avg_price' => (float) $service->avg_price,
                ];
            })
            ->toArray();
    }
    
    /**
     * Получение клиентской статистики
     */
    private function getClientStats(int $userId, Carbon $dateFrom, Carbon $dateTo): array
    {
        $totalClients = DB::table('clients')
            ->where('user_id', $userId)
            ->count();
        
        $newClients = DB::table('clients')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
            
        $returningClients = DB::table('clients')
            ->where('user_id', $userId)
            ->whereIn('id', function ($query) use ($dateFrom, $dateTo) {
                $query->select('client_id')
                    ->from('appointments')
                    ->whereBetween('start_time', [$dateFrom, $dateTo])
                    ->where('status', '!=', 'cancelled')
                    ->groupBy('client_id')
                    ->havingRaw('COUNT(*) >= 2');
            })
            ->count();
            
        return [
            'total_clients' => $totalClients,
            'new_clients' => $newClients,
            'returning_clients' => $returningClients,
        ];
    }
    
    /**
     * Получение количества активных клиентов мастера
     */
    private function getMasterActiveClients(int $masterId, Carbon $dateFrom, Carbon $dateTo): int
    {
        return DB::table('appointments')
            ->where('master_id', $masterId)
            ->whereBetween('start_time', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->distinct('client_id')
            ->count('client_id');
    }
    
    /**
     * Получение клиентской статистики мастера
     */
    private function getMasterClientStats(int $masterId, Carbon $dateFrom, Carbon $dateTo): array
    {
        $totalActiveClients = $this->getMasterActiveClients($masterId, $dateFrom, $dateTo);
            
        $newClients = DB::table('appointments')
            ->join('clients', 'appointments.client_id', '=', 'clients.id')
            ->where('appointments.master_id', $masterId)
            ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
            ->where('appointments.status', '!=', 'cancelled')
            ->where('clients.created_at', '>=', $dateFrom)
            ->distinct('appointments.client_id')
            ->count('appointments.client_id');
            
        return [
            'total_active_clients' => $totalActiveClients,
            'new_clients' => $newClients,
        ];
    }
    
    /**
     * Получение статистики по услугам мастера
     */
    private function getMasterServiceStats(int $masterId, Carbon $dateFrom, Carbon $dateTo): array
    {
        return DB::table('appointments')
            ->select([
                'services.*',
                DB::raw('COUNT(appointments.id) as appointments_count'),
                DB::raw('SUM(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE 0 END) as revenue'),
                DB::raw('AVG(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE NULL END) as avg_price')
            ])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('appointments.master_id', $masterId)
            ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
            ->where('appointments.status', '!=', 'cancelled')
            ->groupBy('services.id')
            ->orderBy('appointments_count', 'desc')
            ->get()
            ->map(function ($service) {
                return [
                    'service' => $service,
                    'appointments_count' => (int) $service->appointments_count,
                    'revenue' => (float) $service->revenue,
                    'avg_price' => (float) $service->avg_price,
                ];
            })
            ->toArray();
    }
    
    /**
     * Получение статистики производительности мастера
     */
    private function getMasterPerformanceStats(int $masterId, Carbon $dateFrom, Carbon $dateTo): array
    {
        $daysDiff = $dateFrom->diffInDays($dateTo);
        $totalAppointments = $this->getTotalAppointments([$masterId], $dateFrom, $dateTo);
        
        // Средние записи в день
        $avgAppointmentsPerDay = $daysDiff > 0 ? round($totalAppointments / $daysDiff, 1) : 0;
        
        // Коэффициент использования рабочего времени (упрощенный расчет)
        $workingDays = $daysDiff; // Упрощенно считаем все дни рабочими
        $efficiency = $workingDays > 0 ? min(100, round(($totalAppointments / ($workingDays * 8)) * 100)) : 0;
        
        // Средняя длительность записи
        $dbDriver = config('database.default');
        
        if ($dbDriver === 'mysql') {
            $avgDuration = DB::table('appointments')
                ->where('master_id', $masterId)
                ->whereBetween('start_time', [$dateFrom, $dateTo])
                ->where('status', '!=', 'cancelled')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration')
                ->value('avg_duration');
        } else {
            // SQLite и другие СУБД
            $avgDuration = DB::table('appointments')
                ->where('master_id', $masterId)
                ->whereBetween('start_time', [$dateFrom, $dateTo])
                ->where('status', '!=', 'cancelled')
                ->selectRaw('AVG((strftime("%s", end_time) - strftime("%s", start_time)) / 60.0) as avg_duration')
                ->value('avg_duration');
        }
            
        return [
            'avg_appointments_per_day' => $avgAppointmentsPerDay,
            'efficiency_percentage' => $efficiency,
            'avg_duration_minutes' => round($avgDuration ?? 0),
            'working_days' => $workingDays,
        ];
    }
    
    /**
     * Расчет статистики салона для кастомного периода
     */
    private function calculateSalonStatsForCustomPeriod(User $salon, Carbon $dateFrom, Carbon $dateTo): array
    {
        // Получаем мастеров салона через связь многие-ко-многим
        $masters = $salon->masters()->get();
        $masterIds = $masters->pluck('id')->toArray();
        
        if (empty($masterIds)) {
            return $this->getEmptyStatsForCustomPeriod($dateFrom, $dateTo);
        }
        
        // Базовая статистика - используем методы для салона
        $stats = [
            'total_appointments' => $this->getTotalAppointmentsForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
            'total_revenue' => $this->getTotalRevenueForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
            'active_clients' => $this->getActiveClientsForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
            'popular_services' => $this->getPopularServicesForSalon($salon->id, $masterIds, $dateFrom, $dateTo),
        ];
        
        // Статистика по мастерам
        $masterStats = $this->getMastersStatsForSalon($masters, $dateFrom, $dateTo);
        
        // Статистика по услугам
        $serviceStats = $this->getServiceStatsForSalon($salon->id, $masterIds, $dateFrom, $dateTo);
        
        // Клиентская аналитика
        $clientStats = $this->getClientStatsForSalon($salon->id, $masterIds, $dateFrom, $dateTo);
        
        return [
            'stats' => $stats,
            'masterStats' => $masterStats,
            'serviceStats' => $serviceStats,
            'clientStats' => $clientStats,
            'period' => 'custom',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }
    
    /**
     * Расчет статистики мастера для кастомного периода
     */
    private function calculateMasterStatsForCustomPeriod(Master $master, Carbon $dateFrom, Carbon $dateTo): array
    {
        // Базовая статистика
        $stats = [
            'total_appointments' => $this->getTotalAppointments([$master->id], $dateFrom, $dateTo),
            'total_revenue' => $this->getTotalRevenue([$master->id], $dateFrom, $dateTo),
            'active_clients' => $this->getMasterActiveClients($master->id, $dateFrom, $dateTo),
        ];
        
        // Клиентская база мастера
        $clientStats = $this->getMasterClientStats($master->id, $dateFrom, $dateTo);
        
        // Аналитика услуг мастера
        $serviceStats = $this->getMasterServiceStats($master->id, $dateFrom, $dateTo);
        
        // Производительность мастера
        $performanceStats = $this->getMasterPerformanceStats($master->id, $dateFrom, $dateTo);
        
        return [
            'stats' => $stats,
            'clientStats' => $clientStats,
            'serviceStats' => $serviceStats,
            'performanceStats' => $performanceStats,
            'period' => 'custom',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'master' => $master,
        ];
    }
    
    /**
     * Получение пустой статистики для салонов без мастеров
     */
    private function getEmptyStats(string $period = 'month'): array
    {
        $dateFrom = $this->getDateFrom($period);
        $dateTo = $this->getDateTo($period);
        
        return [
            'stats' => [
                'total_appointments' => 0,
                'total_revenue' => 0,
                'active_clients' => 0,
                'popular_services' => [],
            ],
            'masterStats' => [],
            'serviceStats' => [],
            'clientStats' => [
                'total_clients' => 0,
                'new_clients' => 0,
                'returning_clients' => 0,
            ],
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }
    
    /**
     * Получение пустой статистики для кастомного периода
     */
    private function getEmptyStatsForCustomPeriod(Carbon $dateFrom, Carbon $dateTo): array
    {
        return [
            'stats' => [
                'total_appointments' => 0,
                'total_revenue' => 0,
                'active_clients' => 0,
                'popular_services' => [],
            ],
            'masterStats' => [],
            'serviceStats' => [],
            'clientStats' => [
                'total_clients' => 0,
                'new_clients' => 0,
                'returning_clients' => 0,
            ],
            'period' => 'custom',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }
    

    
    /**
     * Получение данных для графика доходов
     */
    public function getRevenueChartData(User $user, string $period = 'month'): array
    {
        $dateFrom = $this->getDateFrom($period);
        $dateTo = $this->getDateTo($period);
        
        if ($user->isSalon()) {
            $masters = $user->masters()->get();
            $masterIds = $masters->pluck('id')->toArray();
        } else {
            $master = Master::where('user_id', $user->id)->first();
            $masterIds = $master ? [$master->id] : [];
        }
        
        if (empty($masterIds)) {
            return ['labels' => [], 'data' => []];
        }
        
        // Генерируем интервалы в зависимости от периода
        $intervals = $this->generateIntervals($dateFrom, $dateTo, $period);
        
        $revenueData = [];
        $labels = [];
        
        foreach ($intervals as $interval) {
            $intervalStart = $interval['start'];
            $intervalEnd = $interval['end'];
            
            // Используем единый подход для салонов и мастеров
            // masterIds уже содержит правильные ID мастеров для данного пользователя
            $revenue = DB::table('appointments')
                ->whereIn('master_id', $masterIds)
                ->whereBetween('start_time', [$intervalStart, $intervalEnd])
                ->where('status', '!=', 'cancelled')
                ->sum('price');
                
            $revenueData[] = (float) $revenue;
            $labels[] = $interval['label'];
        }
        
        return [
            'labels' => $labels,
            'data' => $revenueData,
        ];
    }
    
    /**
     * Получение данных для графика услуг
     */
    public function getServicesChartData(User $user, string $period = 'month'): array
    {
        $dateFrom = $this->getDateFrom($period);
        $dateTo = $this->getDateTo($period);
        
        if ($user->isSalon()) {
            // Для салона получаем мастеров и фильтруем записи
            $masters = $user->masters()->get();
            $masterIds = $masters->pluck('id')->toArray();
            
            if (empty($masterIds)) {
                return [
                    'labels' => [],
                    'appointmentsData' => [],
                    'revenueData' => [],
                ];
            }
            
            $services = DB::table('services')
                ->select([
                    'services.name',
                    DB::raw('COUNT(appointments.id) as appointments_count'),
                    DB::raw('SUM(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE 0 END) as revenue')
                ])
                ->leftJoin('appointments', function ($join) use ($dateFrom, $dateTo, $masterIds) {
                    $join->on('services.id', '=', 'appointments.service_id')
                        ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                        ->where('appointments.status', '!=', 'cancelled')
                        ->whereIn('appointments.master_id', $masterIds);
                })
                ->where('services.user_id', $user->id)
                ->groupBy('services.id', 'services.name')
                ->orderBy('appointments_count', 'desc')
                ->limit(10)
                ->get();
        } else {
            // Для мастера используем обычный запрос
            $services = DB::table('services')
                ->select([
                    'services.name',
                    DB::raw('COUNT(appointments.id) as appointments_count'),
                    DB::raw('SUM(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE 0 END) as revenue')
                ])
                ->leftJoin('appointments', function ($join) use ($dateFrom, $dateTo) {
                    $join->on('services.id', '=', 'appointments.service_id')
                        ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                        ->where('appointments.status', '!=', 'cancelled');
                })
                ->where('services.user_id', $user->id)
                ->groupBy('services.id', 'services.name')
                ->orderBy('appointments_count', 'desc')
                ->limit(10)
                ->get();
        }
            
        return [
            'labels' => $services->pluck('name')->toArray(),
            'appointmentsData' => $services->pluck('appointments_count')->toArray(),
            'revenueData' => $services->pluck('revenue')->map(fn($r) => (float) $r)->toArray(),
        ];
    }
    
    /**
     * Получение данных для графика мастеров (только для салонов)
     */
    public function getMastersChartData(User $salon, string $period = 'month'): array
    {
        $dateFrom = $this->getDateFrom($period);
        $dateTo = $this->getDateTo($period);
        
        $masters = $salon->masters()->get();
        $masterIds = $masters->pluck('id')->toArray();
        
        if (empty($masterIds)) {
            return ['labels' => [], 'appointmentsData' => [], 'revenueData' => []];
        }
        
        // Фильтруем записи только для мастеров этого салона
        // masterIds уже содержит правильные ID мастеров для данного салона
        $stats = DB::table('appointments')
            ->select([
                'appointments.master_id',
                DB::raw('COUNT(*) as appointments_count'),
                DB::raw('SUM(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE 0 END) as revenue')
            ])
            ->whereIn('appointments.master_id', $masterIds)
            ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
            ->where('appointments.status', '!=', 'cancelled')
            ->groupBy('appointments.master_id')
            ->get()
            ->keyBy('master_id');
            
        $labels = [];
        $appointmentsData = [];
        $revenueData = [];
        
        foreach ($masters as $master) {
            $masterStat = $stats->get($master->id);
            
            $labels[] = $master->name;
            $appointmentsData[] = $masterStat ? (int) $masterStat->appointments_count : 0;
            $revenueData[] = $masterStat ? (float) $masterStat->revenue : 0.0;
        }
        
        return [
            'labels' => $labels,
            'appointmentsData' => $appointmentsData,
            'revenueData' => $revenueData,
        ];
    }
    
    /**
     * Генерация временных интервалов для графиков
     */
    private function generateIntervals(Carbon $dateFrom, Carbon $dateTo, string $period): array
    {
        $intervals = [];
        
        switch ($period) {
            case 'week':
                // По дням для недели
                $current = $dateFrom->copy();
                while ($current <= $dateTo) {
                    $intervalEnd = $current->copy()->endOfDay();
                    if ($intervalEnd > $dateTo) {
                        $intervalEnd = $dateTo;
                    }
                    
                    $intervals[] = [
                        'start' => $current->copy()->startOfDay(),
                        'end' => $intervalEnd,
                        'label' => $current->format('d.m'),
                    ];
                    
                    $current->addDay();
                }
                break;
                
            case 'month':
                // По неделям для месяца
                $current = $dateFrom->copy()->startOfWeek();
                $weekNum = 1;
                while ($current <= $dateTo) {
                    $intervalEnd = $current->copy()->endOfWeek();
                    if ($intervalEnd > $dateTo) {
                        $intervalEnd = $dateTo;
                    }
                    
                    $intervals[] = [
                        'start' => $current->copy(),
                        'end' => $intervalEnd,
                        'label' => "Неделя {$weekNum}",
                    ];
                    
                    $current->addWeek();
                    $weekNum++;
                }
                break;
                
            case 'quarter':
                // По месяцам для квартала
                $current = $dateFrom->copy()->startOfMonth();
                while ($current <= $dateTo) {
                    $intervalEnd = $current->copy()->endOfMonth();
                    if ($intervalEnd > $dateTo) {
                        $intervalEnd = $dateTo;
                    }
                    
                    $intervals[] = [
                        'start' => $current->copy(),
                        'end' => $intervalEnd,
                        'label' => $current->format('M'),
                    ];
                    
                    $current->addMonth();
                }
                break;
                
            case 'year':
                // По кварталам для года
                $quarters = [
                    ['start' => $dateFrom->copy()->month(1)->startOfMonth(), 'label' => 'Q1'],
                    ['start' => $dateFrom->copy()->month(4)->startOfMonth(), 'label' => 'Q2'],
                    ['start' => $dateFrom->copy()->month(7)->startOfMonth(), 'label' => 'Q3'],
                    ['start' => $dateFrom->copy()->month(10)->startOfMonth(), 'label' => 'Q4'],
                ];
                
                foreach ($quarters as $quarter) {
                    $intervalEnd = $quarter['start']->copy()->addMonths(2)->endOfMonth();
                    if ($intervalEnd > $dateTo) {
                        $intervalEnd = $dateTo;
                    }
                    if ($quarter['start'] <= $dateTo) {
                        $intervals[] = [
                            'start' => $quarter['start'],
                            'end' => $intervalEnd,
                            'label' => $quarter['label'],
                        ];
                    }
                }
                break;
        }
        
        return $intervals;
    }

    /**
     * Получение общего количества записей для салона (только мастера этого салона)
     */
    private function getTotalAppointmentsForSalon(int $salonId, array $masterIds, Carbon $dateFrom, Carbon $dateTo): int
    {
        if (empty($masterIds)) {
            return 0;
        }
        
        // Важно: учитываем только те записи, которые связаны с услугами текущего салона
        return DB::table('appointments')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('services.user_id', $salonId)
            ->whereIn('appointments.master_id', $masterIds)
            ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
            ->where('appointments.status', '!=', 'cancelled')
            ->count();
    }
    
    /**
     * Получение общего дохода для салона (только мастера этого салона)
     */
    private function getTotalRevenueForSalon(int $salonId, array $masterIds, Carbon $dateFrom, Carbon $dateTo): float
    {
        if (empty($masterIds)) {
            return 0.0;
        }
        
        // Важно: учитываем только услуги текущего салона
        return (float) DB::table('appointments')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('services.user_id', $salonId)
            ->whereIn('appointments.master_id', $masterIds)
            ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
            ->where('appointments.status', '!=', 'cancelled')
            ->sum('appointments.price');
    }
    
    /**
     * Получение количества активных клиентов для салона
     */
    private function getActiveClientsForSalon(int $salonId, array $masterIds, Carbon $dateFrom, Carbon $dateTo): int
    {
        if (empty($masterIds)) {
            return 0;
        }
        
        return DB::table('appointments')
            ->join('salon_master', 'appointments.master_id', '=', 'salon_master.master_id')
            ->where('salon_master.salon_id', $salonId)
            ->whereIn('appointments.master_id', $masterIds)
            ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
            ->where('appointments.status', '!=', 'cancelled')
            ->distinct('appointments.client_id')
            ->count('appointments.client_id');
    }
    
    /**
     * Получение популярных услуг для салона
     */
    private function getPopularServicesForSalon(int $salonId, array $masterIds, Carbon $dateFrom, Carbon $dateTo): array
    {
        if (empty($masterIds)) {
            return [];
        }
        
        return DB::table('services')
            ->select([
                'services.*',
                DB::raw('COUNT(appointments.id) as appointments_count')
            ])
            ->leftJoin('appointments', function ($join) use ($dateFrom, $dateTo, $masterIds) {
                $join->on('services.id', '=', 'appointments.service_id')
                    ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                    ->where('appointments.status', '!=', 'cancelled')
                    ->whereIn('appointments.master_id', $masterIds);
            })
            ->where('services.user_id', $salonId)
            ->groupBy('services.id')
            ->orderBy('appointments_count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Получение статистики по услугам для салона (оптимизированный запрос)
     */
    private function getServiceStatsForSalon(int $salonId, array $masterIds, Carbon $dateFrom, Carbon $dateTo): array
    {
        if (empty($masterIds)) {
            return [];
        }
        
        return DB::table('services')
            ->select([
                'services.*',
                DB::raw('COUNT(appointments.id) as appointments_count'),
                DB::raw('SUM(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE 0 END) as revenue'),
                DB::raw('AVG(CASE WHEN appointments.status != "cancelled" THEN appointments.price ELSE NULL END) as avg_price')
            ])
            ->leftJoin('appointments', function ($join) use ($dateFrom, $dateTo, $masterIds) {
                $join->on('services.id', '=', 'appointments.service_id')
                    ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                    ->where('appointments.status', '!=', 'cancelled')
                    ->whereIn('appointments.master_id', $masterIds);
            })
            ->where('services.user_id', $salonId)
            ->groupBy('services.id')
            ->orderBy('appointments_count', 'desc')
            ->get()
            ->map(function ($service) {
                return [
                    'service' => $service,
                    'appointments_count' => (int) $service->appointments_count,
                    'revenue' => (float) $service->revenue,
                    'avg_price' => (float) $service->avg_price,
                ];
            })
            ->toArray();
    }
    
    /**
     * Получение клиентской статистики для салона
     */
    private function getClientStatsForSalon(int $salonId, array $masterIds, Carbon $dateFrom, Carbon $dateTo): array
    {
        if (empty($masterIds)) {
            return [
                'total_clients' => 0,
                'new_clients' => 0,
                'returning_clients' => 0,
            ];
        }
        
        $totalClients = DB::table('clients')
            ->where('user_id', $salonId)
            ->count();
        
        $newClients = DB::table('clients')
            ->where('user_id', $salonId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
            
        $returningClients = DB::table('clients')
            ->where('user_id', $salonId)
            ->whereIn('id', function ($query) use ($dateFrom, $dateTo, $masterIds) {
                $query->select('appointments.client_id')
                    ->from('appointments')
                    ->whereIn('appointments.master_id', $masterIds)
                    ->whereBetween('appointments.start_time', [$dateFrom, $dateTo])
                    ->where('appointments.status', '!=', 'cancelled')
                    ->groupBy('appointments.client_id')
                    ->havingRaw('COUNT(*) >= 2');
            })
            ->count();
            
        return [
            'total_clients' => $totalClients,
            'new_clients' => $newClients,
            'returning_clients' => $returningClients,
        ];
    }
} 