<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $user = User::factory();
        return [
            "title" => $this->faker->sentence,
            "slug" => $this->faker->unique()->slug,
            "content" => $this->faker->paragraphs(asText: true),
            "is_draft" => true,
            "is_private" => true,
            "is_password_protected" => false,
            "password" => null,
            "visible_from" => null,
            "visible_until" => null,
            "created_by_id" => $user,
            "updated_by_id" => $user,
        ];
    }

    public function withoutUser(): PageFactory
    {
        return $this->state([
            'created_by_id' => null,
            'updated_by_id' => null,
        ]);
    }

    public function withFrom(?Carbon $date = null): PageFactory
    {
        return $this->state([
            'visible_from' => $date ?? $this->faker->dateTimeBetween('-1 year'),
        ]);
    }

    public function withUntil(?Carbon $date = null): PageFactory
    {
        return $this->state([
            'visible_until' => $date ?? $this->faker->dateTimeBetween('now', '1 year'),
        ]);
    }

    public function withPassword(?string $password = null): PageFactory
    {
        return $this->state([
            'is_password_protected' => true,
            'password' => $password ?? $this->faker->password,
        ]);
    }

    public function guestable(): PageFactory
    {
        return $this->state([
            "is_draft" => false,
            "is_private" => false,
        ]);
    }
}
