<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $tags = ['web', 'mobile', 'desktop', 'admin', 'public', 'email'];

        return [
            'name' => $tags[array_rand($tags)] . '_' . $this->faker->randomNumber(3),
        ];
    }
}
