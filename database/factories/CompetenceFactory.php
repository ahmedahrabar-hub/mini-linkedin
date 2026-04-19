<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompetenceFactory extends Factory
{
    public function definition(): array
    {
        $competences = [
            'PHP', 'Laravel', 'Python', 'JavaScript', 'React',
            'Vue.js', 'MySQL', 'Docker', 'Git', 'Node.js',
            'Java', 'Spring Boot', 'Angular', 'TypeScript', 'MongoDB'
        ];

        $categories = [
            'Backend', 'Frontend', 'Base de données', 'DevOps', 'Mobile'
        ];

        return [
            'nom' => fake()->randomElement($competences),
            'categorie' => fake()->randomElement($categories),
        ];
    }
}