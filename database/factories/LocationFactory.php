<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'longitude' => $this->faker->longitude(),
            'latitude' => $this->faker->latitude(),
            'street' => $this->faker->streetName(),
            'housenr' => $this->faker->buildingNumber(),
            'zipcode' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'website' => $this->faker->url(),
            'country' => $this->faker->country()
        ];
    }
}
