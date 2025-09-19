<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\TemplateProcessor;
use App\Models\ReminderTemplate;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\Master;
use App\Models\User;
use Carbon\Carbon;

class TemplateProcessorTest extends TestCase
{
    use RefreshDatabase;

    private TemplateProcessor $templateProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templateProcessor = new TemplateProcessor();
    }

    public function test_can_get_available_placeholders()
    {
        $placeholders = TemplateProcessor::getAvailablePlaceholders();
        
        $this->assertIsArray($placeholders);
        $this->assertArrayHasKey('{client_name}', $placeholders);
        $this->assertArrayHasKey('{service_name}', $placeholders);
        $this->assertArrayHasKey('{date_time}', $placeholders);
        $this->assertArrayHasKey('{price}', $placeholders);
        $this->assertArrayHasKey('{studio_address}', $placeholders);
    }

    public function test_can_generate_preview_with_test_data()
    {
        $templateBody = "Привет, {client_name}! Напоминаем о записи на {service_name} {date_time}. Стоимость: {price}.";
        
        $preview = $this->templateProcessor->generatePreview($templateBody);
        
        $this->assertStringContainsString('Николь', $preview);
        $this->assertStringContainsString('Маникюр', $preview);
        $this->assertStringContainsString('15.07.2025 в 14:30', $preview);
        $this->assertStringContainsString('25 euro', $preview);
        $this->assertStringNotContainsString('{client_name}', $preview);
    }

    public function test_can_validate_template_with_supported_placeholders()
    {
        $templateBody = "Привет, {client_name}! Запись на {service_name} {date_time}. Цена: {price}.";
        
        $errors = $this->templateProcessor->validateTemplate($templateBody);
        
        $this->assertEmpty($errors);
    }

    public function test_can_detect_unsupported_placeholders()
    {
        $templateBody = "Привет, {client_name}! Запись на {service_name} {date_time}. Погода: {weather}.";
        
        $errors = $this->templateProcessor->validateTemplate($templateBody);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('{weather}', $errors[0]);
    }

    public function test_can_process_template_with_real_appointment_data()
    {
        // Создаем тестовые данные
        $user = User::factory()->create([
            'name' => 'Мария Иванова',
            'phone' => '+7 (900) 123-45-67',
            'address' => 'ул. Красная, 15',
            'user_type' => 'master'
        ]);

        $master = Master::create([
            'name' => 'Мария Иванова',
            'phone' => '+7 (900) 123-45-67',
            'email' => 'maria@example.com',
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $client = Client::create([
            'name' => 'Николь',
            'phone' => '+7 (900) 987-65-43',
            'user_id' => $user->id,
        ]);

        $service = Service::create([
            'name' => 'Маникюр',
            'price' => 25,
            'duration' => 90,
            'user_id' => $user->id,
        ]);

        $appointment = Appointment::create([
            'client_id' => $client->id,
            'master_id' => $master->id,
            'service_id' => $service->id,
            'start_time' => Carbon::create(2025, 7, 15, 14, 30),
            'end_time' => Carbon::create(2025, 7, 15, 16, 0),
            'price' => 25,
            'created_by_user_id' => $user->id,
        ]);

        $template = ReminderTemplate::create([
            'name' => 'Тестовый шаблон',
            'body' => 'Привет, {client_name}! Напоминаем о записи на {service_name} {date_time}. Стоимость: {price}. Адрес: {studio_address}.',
            'user_id' => $user->id,
        ]);

        // Обрабатываем шаблон
        $processedText = $this->templateProcessor->processTemplate($template, $appointment);

        // Проверяем результат
        $this->assertStringContainsString('Николь', $processedText);
        $this->assertStringContainsString('Маникюр', $processedText);
        $this->assertStringContainsString('15.07.2025 в 14:30', $processedText);
        $this->assertStringContainsString('25 euro', $processedText);
        $this->assertStringContainsString('ул. Красная, 15', $processedText);
        $this->assertStringNotContainsString('{client_name}', $processedText);
    }

    public function test_handles_missing_data_gracefully()
    {
        // Создаем минимальный appointment без всех связей
        $appointment = new Appointment([
            'price' => null,
            'start_time' => null,
            'end_time' => null,
        ]);

        $template = new ReminderTemplate([
            'body' => 'Привет, {client_name}! Запись на {service_name} {date_time}. Цена: {price}.',
        ]);

        $processedText = $this->templateProcessor->processTemplate($template, $appointment);

        $this->assertStringContainsString('Не указано', $processedText);
        $this->assertStringNotContainsString('{client_name}', $processedText);
    }

    public function test_formats_price_correctly()
    {
        $reflection = new \ReflectionClass($this->templateProcessor);
        $method = $reflection->getMethod('formatPrice');
        $method->setAccessible(true);

        $this->assertEquals('1 000 руб.', $method->invoke($this->templateProcessor, 1000));
        $this->assertEquals('2 500 руб.', $method->invoke($this->templateProcessor, 2500));
        $this->assertEquals('10 000 руб.', $method->invoke($this->templateProcessor, 10000));
    }

    public function test_formats_duration_correctly()
    {
        $reflection = new \ReflectionClass($this->templateProcessor);
        $method = $reflection->getMethod('formatDuration');
        $method->setAccessible(true);

        // Создаем appointment с разными длительностями
        $appointment1 = new Appointment([
            'start_time' => Carbon::create(2025, 7, 15, 14, 0),
            'end_time' => Carbon::create(2025, 7, 15, 14, 30),
        ]);

        $appointment2 = new Appointment([
            'start_time' => Carbon::create(2025, 7, 15, 14, 0),
            'end_time' => Carbon::create(2025, 7, 15, 15, 30),
        ]);

        $appointment3 = new Appointment([
            'start_time' => Carbon::create(2025, 7, 15, 14, 0),
            'end_time' => Carbon::create(2025, 7, 15, 16, 0),
        ]);

        $this->assertEquals('30 мин.', $method->invoke($this->templateProcessor, $appointment1));
        $this->assertEquals('1 ч. 30 мин.', $method->invoke($this->templateProcessor, $appointment2));
        $this->assertEquals('2 ч.', $method->invoke($this->templateProcessor, $appointment3));
    }
}
