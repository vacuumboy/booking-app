<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReminderTemplate>
 */
class ReminderTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'body' => fake()->paragraph(),
            'is_active' => fake()->boolean(80), // 80% вероятность быть активным
        ];
    }

    /**
     * Indicate that the template is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a template with placeholders.
     */
    public function withPlaceholders(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => 'Привет, {client_name}! Напоминаем о записи на {service_name} {date_time}. Стоимость: {price}. Адрес: {studio_address}.',
        ]);
    }
}
