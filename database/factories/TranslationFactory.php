<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        $keyPrefixes = [
            'auth', 'validation', 'pagination', 'button',
            'menu', 'error', 'form', 'user', 'dashboard',
        ];

        $locales = ['en', 'fr', 'es', 'de', 'it', 'pt'];

        return [
            'key' => $keyPrefixes[array_rand($keyPrefixes)] . '.' . $this->faker->word() . '_' . $this->faker->randomNumber(4),
            'locale' => $locales[array_rand($locales)],
            'value' => $this->faker->sentence(),
        ];
    }
}
