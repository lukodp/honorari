<?php

namespace Database\Seeders;

use App\Models\LocalGovernment;
use Illuminate\Database\Seeder;
use RuntimeException;

class LocalGovernmentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/podaci_jls_2026.csv');

        if (!file_exists($path)) {
            throw new RuntimeException(
                'CSV datoteka nije pronađena: ' . $path
            );
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                'CSV datoteku nije moguće otvoriti.'
            );
        }

        // Prvi redak sadrži nazive stupaca.
        fgetcsv($handle, 0, ';');

        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 2) {
                continue;
            }

            $code = trim($row[0]);
            $name = trim($row[1]);

            if ($code === '' || $name === '') {
                continue;
            }

            LocalGovernment::updateOrCreate(
                ['code' => $code],
                ['name' => $name]
            );

            $count++;
        }

        fclose($handle);

        $this->command?->info(
            "Obrađeno zapisa gradova i općina: {$count}"
        );
    }
}