<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания клиента и проверка атрибутов.
     */
    public function test_client_can_be_created_with_attributes(): void
    {
        $user = User::factory()->create();
        
        $clientData = [
            'user_id' => $user->id,
            'name' => 'Клиент Тест',
            'phone' => '+7 999 123 00 00',
            'email' => 'client@example.com',
            'birth_date' => '1990-01-15',
            'notes' => 'Примечание к клиенту',
            'address' => 'г. Москва, ул. Клиентская, д. 5',
            'preferred_communication' => 'phone',
        ];
        
        $client = Client::create($clientData);
        
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Клиент Тест',
            'phone' => '+7 999 123 00 00',
            'notes' => 'Примечание к клиенту',
            'email' => 'client@example.com',
        ]);
        
        $this->assertEquals('Клиент Тест', $client->name);
        $this->assertEquals('+7 999 123 00 00', $client->phone);
        $this->assertEquals('client@example.com', $client->email);
        $this->assertEquals('phone', $client->preferred_communication);
        $this->assertEquals(Carbon::parse('1990-01-15')->startOfDay(), $client->birth_date);
    }
    
    /**
     * Тест отношения клиента к пользователю.
     */
    public function test_client_user_relationship(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        $this->assertEquals($user->id, $client->user->id);
    }
    
    /**
     * Тест отношения клиента к записям.
     */
    public function test_client_appointments_relationship(): void
    {
        $client = Client::factory()->create();
        $appointment1 = Appointment::factory()->create(['client_id' => $client->id]);
        $appointment2 = Appointment::factory()->create(['client_id' => $client->id]);
        
        $this->assertCount(2, $client->appointments);
        $this->assertTrue($client->appointments->contains($appointment1));
        $this->assertTrue($client->appointments->contains($appointment2));
    }
    
    /**
     * Тест scope метода для клиентов определенного пользователя.
     */
    public function test_for_user_scope(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $client2 = Client::factory()->create(['user_id' => $user1->id]);
        $client3 = Client::factory()->create(['user_id' => $user2->id]);
        
        $user1Clients = Client::forUser($user1->id)->get();
        
        $this->assertCount(2, $user1Clients);
        $this->assertTrue($user1Clients->contains($client1));
        $this->assertTrue($user1Clients->contains($client2));
        $this->assertFalse($user1Clients->contains($client3));
    }
    
    /**
     * Тест метода получения последних записей клиента.
     */
    public function test_recent_appointments(): void
    {
        $client = Client::factory()->create();
        $master = User::factory()->create(['user_type' => 'master']);
        $service = User::factory()->create(['user_type' => 'service']);
        
        // Создаем 3 записи с разными датами
        $appointment1 = Appointment::factory()->create([
            'client_id' => $client->id,
            'start_time' => Carbon::now()->subDays(5),
            'end_time' => Carbon::now()->subDays(5)->addHour(),
        ]);
        
        $appointment2 = Appointment::factory()->create([
            'client_id' => $client->id,
            'start_time' => Carbon::now()->subDays(10),
            'end_time' => Carbon::now()->subDays(10)->addHour(),
        ]);
        
        $appointment3 = Appointment::factory()->create([
            'client_id' => $client->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHour(),
        ]);
        
        // Получаем 2 последние записи
        $recentAppointments = $client->recentAppointments(2);
        
        $this->assertCount(2, $recentAppointments);
        // Проверяем, что записи отсортированы по дате (сначала новые)
        $this->assertEquals($appointment3->id, $recentAppointments[0]->id);
        $this->assertEquals($appointment1->id, $recentAppointments[1]->id);
    }
    
    /**
     * Тест метода получения предстоящих записей клиента.
     */
    public function test_upcoming_appointments(): void
    {
        $client = Client::factory()->create();
        
        // Прошедшая запись
        $pastAppointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHour(),
            'status' => 'completed'
        ]);
        
        // Отмененная предстоящая запись
        $cancelledAppointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
            'status' => 'cancelled'
        ]);
        
        // Активная предстоящая запись
        $upcomingAppointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHour(),
            'status' => 'confirmed'
        ]);
        
        $upcomingAppointments = $client->upcomingAppointments();
        
        $this->assertCount(1, $upcomingAppointments);
        $this->assertEquals($upcomingAppointment->id, $upcomingAppointments[0]->id);
    }
}
