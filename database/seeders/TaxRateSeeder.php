<?php

namespace Database\Seeders;

use App\Models\LocalGovernment;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;
use RuntimeException;

class TaxRateSeeder extends Seeder
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

        $header = fgetcsv($handle, 0, ';');
        if ($header !== false && isset($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
}

        if ($header === false ||
            !in_array('code', $header, true) ||
            !in_array('iban', $header, true) ||
            !in_array('lower_rate', $header, true) ||
            !in_array('valid_from', $header, true)) {
            fclose($handle);

            throw new RuntimeException(
                'CSV datoteka nema očekivane stupce.'
            );
        }

        $columns = array_flip($header);
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) !== count($header)) {
                fclose($handle);

                throw new RuntimeException(
                    'CSV redak nema očekivani broj stupaca.'
                );
            }

            $code = trim($row[$columns['code']]);
            $iban = strtoupper(
                preg_replace('/\s+/', '', $row[$columns['iban']])
            );
            $rate = (float) $row[$columns['lower_rate']];
            $validFrom = trim($row[$columns['valid_from']]);

            $localGovernment = LocalGovernment::where('code', $code)->first();

            if (!$localGovernment) {
                fclose($handle);

                throw new RuntimeException(
                    "Nije pronađena jedinica lokalne samouprave sa šifrom {$code}."
                );
            }

            $coefficient = number_format(
                1 / (1 - ($rate / 100)),
                6,
                '.',
                ''
            );

            TaxRate::updateOrCreate(
                [
                    'local_government_id' => $localGovernment->id,
                    'valid_from' => $validFrom,
                ],
                [
                    'city' => $localGovernment->name,
                    'iban' => $iban,
                    'rate' => number_format($rate, 2, '.', ''),
                    'coefficient' => $coefficient,
                    'valid_until' => null,
                ]
            );

            $count++;
        }

        fclose($handle);

        $this->command?->info(
            "Obrađeno poreznih stopa: {$count}"
        );
    }
}