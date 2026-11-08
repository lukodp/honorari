<?php

namespace App\Console\Commands;

use App\Models\LocalGovernment;
use App\Models\TaxRate;
use App\Services\JlsWorkbookReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImportInitialJls extends Command
{
    protected $signature = 'jls:import-initial {file : Lokalna službena XLSX tablica za 2026.}
                            {--dry-run : Samo provjera i pregled, bez upisa}';

    protected $description = 'Početni transakcijski uvoz 556 JLS-a i nižih poreznih stopa u prazne tablice';

    public function handle(JlsWorkbookReader $reader): int
    {
        try {
            $this->requireEmptyTables();
            $rows = $reader->read((string) $this->argument('file'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Provjereno: 556 JLS zapisa i 556 poreznih stopa.');
        $this->line('Ciljna baza: '.DB::connection()->getDatabaseName());
        $this->table(['Šifra', 'Naziv', 'Stopa (%)', 'valid_from (H)'], array_map(
            fn (array $row): array => [$row['code'], $row['name'], $row['lower_rate'], $row['valid_from']],
            array_slice($rows, 0, 3)
        ));
        $this->warn('H je datum stupanja odluke na snagu; zadržana je pretpostavka postojećeg seedera za valid_from.');
        $this->warn('Početni skup nije jamstvo aktualnosti stopa. Prije uporabe provjerite odluke i uvjete izvora.');
        if ($this->option('dry-run')) {
            $this->info('Provjera završena bez upisa.');

            return self::SUCCESS;
        }
        if (! $this->input->isInteractive() || ! $this->confirm('Upisati 556 JLS-a i stopa u prazne tablice?', false)) {
            $this->warn('Uvoz otkazan; nema upisa.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($rows): void {
                $this->requireEmptyTables();
                foreach ($rows as $row) {
                    $government = LocalGovernment::create([
                        'code' => $row['code'],
                        'name' => $row['name'],
                    ]);
                    TaxRate::create([
                        'local_government_id' => $government->id,
                        'city' => $row['name'],
                        'iban' => $row['iban'],
                        'rate' => $row['lower_rate'],
                        'coefficient' => number_format(1 / (1 - ((float) $row['lower_rate'] / 100)), 6, '.', ''),
                        'valid_from' => $row['valid_from'],
                        'valid_until' => null,
                    ]);
                }
            });
        } catch (Throwable) {
            $this->error('Uvoz nije uspio; transakcija je poništena. Provjerite dostupnost baze i njezinu shemu.');

            return self::FAILURE;
        }
        $this->info('Uvezeno: 556 JLS zapisa i 556 poreznih stopa.');

        return self::SUCCESS;
    }

    private function requireEmptyTables(): void
    {
        if (LocalGovernment::query()->exists() || TaxRate::query()->exists()) {
            throw new RuntimeException('Uvoz je dopušten samo kada su obje tablice local_governments i tax_rates prazne.');
        }
    }
}
