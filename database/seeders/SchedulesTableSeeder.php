<?php

namespace Database\Seeders;

use App\Models\Master;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SchedulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех мастеров
        $masters = Master::all();
        
        if ($masters->count() === 0) {
            $this->command->warn('Нет мастеров для создания расписания.');
            return;
        }
        
        // Создаем расписание на следующие 2 недели
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->addWeeks(2)->endOfWeek();
        
        // Стандартные часы работы по дням недели
        $defaultWorkingHours = [
            0 => ['10:00', '18:00'], // Воскресенье
            1 => ['09:00', '19:00'], // Понедельник
            2 => ['09:00', '19:00'], // Вторник
            3 => ['09:00', '19:00'], // Среда
            4 => ['09:00', '19:00'], // Четверг
            5 => ['09:00', '19:00'], // Пятница
            6 => ['10:00', '18:00'], // Суббота
        ];
        
        // Случайные цвета для расписания
        $colors = ['#FF9AA2', '#FFB7B2', '#FFDAC1', '#E2F0CB', '#B5EAD7', '#C7CEEA'];
        
        // Для каждого мастера создаем расписание
        foreach ($masters as $master) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dayOfWeek = $currentDate->dayOfWeek;
                
                // Определяем, будет ли это выходной день
                $isDayOff = rand(1, 100) <= 5; // 5% шанс выходного дня
                
                // Если воскресенье, больше шансов что это выходной
                if ($dayOfWeek === 0 && rand(1, 100) <= 50) {
                    $isDayOff = true;
                }
                
                // Создаем запись расписания
                $workingHours = $defaultWorkingHours[$dayOfWeek];
                
                // Случайные перерывы
                $breaks = [];
                if (rand(1, 100) <= 70 && !$isDayOff) { // 70% шанс перерыва
                    $breakStart = Carbon::createFromFormat('H:i', $workingHours[0])->addHours(rand(2, 4))->format('H:i');
                    $breakEnd = Carbon::createFromFormat('H:i', $breakStart)->addMinutes(rand(30, 60))->format('H:i');
                    $breaks[] = [
                        'start' => $breakStart,
                        'end' => $breakEnd,
                        'reason' => 'Обеденный перерыв'
                    ];
                }
                
                $schedule = Schedule::create([
                    'master_id' => $master->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'start_time' => $isDayOff ? null : $workingHours[0],
                    'end_time' => $isDayOff ? null : $workingHours[1],
                    'is_day_off' => $isDayOff,
                    'notes' => $isDayOff ? 'Выходной день' : null,
                    'is_recurring' => false,
                    'recurring_until' => null,
                    'breaks' => $breaks,
                    'color_code' => $colors[array_rand($colors)],
                    'is_visible_online' => true,
                ]);
                
                // Переходим к следующему дню
                $currentDate->addDay();
            }
            
            // Добавляем повторяющийся график (для примера)
            if (rand(1, 100) <= 30) { // 30% шанс
                $recurringDay = Carbon::now()->addDays(rand(3, 10));
                $dayOfWeek = $recurringDay->dayOfWeek;
                
                Schedule::create([
                    'master_id' => $master->id,
                    'date' => $recurringDay->format('Y-m-d'),
                    'start_time' => $defaultWorkingHours[$dayOfWeek][0],
                    'end_time' => $defaultWorkingHours[$dayOfWeek][1],
                    'is_day_off' => false,
                    'notes' => 'Повторяющийся график',
                    'is_recurring' => true,
                    'recurring_until' => $recurringDay->copy()->addMonths(1)->format('Y-m-d'),
                    'breaks' => [],
                    'color_code' => $colors[array_rand($colors)],
                    'is_visible_online' => true,
                ]);
            }
        }
    }
}
