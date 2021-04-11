<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
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
}
