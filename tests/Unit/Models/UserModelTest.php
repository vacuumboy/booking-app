<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Master;
use App\Models\Client;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания пользователя и проверка атрибутов.
     */
    public function test_user_can_be_created_with_attributes(): void
    {
        $userData = [
            'name' => 'Тест Пользователь',
            'email' => 'test@example.com',
            'phone' => '+7 999 123 45 67',
            'password' => bcrypt('password'),
            'user_type' => 'master',
            'avatar' => null,
            'bio' => 'Тестовая биография',
            'address' => 'г. Москва, ул. Тестовая, д. 1',
            'is_active' => true,
            'is_verified' => true,
            'salon_name' => null,
            'working_hours' => ['mon' => ['10:00-18:00'], 'tue' => ['10:00-18:00']],
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'name' => 'Тест Пользователь',
            'email' => 'test@example.com',
            'phone' => '+7 999 123 45 67',
            'user_type' => 'master',
            'is_active' => 1,
            'is_verified' => 1,
        ]);

        $this->assertEquals('Тест Пользователь', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('master', $user->user_type);
        $this->assertIsArray($user->working_hours);
    }

    /**
     * Тест проверки типа пользователя "мастер".
     */
    public function test_user_is_master(): void
    {
        $user = User::factory()->create([
            'user_type' => 'master'
        ]);

        $this->assertTrue($user->isMaster());
        $this->assertFalse($user->isSalon());
        $this->assertFalse($user->isClient());
    }

    /**
     * Тест проверки типа пользователя "салон".
     */
    public function test_user_is_salon(): void
    {
        $user = User::factory()->create([
            'user_type' => 'salon'
        ]);

        $this->assertTrue($user->isSalon());
        $this->assertFalse($user->isMaster());
        $this->assertFalse($user->isClient());
    }

    /**
     * Тест проверки типа пользователя "клиент".
     */
    public function test_user_is_client(): void
    {
        $user = User::factory()->create([
            'user_type' => 'client'
        ]);

        $this->assertTrue($user->isClient());
        $this->assertFalse($user->isMaster());
        $this->assertFalse($user->isSalon());
    }

    /**
     * Тест отношения пользователя к профилю мастера.
     */
    public function test_user_master_profile_relationship(): void
    {
        $user = User::factory()->create([
            'user_type' => 'master'
        ]);

        $master = Master::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertEquals($master->id, $user->masterProfile->id);
    }

    /**
     * Тест отношения пользователя-салона к мастерам салона.
     */
    public function test_salon_masters_relationship(): void
    {
        $salon = User::factory()->create([
            'user_type' => 'salon'
        ]);

        $master1 = Master::factory()->create([
            'salon_id' => $salon->id
        ]);

        $master2 = Master::factory()->create([
            'salon_id' => $salon->id
        ]);

        $this->assertCount(2, $salon->salonMasters);
        $this->assertTrue($salon->salonMasters->contains($master1));
        $this->assertTrue($salon->salonMasters->contains($master2));
    }

    /**
     * Тест отношения пользователя к клиентам.
     */
    public function test_user_clients_relationship(): void
    {
        $user = User::factory()->create();

        $client1 = Client::factory()->create([
            'user_id' => $user->id
        ]);

        $client2 = Client::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertCount(2, $user->clients);
        $this->assertTrue($user->clients->contains($client1));
        $this->assertTrue($user->clients->contains($client2));
    }

    /**
     * Тест отношения пользователя-салона к услугам.
     */
    public function test_salon_services_relationship(): void
    {
        $salon = User::factory()->create([
            'user_type' => 'salon'
        ]);

        $service1 = Service::factory()->create([
            'user_id' => $salon->id
        ]);

        $service2 = Service::factory()->create([
            'user_id' => $salon->id
        ]);

        $this->assertCount(2, $salon->services);
        $this->assertTrue($salon->services->contains($service1));
        $this->assertTrue($salon->services->contains($service2));
    }

    /**
     * Тест получения URL фотографии пользователя.
     */
    public function test_get_photo_url_attribute(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'photo_path' => 'users/test.jpg'
        ]);

        $this->assertEquals(asset('storage/users/test.jpg'), $user->photo_url);

        $user->photo_path = null;
        $user->save();
        
        $this->assertStringContainsString('ui-avatars.com', $user->photo_url);
        $this->assertStringContainsString('Test+User', $user->photo_url);
    }
}
