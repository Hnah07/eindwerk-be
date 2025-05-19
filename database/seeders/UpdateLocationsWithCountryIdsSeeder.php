<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateLocationsWithCountryIdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all country IDs
        $countryIds = DB::table('countries')->pluck('id')->toArray();

        // Get all locations
        $locations = DB::table('locations')->get();

        // Assign country IDs in a round-robin fashion
        foreach ($locations as $index => $location) {
            $countryId = $countryIds[$index % count($countryIds)];

            DB::table('locations')
                ->where('id', $location->id)
                ->update(['country_id' => $countryId]);
        }
    }
}
