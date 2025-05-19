<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Concert;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConcertOccurrenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all concerts and locations
        $concerts = Concert::all();
        $locations = Location::all();

        // If we don't have any concerts or locations, create some first
        if ($concerts->isEmpty()) {
            $concerts = Concert::factory()->count(5)->create();
        }

        if ($locations->isEmpty()) {
            $locations = Location::factory()->count(3)->create();
        }

        // Create concert occurrences
        foreach ($concerts as $concert) {
            // For each concert, create 1-3 occurrences at different locations
            $numberOfOccurrences = rand(1, 3);

            // Get random locations for this concert
            $randomLocations = $locations->random($numberOfOccurrences);

            foreach ($randomLocations as $location) {
                // Generate a random date between now and 1 year from now
                $date = Carbon::now()->addDays(rand(1, 365));

                // Insert directly into the pivot table
                DB::table('concert_occurrences')->insert([
                    'concert_id' => $concert->id,
                    'location_id' => $location->id,
                    'date' => $date,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
