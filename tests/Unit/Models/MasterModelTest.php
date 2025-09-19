<?php

namespace Tests\Unit\Models;

use App\Models\Master;
use App\Models\User;
use App\Models\Service;
use App\Models\Schedule;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания мастера и проверка атрибутов.
     */
    public function test_master_can_be_created_with_attributes(): void
    {
        $user = User::factory()->create(['user_type' => 'master']);
        
        $masterData = [
            'user_id' => $user->id,
            'name' => 'Мастер Тест',
            'email' => 'master@example.com',
            'phone' => '+7 999 987 65 43',
            'bio' => 'Биография мастера',
            'photo_path' => 'masters/photo.jpg',
            'is_active' => true,
            'salon_id' => null,
            'specialization' => 'Маникюр',
            'experience_years' => 5,
            'certificates' => ['cert1.jpg', 'cert2.jpg'],
            'rating' => 4.8,
        ];
        
        $master = Master::create($masterData);
        
        $this->assertDatabaseHas('masters', [
            'user_id' => $user->id,
            'name' => 'Мастер Тест',
            'email' => 'master@example.com',
            'phone' => '+7 999 987 65 43',
            'is_active' => 1,
        ]);
        
        $this->assertEquals('Мастер Тест', $master->name);
        $this->assertEquals('master@example.com', $master->email);
        $this->assertEquals('Маникюр', $master->specialization);
        $this->assertEquals(5, $master->experience_years);
        $this->assertEquals(4.8, $master->rating);
        $this->assertIsArray($master->certificates);
    }
    
    /**
     * Тест отношения мастера к пользователю.
     */
    public function test_master_user_relationship(): void
    {
        $user = User::factory()->create(['user_type' => 'master']);
        $master = Master::factory()->create(['user_id' => $user->id]);
        
        $this->assertEquals($user->id, $master->user->id);
    }
    
    /**
     * Тест отношения мастера к салону.
     */
    public function test_master_salon_relationship(): void
    {
        $salon = User::factory()->create(['user_type' => 'salon']);
        $master = Master::factory()->create(['salon_id' => $salon->id]);
        
        $this->assertEquals($salon->id, $master->salon->id);
    }
    
    /**
     * Тест отношения мастера к услугам.
     */
    public function test_master_services_relationship(): void
    {
        $master = Master::factory()->create();
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();
        
        $master->services()->attach($service1->id, [
            'custom_price' => 1500,
            'custom_duration' => 60
        ]);
        
        $master->services()->attach($service2->id, [
            'custom_price' => 2000,
            'custom_duration' => 90
        ]);
        
        $this->assertCount(2, $master->services);
        $this->assertTrue($master->services->contains($service1));
        $this->assertTrue($master->services->contains($service2));
        
        // Проверяем pivot данные
        $pivotData = $master->services->find($service1->id)->pivot;
        $this->assertEquals(1500, $pivotData->custom_price);
        $this->assertEquals(60, $pivotData->custom_duration);
    }
    
    /**
     * Тест отношения мастера к расписанию.
     */
    public function test_master_schedules_relationship(): void
    {
        $master = Master::factory()->create();
        $schedule1 = Schedule::factory()->create(['master_id' => $master->id]);
        $schedule2 = Schedule::factory()->create(['master_id' => $master->id]);
        
        $this->assertCount(2, $master->schedules);
        $this->assertTrue($master->schedules->contains($schedule1));
        $this->assertTrue($master->schedules->contains($schedule2));
    }
    
    /**
     * Тест отношения мастера к записям.
     */
    public function test_master_appointments_relationship(): void
    {
        $master = Master::factory()->create();
        $appointment1 = Appointment::factory()->create(['master_id' => $master->id]);
        $appointment2 = Appointment::factory()->create(['master_id' => $master->id]);
        
        $this->assertCount(2, $master->appointments);
        $this->assertTrue($master->appointments->contains($appointment1));
        $this->assertTrue($master->appointments->contains($appointment2));
    }
    
    /**
     * Тест scope метода для активных мастеров.
     */
    public function test_active_scope(): void
    {
        $activeMaster = Master::factory()->create(['is_active' => true]);
        $inactiveMaster = Master::factory()->create(['is_active' => false]);
        
        $activeMasters = Master::active()->get();
        
        $this->assertTrue($activeMasters->contains($activeMaster));
        $this->assertFalse($activeMasters->contains($inactiveMaster));
    }
    
    /**
     * Тест scope метода для мастеров определенного салона.
     */
    public function test_for_salon_scope(): void
    {
        $salon1 = User::factory()->create(['user_type' => 'salon']);
        $salon2 = User::factory()->create(['user_type' => 'salon']);
        
        $master1 = Master::factory()->create(['salon_id' => $salon1->id]);
        $master2 = Master::factory()->create(['salon_id' => $salon1->id]);
        $master3 = Master::factory()->create(['salon_id' => $salon2->id]);
        
        $salon1Masters = Master::forSalon($salon1->id)->get();
        
        $this->assertCount(2, $salon1Masters);
        $this->assertTrue($salon1Masters->contains($master1));
        $this->assertTrue($salon1Masters->contains($master2));
        $this->assertFalse($salon1Masters->contains($master3));
    }
    
    /**
     * Тест получения полного имени мастера.
     */
    public function test_get_full_name_attribute(): void
    {
        $user = User::factory()->create([
            'name' => 'Иванов Иван'
        ]);
        
        $master = Master::factory()->create([
            'user_id' => $user->id,
            'name' => 'Петров Петр'
        ]);
        
        // Должно вернуть имя связанного пользователя
        $this->assertEquals('Иванов Иван', $master->full_name);
        
        // Теперь тестируем мастера без связанного пользователя
        $masterWithoutUser = Master::factory()->create([
            'user_id' => null,
            'name' => 'Сидоров Сидор'
        ]);
        
        // Должно вернуть имя мастера
        $this->assertEquals('Сидоров Сидор', $masterWithoutUser->full_name);
    }
}
