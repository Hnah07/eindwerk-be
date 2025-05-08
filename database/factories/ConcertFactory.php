<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Concert>
 */
class ConcertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $concertTypes = ['concert', 'festival', 'dj set', 'club show', 'theater show'];
        $artists = [
            'Slipknot',
            'Korn',
            'System of a Down',
            'Disturbed',
            'Linkin Park',
            'Avenged Sevenfold',
            'Megadeth',
            'Lorna Shore',
            'Bring Me The Horizon',
            'Paleface Swiss',
            'Uriah Heep'
        ];

        $year = $this->faker->numberBetween(2024, 2025);

        return [
            'name' => $this->faker->randomElement($artists) . ' - ' . $this->faker->words(3, true),
            'description' => $this->faker->paragraph(3),
            'year' => $year,
            'type' => $this->faker->randomElement($concertTypes),
        ];
    }
}
