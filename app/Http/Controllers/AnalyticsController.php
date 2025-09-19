<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;
    
    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }
    
    /**
     * Главная страница аналитики - перенаправляет в зависимости от типа пользователя
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        if ($user->isSalon()) {
            return $this->salonDashboard($request);
        } elseif ($user->isMaster()) {
            return $this->masterDashboard($request);
        }
        
        abort(403, 'У вас нет доступа к статистике');
    }
    
    /**
     * Дашборд статистики для салонов
     */
    public function salonDashboard(Request $request): View
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут просматривать эту статистику');
        }
        
        $period = $request->get('period', 'month');
        
        // Обработка кастомного периода
        if ($period === 'custom') {
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            if ($dateFrom && $dateTo) {
                try {
                    $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
                    $dateToCarbon = Carbon::parse($dateTo)->endOfDay();
                    
                    $data = $this->analyticsService->getCustomPeriodStats($user, $dateFromCarbon, $dateToCarbon);
                } catch (\Exception $e) {
                    Log::error('Error processing custom period:', ['error' => $e->getMessage()]);
                    return redirect()->route('analytics')->with('error', 'Ошибка при обработке кастомного периода');
                }
            } else {
                return redirect()->route('analytics')->with('error', 'Для кастомного периода требуются даты начала и окончания');
            }
        } else {
            $data = $this->analyticsService->getSalonStats($user, $period);
        }
        
        // Отладка: проверим что содержат данные
        Log::debug('Analytics data:', [
            'user_id' => $user->id,
            'period' => $period,
            'stats' => $data['stats'] ?? 'НЕТ STATS',
            'masters_count' => isset($data['masterStats']) ? count($data['masterStats']) : 'НЕТ MASTERS',
        ]);
        
        return view('analytics.salon-dashboard', $data);
    }
    
    /**
     * Дашборд статистики для мастеров
     */
    public function masterDashboard(Request $request): View
    {
        $user = $request->user();
        
        if (!$user->isMaster()) {
            abort(403, 'Только мастера могут просматривать эту статистику');
        }
        
        // Находим профиль мастера
        $master = Master::where('user_id', $user->id)->first();
        if (!$master) {
            $master = Master::where('email', $user->email)->first();
        }
        
        if (!$master) {
            abort(404, 'Профиль мастера не найден');
        }
        
        $period = $request->get('period', 'month');
        
        // Обработка кастомного периода
        if ($period === 'custom') {
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            if ($dateFrom && $dateTo) {
                try {
                    $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
                    $dateToCarbon = Carbon::parse($dateTo)->endOfDay();
                    
                    $data = $this->analyticsService->getCustomPeriodStats($user, $dateFromCarbon, $dateToCarbon);
                } catch (\Exception $e) {
                    Log::error('Error processing custom period for master:', ['error' => $e->getMessage()]);
                    return redirect()->route('analytics')->with('error', 'Ошибка при обработке кастомного периода');
                }
            } else {
                return redirect()->route('analytics')->with('error', 'Для кастомного периода требуются даты начала и окончания');
            }
        } else {
            $data = $this->analyticsService->getMasterStats($master, $period);
        }
        
        return view('analytics.master-dashboard', $data);
    }
    

    
    /**
     * Получение статистики для кастомного периода
     */
    public function getCustomPeriodStats(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);
        
        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();
        
        try {
            $data = $this->analyticsService->getCustomPeriodStats($user, $dateFrom, $dateTo);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * API для получения данных графика доходов
     */
    public function getRevenueChartData(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'month');
        
        $chartData = $this->analyticsService->getRevenueChartData($user, $period);
        
        // Отладочная информация
        \Log::debug('Revenue chart data:', [
            'user_id' => $user->id,
            'period' => $period,
            'labels' => $chartData['labels'],
            'data' => $chartData['data'],
            'data_sum' => array_sum($chartData['data'])
        ]);
        
        return response()->json($chartData);
    }
    
    /**
     * API для получения данных графика услуг
     */
    public function getServicesChartData(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'month');
        
        $chartData = $this->analyticsService->getServicesChartData($user, $period);
        
        // Отладочная информация
        \Log::debug('Services chart data:', [
            'user_id' => $user->id,
            'period' => $period,
            'labels' => $chartData['labels'],
            'appointmentsData' => $chartData['appointmentsData'],
            'revenueData' => $chartData['revenueData'],
            'revenue_sum' => array_sum($chartData['revenueData'] ?? [])
        ]);
        
        return response()->json($chartData);
    }
    
    /**
     * API для получения данных графика мастеров
     */
    public function getMastersChartData(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'month');
        
        if (!$user->isSalon()) {
            return response()->json(['error' => 'Доступно только для салонов'], 403);
        }
        
        $chartData = $this->analyticsService->getMastersChartData($user, $period);
        
        return response()->json($chartData);
    }
    
    /**
     * Экспорт отчета в PDF
     */
    public function exportToPdf(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'month');
        
        Log::info('PDF Export request started', [
            'user_id' => $user->id,
            'period' => $period,
            'user_type' => $user->isSalon() ? 'salon' : 'master'
        ]);
        
        try {
            // Валидация для кастомного периода
            if ($period === 'custom') {
                $request->validate([
                    'date_from' => 'required|date',
                    'date_to' => 'required|date|after_or_equal:date_from',
                ]);
                
                $dateFrom = Carbon::parse($request->date_from);
                $dateTo = Carbon::parse($request->date_to);
                $data = $this->analyticsService->getCustomPeriodStats($user, $dateFrom, $dateTo);
            } else {
                if ($user->isSalon()) {
                    $data = $this->analyticsService->getSalonStats($user, $period);
                } else {
                    $master = Master::where('user_id', $user->id)->first();
                    if (!$master) {
                        $master = Master::where('email', $user->email)->first();
                    }
                    
                    if (!$master) {
                        Log::error('Master profile not found for user', ['user_id' => $user->id]);
                        abort(404, 'Профиль мастера не найден');
                    }
                    $data = $this->analyticsService->getMasterStats($master, $period);
                }
            }
            
            Log::info('PDF Export data collected', [
                'data_keys' => array_keys($data),
                'stats_available' => isset($data['stats'])
            ]);
            
            // Подготавливаем данные для PDF шаблона
            $pdfData = [
                'user' => $user,
                'period' => $period,
                'periodTitle' => $this->getPeriodTitle($period, $request),
                'generated_at' => Carbon::now()->format('d.m.Y H:i'),
                'userType' => $user->isSalon() ? 'salon' : 'master',
                'stats' => $data['stats'] ?? [],
                'serviceStats' => $data['serviceStats'] ?? [],
                'masterStats' => $data['masterStats'] ?? [],
                'clientStats' => $data['clientStats'] ?? [],
                'topClients' => $data['topClients'] ?? [],
                'performanceStats' => $data['performanceStats'] ?? [],
            ];
            
            Log::info('PDF Export generating PDF...');
            
            $pdf = Pdf::loadView('analytics.export.pdf-report', $pdfData);
            
            $filename = ($user->isSalon() ? 'salon' : 'master') . '_analytics_report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
            
            Log::info('PDF Export successful', ['filename' => $filename]);
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('PDF Export error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Ошибка при генерации PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Получение названия периода
     */
    private function getPeriodTitle(string $period, Request $request = null): string
    {
        if ($period === 'custom' && $request) {
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            if ($dateFrom && $dateTo) {
                $formattedDateFrom = Carbon::parse($dateFrom)->format('d.m.Y');
                $formattedDateTo = Carbon::parse($dateTo)->format('d.m.Y');
                return "Кастомный период: {$formattedDateFrom} - {$formattedDateTo}";
            }
        }
        
        return match($period) {
            'week' => 'Последние 7 дней',
            'month' => 'Месяц',
            'quarter' => 'Квартал',
            'year' => 'Год',
            'custom' => 'Кастомный период',
            default => 'Месяц',
        };
    }
} 