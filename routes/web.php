<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\SalonScheduleController;
use App\Http\Controllers\SalonMastersController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReminderTemplateController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('register');
});

// Демонстрационная страница адаптивной системы
Route::get('/adaptive-demo', function () {
    return view('adaptive-demo');
})->name('adaptive-demo');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Маршруты для записей
Route::middleware('auth')->group(function () {
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
});

// Маршруты для клиентов
Route::middleware('auth')->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
});

// Маршруты для услуг
Route::middleware('auth')->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::patch('/services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle-status');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
});

// Маршруты для расписания
Route::middleware('auth')->group(function () {
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}', [ScheduleController::class, 'show'])->name('schedules.show');
    Route::get('/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
});

// Маршруты для календаря
Route::middleware('auth')->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/create-appointment', [CalendarController::class, 'createAppointment'])->name('calendar.create-appointment');
    Route::post('/calendar/store-appointment', [CalendarController::class, 'storeAppointment'])->name('calendar.store-appointment');
    Route::get('/calendar/{date}', [CalendarController::class, 'day'])->name('calendar.day');
});

// Маршруты для салонов
Route::middleware(['auth', 'salon'])->group(function () {
    Route::get('/salon/schedules', [SalonScheduleController::class, 'index'])->name('salon.schedules.index');
    Route::get('/salon/schedules/create', [SalonScheduleController::class, 'create'])->name('salon.schedules.create');
    Route::post('/salon/schedules', [SalonScheduleController::class, 'store'])->name('salon.schedules.store');
    Route::get('/salon/schedules/{schedule}', [SalonScheduleController::class, 'show'])->name('salon.schedules.show');
    Route::get('/salon/schedules/{schedule}/edit', [SalonScheduleController::class, 'edit'])->name('salon.schedules.edit');
    Route::put('/salon/schedules/{schedule}', [SalonScheduleController::class, 'update'])->name('salon.schedules.update');
    Route::delete('/salon/schedules/{schedule}', [SalonScheduleController::class, 'destroy'])->name('salon.schedules.destroy');
});

// Маршруты для мастеров салона
Route::middleware(['auth', 'salon'])->group(function () {
    Route::get('/salon/masters', [SalonMastersController::class, 'index'])->name('salon.masters.index');
    Route::get('/salon/masters/create', [SalonMastersController::class, 'create'])->name('salon.masters.create');
    Route::post('/salon/masters', [SalonMastersController::class, 'store'])->name('salon.masters.store');
    Route::get('/salon/masters/{master}', [SalonMastersController::class, 'show'])->name('salon.masters.show');
    Route::get('/salon/masters/{master}/edit', [SalonMastersController::class, 'edit'])->name('salon.masters.edit');
    Route::put('/salon/masters/{master}', [SalonMastersController::class, 'update'])->name('salon.masters.update');
    Route::delete('/salon/masters/{master}/remove', [SalonMastersController::class, 'removeMaster'])->name('salon.masters.remove');
    Route::delete('/salon/masters/{master}', [SalonMastersController::class, 'destroy'])->name('salon.masters.destroy');
});

// Маршруты для аналитики
Route::middleware('auth')->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::post('/analytics/report', [AnalyticsController::class, 'generateReport'])->name('analytics.report');
    
    // API маршруты для получения данных графиков
    Route::get('/analytics/api/custom-period', [AnalyticsController::class, 'getCustomPeriodStats'])->name('analytics.api.custom-period');
    Route::get('/analytics/api/revenue-chart', [AnalyticsController::class, 'getRevenueChartData'])->name('analytics.api.revenue-chart');
    Route::get('/analytics/api/services-chart', [AnalyticsController::class, 'getServicesChartData'])->name('analytics.api.services-chart');
    Route::get('/analytics/api/masters-chart', [AnalyticsController::class, 'getMastersChartData'])->name('analytics.api.masters-chart');
    
    // Экспорт в PDF
    Route::get('/analytics/export/pdf', [AnalyticsController::class, 'exportToPdf'])->name('analytics.export.pdf');
});

// Маршруты для шаблонов напоминаний
Route::middleware('auth')->group(function () {
    Route::get('/reminder-templates', [ReminderTemplateController::class, 'index'])->name('reminder-templates.index');
    Route::get('/reminder-templates/create', [ReminderTemplateController::class, 'create'])->name('reminder-templates.create');
    Route::post('/reminder-templates', [ReminderTemplateController::class, 'store'])->name('reminder-templates.store');
    Route::get('/reminder-templates/{template}', [ReminderTemplateController::class, 'show'])->name('reminder-templates.show');
    Route::get('/reminder-templates/{template}/edit', [ReminderTemplateController::class, 'edit'])->name('reminder-templates.edit');
    Route::put('/reminder-templates/{template}', [ReminderTemplateController::class, 'update'])->name('reminder-templates.update');
    Route::delete('/reminder-templates/{template}', [ReminderTemplateController::class, 'destroy'])->name('reminder-templates.destroy');
    // Доп. API для модалки в редактировании записи
    Route::get('/reminder-templates/active', [ReminderTemplateController::class, 'getActiveTemplates'])->name('reminder-templates.active');
    Route::get('/reminder-templates/{reminderTemplate}/preview', [ReminderTemplateController::class, 'preview'])->name('reminder-templates.preview');
});

require __DIR__.'/auth.php';
