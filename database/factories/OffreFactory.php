<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OffreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titre' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'localisation' => fake()->city(),
            'type' => fake()->randomElement(['CDI', 'CDD', 'stage']),
            'actif' => true,
        ];
    }
}