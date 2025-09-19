<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Master;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех клиентов, мастеров и услуги
        $clients = Client::all();
        $masters = Master::all();
        $services = Service::all();
        
        if ($clients->count() === 0 || $masters->count() === 0 || $services->count() === 0) {
            $this->command->warn('Недостаточно данных для создания записей.');
            return;
        }
        
        // Статусы для записей
        $statuses = [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_NO_SHOW,
        ];
        
        // Создаем записи на прошедшие даты (история)
        $this->createHistoricalAppointments($clients, $masters, $services, $statuses);
        
        // Создаем записи на предстоящие даты
        $this->createUpcomingAppointments($clients, $masters, $services, $statuses);
        
        // Создаем случайные записи
        $this->createRandomAppointments($clients, $masters, $services, $statuses);
    }
    
    /**
     * Создаем записи на прошедшие даты (история)
     */
    private function createHistoricalAppointments($clients, $masters, $services, $statuses)
    {
        // Создаем записи для прошедших дат
        $startDate = Carbon::now()->subMonths(2);
        $endDate = Carbon::now()->subDay();
        
        // Выбираем случайное количество записей для создания
        $appointmentsToCreate = rand(20, 40);
        
        for ($i = 0; $i < $appointmentsToCreate; $i++) {
            // Выбираем случайного клиента, мастера и услугу
            $client = $clients->random();
            $master = $masters->random();
            $service = $services->random();
            
            // Проверяем, оказывает ли мастер эту услугу
            $masterServices = $master->services;
            if ($masterServices->count() > 0 && rand(1, 100) <= 70) {
                // 70% шанс выбрать услугу, которую оказывает мастер
                $service = $masterServices->random();
            }
            
            // Генерируем случайную дату в прошлом
            $startTime = Carbon::createFromTimestamp(rand(
                $startDate->timestamp, 
                $endDate->timestamp
            ))->setTime(rand(9, 18), rand(0, 3) * 15);
            
            // Продолжительность услуги
            $duration = $service->duration;
            $endTime = (clone $startTime)->addMinutes($duration);
            
            // Статус (для прошедших записей обычно completed или cancelled)
            $completedStatuses = [
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_NO_SHOW,
            ];
            $status = $completedStatuses[array_rand($completedStatuses)];
            
            // Создаем запись
            Appointment::create([
                'client_id' => $client->id,
                'master_id' => $master->id,
                'service_id' => $service->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'price' => $service->price,
                'status' => $status,
                'is_confirmed' => $status != Appointment::STATUS_PENDING,
                'is_paid' => $status == Appointment::STATUS_COMPLETED && rand(1, 100) <= 90, // 90% оплаченных
                'payment_method' => rand(1, 100) <= 70 ? 'cash' : 'card',
                'notes' => fake()->optional(0.3)->sentence,
                'created_by_user_id' => $master->user_id ?? User::inRandomOrder()->first()->id,
            ]);
        }
    }
    
    /**
     * Создаем записи на предстоящие даты
     */
    private function createUpcomingAppointments($clients, $masters, $services, $statuses)
    {
        // Получаем все расписания для определения доступных слотов
        $schedules = Schedule::where('date', '>=', Carbon::now()->format('Y-m-d'))
            ->where('date', '<=', Carbon::now()->addDays(14)->format('Y-m-d'))
            ->where('is_day_off', false)
            ->get();
        
        if ($schedules->count() === 0) {
            return;
        }
        
        // Группируем расписания по мастерам
        $schedulesByMaster = $schedules->groupBy('master_id');
        
        // Создаем 1-3 записи для каждого мастера, у которого есть расписание
        foreach ($schedulesByMaster as $masterId => $masterSchedules) {
            $master = $masters->find($masterId);
            if (!$master) continue;
            
            // Получаем случайные расписания для этого мастера
            $selectedSchedules = $masterSchedules->random(min(3, $masterSchedules->count()));
            
            foreach ($selectedSchedules as $schedule) {
                if (rand(1, 100) <= 70) { // 70% шанс создать запись для этого расписания
                    // Выбираем случайного клиента
                    $client = $clients->random();
                    
                    // Выбираем случайную услугу
                    $service = $services->random();
                    
                    // Проверяем, оказывает ли мастер эту услугу
                    $masterServices = $master->services;
                    if ($masterServices->count() > 0 && rand(1, 100) <= 70) {
                        // 70% шанс выбрать услугу, которую оказывает мастер
                        $service = $masterServices->random();
                    }
                    
                    // Генерируем время начала записи (внутри рабочего дня)
                    $scheduleStart = Carbon::createFromFormat('Y-m-d H:i', $schedule->date . ' ' . $schedule->start_time);
                    $scheduleEnd = Carbon::createFromFormat('Y-m-d H:i', $schedule->date . ' ' . $schedule->end_time);
                    
                    // Максимальное время начала записи (учитывая длительность услуги)
                    $maxStart = (clone $scheduleEnd)->subMinutes($service->duration);
                    
                    // Если слишком поздно для записи, пропускаем
                    if ($scheduleStart->gt($maxStart)) {
                        continue;
                    }
                    
                    // Разбиваем день на 30-минутные слоты и выбираем случайный
                    $interval = new \DateInterval('PT30M'); // 30 минут
                    $slots = [];
                    $current = clone $scheduleStart;
                    
                    while ($current <= $maxStart) {
                        $slots[] = clone $current;
                        $current->add($interval);
                    }
                    
                    if (count($slots) === 0) {
                        continue;
                    }
                    
                    // Выбираем случайный слот
                    $startTime = $slots[array_rand($slots)];
                    $endTime = (clone $startTime)->addMinutes($service->duration);
                    
                    // Статус (для предстоящих записей обычно pending или confirmed)
                    $upcomingStatuses = [
                        Appointment::STATUS_PENDING,
                        Appointment::STATUS_CONFIRMED,
                    ];
                    $status = $upcomingStatuses[array_rand($upcomingStatuses)];
                    
                    // Создаем запись
                    Appointment::create([
                        'client_id' => $client->id,
                        'master_id' => $master->id,
                        'service_id' => $service->id,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'price' => $service->price,
                        'status' => $status,
                        'is_confirmed' => $status == Appointment::STATUS_CONFIRMED,
                        'is_paid' => false, // предстоящие записи еще не оплачены
                        'payment_method' => null,
                        'notes' => fake()->optional(0.3)->sentence,
                        'created_by_user_id' => $master->user_id ?? User::inRandomOrder()->first()->id,
                    ]);
                }
            }
        }
    }
    
    /**
     * Создаем случайные записи для заполнения базы
     */
    private function createRandomAppointments($clients, $masters, $services, $statuses)
    {
        // Создаем несколько случайных записей с помощью фабрики
        Appointment::factory()
            ->count(10)
            ->state(function () use ($clients, $masters, $services) {
                $client = $clients->random();
                $master = $masters->random();
                $service = $services->random();
                
                $startTime = Carbon::now()->addDays(rand(-30, 14))
                    ->setTime(rand(9, 18), rand(0, 3) * 15);
                $endTime = (clone $startTime)->addMinutes($service->duration);
                
                return [
                    'client_id' => $client->id,
                    'master_id' => $master->id,
                    'service_id' => $service->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'price' => $service->price,
                ];
            })
            ->create();
    }
}
