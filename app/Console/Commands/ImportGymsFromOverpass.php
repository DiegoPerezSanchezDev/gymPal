<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Gym;

class ImportGymsFromOverpass extends Command
{
    protected $signature = 'gyms:import-spain';
    protected $description = 'Importa gimnasios de OpenStreetMap para las principales ciudades de España';

    public function handle()
    {
        $cities = [
            ['name' => 'Madrid', 'lat' => 40.4168, 'lon' => -3.7038],
            ['name' => 'Barcelona', 'lat' => 41.3851, 'lon' => 2.1734],
            ['name' => 'Valencia', 'lat' => 39.4699, 'lon' => -0.3763],
            ['name' => 'Sevilla', 'lat' => 37.3891, 'lon' => -5.9845],
            ['name' => 'Zaragoza', 'lat' => 41.6488, 'lon' => -0.8891],
            ['name' => 'Málaga', 'lat' => 36.7213, 'lon' => -4.4214],
            ['name' => 'Murcia', 'lat' => 37.9922, 'lon' => -1.1307],
            ['name' => 'Palma', 'lat' => 39.5696, 'lon' => 2.6502],
            ['name' => 'Las Palmas', 'lat' => 28.1235, 'lon' => -15.4363],
            ['name' => 'Bilbao', 'lat' => 43.2630, 'lon' => -2.9350],
        ];

        foreach ($cities as $city) {
            $this->info("Importing gyms for {$city['name']}...");
            $this->importCity($city);
            sleep(2); // Rate limiting respect
        }

        $this->info('Import completed successfully!');
    }

    private function importCity($city)
    {
        // Radio de 15km aprox (0.135 grados)
        $lat = $city['lat'];
        $lon = $city['lon'];
        $delta = 0.13; 

        $south = $lat - $delta;
        $north = $lat + $delta;
        $west = $lon - $delta;
        $east = $lon + $delta;

        // Query optimized to catch fitness centers, sports centres, and yoga/crossfit/swimming/calisthenics related nodes/ways
        $query = "
            [out:json][timeout:60];
            (
                nwr[\"leisure\"=\"fitness_centre\"]({$south},{$west},{$north},{$east});
                nwr[\"leisure\"=\"sports_centre\"]({$south},{$west},{$north},{$east});
                nwr[\"leisure\"=\"fitness_station\"]({$south},{$west},{$north},{$east}); // Calistenia y parques
                nwr[\"sport\"~\"yoga|pilates|crossfit|fitness|swimming|calisthenics\"]({$south},{$west},{$north},{$east});
            );
            out center;
        ";

        try {
            // Using raw POST body for Overpass
            $response = Http::timeout(120)
                ->withBody("data=" . urlencode($query), 'application/x-www-form-urlencoded')
                ->post('https://overpass-api.de/api/interpreter');

            if ($response->failed()) {
                $this->error("Failed to fetch data for {$city['name']} - Status: " . $response->status());
                return;
            }

            $data = $response->json();
            if (!isset($data['elements'])) {
                 $this->warn("No elements found for {$city['name']}. Response might be invalid.");
                 return;
            }

            $count = 0;
            foreach ($data['elements'] as $element) {
                // Ensure we have tags
                if (!isset($element['tags'])) {
                    continue;
                }

                $tags = $element['tags'];
                
                // Si no tiene nombre, intentamos construir uno descriptivo
                $name = $tags['name'] ?? $tags['brand'] ?? null;
                $leisure = $tags['leisure'] ?? null;
                $sport = $tags['sport'] ?? null;

                if (!$name) {
                    if ($leisure === 'fitness_station') $name = 'Parque de Calistenia';
                    else if ($sport === 'swimming') $name = 'Piscina Municipal';
                    else continue; // Si no podemos darle un nombre lógico, lo saltamos
                }
                
                // Determine type
                $type = 'gym';
                $lowerName = strtolower($name . ' ' . ($tags['sport'] ?? ''));
                $lowerLeisure = strtolower($tags['leisure'] ?? '');
                
                if (str_contains($lowerName, 'yoga') || str_contains($lowerName, 'pilates')) $type = 'yoga';
                else if (str_contains($lowerName, 'crossfit') || str_contains($lowerName, 'box')) $type = 'crossfit';
                else if (str_contains($lowerName, 'pool') || str_contains($lowerName, 'piscina') || str_contains($lowerName, 'natación') || str_contains($sport, 'swimming')) $type = 'pool';
                else if (str_contains($lowerName, 'park') || str_contains($lowerName, 'calistenia') || $lowerLeisure === 'fitness_station') $type = 'park';

                Gym::updateOrCreate(
                    ['external_id' => (string)$element['id']],
                    [
                        'name' => $name,
                        'latitude' => $element['lat'] ?? $element['center']['lat'],
                        'longitude' => $element['lon'] ?? $element['center']['lon'],
                        'type' => $type,
                        'city' => $city['name'],
                        'address' => trim(($tags['addr:street'] ?? '') . ' ' . ($tags['addr:housenumber'] ?? '')),
                        'website' => $tags['website'] ?? $tags['contact:website'] ?? null,
                        'users_count' => rand(5, 50), // Simulation
                        'meta_data' => [
                            'hours' => $tags['opening_hours'] ?? null,
                            'sport' => $tags['sport'] ?? null,
                            'leisure' => $tags['leisure'] ?? null,
                        ]
                    ]
                );
                $count++;
            }
            $this->info("Imported {$count} locations for {$city['name']}");

        } catch (\Exception $e) {
            $this->error("Error importing {$city['name']}: " . $e->getMessage());
        }
    }
}
