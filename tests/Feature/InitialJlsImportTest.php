<?php

namespace Tests\Feature;

use App\Models\LocalGovernment;
use App\Models\Organization;
use App\Models\Referee;
use App\Models\TaxRate;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class InitialJlsImportTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_imports_556_records_preserving_codes_iban_percentages_and_existing_organizations(): void
    {
        $organization = Organization::create([
            'name' => 'Sintetička organizacija', 'street' => 'Testna ulica', 'house_number' => '1',
            'postal_code' => '10000', 'city' => 'Testograd', 'oib' => '00000000010',
            'iban' => 'HR3100000000000000001', 'is_active' => true,
        ]);
        $before = $organization->fresh()->getRawOriginal();
        $path = $this->workbook([1 => ['B' => ['value' => '00002', 'type' => 'inlineStr']], 2 => ['F' => ['value' => '0.205']]]);

        $this->artisan('jls:import-initial', ['file' => $path])
            ->expectsOutputToContain('Provjereno: 556 JLS zapisa')
            ->expectsConfirmation('Upisati 556 JLS-a i stopa u prazne tablice?', 'yes')
            ->expectsOutputToContain('Uvezeno: 556 JLS zapisa i 556 poreznih stopa.')
            ->assertSuccessful();

        $this->assertDatabaseCount('local_governments', 556);
        $this->assertDatabaseCount('tax_rates', 556);
        $first = LocalGovernment::where('code', '00001')->firstOrFail();
        $this->assertDatabaseHas('local_governments', ['code' => '00002']);
        $rate = $first->taxRates()->firstOrFail();
        $this->assertSame('HR3100000000000000001', $rate->iban);
        $this->assertSame('20.00', $rate->rate);
        $this->assertSame('1.250000', $rate->coefficient);
        $this->assertSame('2024-01-01', $rate->valid_from->format('Y-m-d'));
        $this->assertNull($rate->valid_until);
        $this->assertSame('20.50', LocalGovernment::where('code', '00003')->firstOrFail()->taxRates()->firstOrFail()->rate);
        $this->assertSame($before, $organization->fresh()->getRawOriginal());
        $this->assertDatabaseCount('referees', 0);
        $this->assertDatabaseCount('calculations', 0);
    }

    public function test_dry_run_validates_without_writing(): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook(), '--dry-run' => true])
            ->expectsOutputToContain('Provjera završena bez upisa.')
            ->assertSuccessful();

        $this->assertDatabaseCount('local_governments', 0);
        $this->assertDatabaseCount('tax_rates', 0);
    }

    public function test_non_interactive_import_does_not_write_without_confirmation(): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook(), '--no-interaction' => true])
            ->expectsOutputToContain('Uvoz otkazan')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
        $this->assertDatabaseCount('tax_rates', 0);
    }

    public function test_declining_confirmation_leaves_tables_empty(): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook()])
            ->expectsConfirmation('Upisati 556 JLS-a i stopa u prazne tablice?', 'no')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
        $this->assertDatabaseCount('tax_rates', 0);
    }

    #[DataProvider('invalidRows')]
    public function test_rejects_invalid_rows_before_any_write(array $changes, string $message): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook($changes)])
            ->expectsOutputToContain($message)
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
        $this->assertDatabaseCount('tax_rates', 0);
    }

    public static function invalidRows(): array
    {
        return [
            'duplicate including different leading zeros' => [[1 => ['B' => ['value' => '1']]], 'duplicirana šifra'],
            'IBAN checksum' => [[0 => ['D' => ['value' => 'HR3200000000000000001']]], 'neispravan IBAN'],
            'invalid calendar date' => [[0 => ['H' => ['value' => '31.2.2026.']]], 'neispravan datum'],
            'percent stored as whole number' => [[0 => ['F' => ['value' => '20']]], 'stopa nije podržana'],
            'unsupported rate precision' => [[0 => ['F' => ['value' => '0.201234']]], 'stopa nije podržana'],
            'missing field' => [[0 => ['H' => ['value' => '']]], 'nedostaje obvezno polje H'],
            'too many code digits' => [[0 => ['B' => ['value' => '123456']]], 'neispravna šifra'],
            'unexpected numeric code format' => [[0 => ['B' => ['value' => '1', 'style' => 2]]], 'neočekivan format šifre'],
            'non numeric code' => [[0 => ['B' => ['value' => 'ABC', 'type' => 'inlineStr']]], 'neispravna šifra'],
            'invalid name' => [[0 => ['C' => ['value' => '<test>']]], 'neispravan naziv'],
            'unexpected column' => [[0 => ['I' => ['value' => 'extra']]], 'Neočekivani stupci'],
            'formula with cached value' => [[0 => ['F' => ['value' => '0.2', 'formula' => '1/5']]], 'formule'],
            'rate without percent format' => [[0 => ['F' => ['value' => '0.2', 'style' => 0]]], 'Excel postotnom formatu'],
        ];
    }

    public function test_rejects_incomplete_workbook(): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook(count: 555)])
            ->expectsOutputToContain('Očekuje se točno 556')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
        $this->assertDatabaseCount('tax_rates', 0);
    }

    public function test_rejects_unexpected_headers(): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook(heading: 'Primjena')])
            ->expectsOutputToContain('Neočekivano XLSX zaglavlje')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
    }

    public function test_rejects_external_relationships(): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook(external: true)])
            ->expectsOutputToContain('Vanjske poveznice')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
    }

    public function test_rejects_xml_entities(): void
    {
        $this->artisan('jls:import-initial', ['file' => $this->workbook(doctype: true)])
            ->expectsOutputToContain('nesiguran XML')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
    }

    public function test_rejects_import_when_local_governments_already_exist(): void
    {
        LocalGovernment::create(['code' => '99999', 'name' => 'Postojeći Testograd']);

        $this->artisan('jls:import-initial', ['file' => $this->workbook()])
            ->expectsOutputToContain('obje tablice')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 1);
        $this->assertDatabaseCount('tax_rates', 0);
    }

    public function test_rejects_import_when_only_tax_rates_exist(): void
    {
        TaxRate::create([
            'city' => 'Testograd', 'iban' => 'HR3100000000000000001', 'rate' => '20',
            'coefficient' => '1.250000', 'valid_from' => '2026-01-01',
        ]);

        $this->artisan('jls:import-initial', ['file' => $this->workbook()])
            ->expectsOutputToContain('obje tablice')
            ->assertFailed();

        $this->assertDatabaseCount('local_governments', 0);
        $this->assertDatabaseCount('tax_rates', 1);
    }

    public function test_rolls_back_all_rows_if_a_write_fails_mid_import(): void
    {
        $dispatcher = TaxRate::getEventDispatcher();
        TaxRate::setEventDispatcher(clone $dispatcher);
        $created = 0;
        TaxRate::creating(function () use (&$created): void {
            if (++$created === 2) {
                throw new RuntimeException('Synthetic write failure');
            }
        });

        try {
            $this->artisan('jls:import-initial', ['file' => $this->workbook()])
                ->expectsConfirmation('Upisati 556 JLS-a i stopa u prazne tablice?', 'yes')
                ->expectsOutputToContain('transakcija je poništena')
                ->assertFailed();

            $this->assertSame(2, $created);
            $this->assertDatabaseCount('local_governments', 0);
            $this->assertDatabaseCount('tax_rates', 0);
        } finally {
            TaxRate::setEventDispatcher($dispatcher);
        }
    }

    public function test_imported_rate_is_used_by_existing_calculation_preview(): void
    {
        $this->travelTo('2026-10-05');
        $this->artisan('jls:import-initial', ['file' => $this->workbook()])
            ->expectsConfirmation('Upisati 556 JLS-a i stopa u prazne tablice?', 'yes')
            ->assertSuccessful();
        $referee = Referee::create([
            'first_name' => 'Testni', 'last_name' => 'Sudac', 'oib' => '00000000036',
            'address' => 'Testna 1', 'postal_code' => '10000', 'city' => 'Testograd',
            'iban' => 'HR3100000000000000001', 'is_active' => true,
            'local_government_id' => LocalGovernment::where('code', '00001')->firstOrFail()->id,
        ]);

        $this->post(route('calculations.preview'), [
            'referee_id' => $referee->id, 'payment_date' => '2026-10-05', 'net_amount' => '80.00',
        ])->assertOk()->assertViewHas('grossAmount', 100.0)->assertViewHas('taxAmount', 20.0);

        $this->assertDatabaseCount('calculations', 0);
    }

    /**
     * Creates a synthetic workbook in OS temporary storage; no official dataset is bundled.
     *
     * @param  array<int, array<string, array{value: string, type?: string, style?: int, formula?: string}>>  $changes
     */
    private function workbook(array $changes = [], int $count = 556, string $heading = 'Stupanje na snagu ', bool $external = false, bool $doctype = false): string
    {
        $base = tempnam(sys_get_temp_dir(), 'honorari-jls-');
        $path = $base.'.xlsx';
        rename($base, $path);
        $this->temporaryFiles[] = $path;
        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $document = new DOMDocument('1.0', 'UTF-8');
        $sheet = $document->appendChild($document->createElementNS($ns, 'worksheet'));
        $data = $sheet->appendChild($document->createElementNS($ns, 'sheetData'));
        $strings = [];
        $headers = ['Rbr.', 'Šifra grada/općine', 'Ime grada/općine', 'Uplatni račun', 'Broj NN', 'Niža stopa 2026.   ', 'Viša stopa 2026.', $heading];
        $allRows = [array_combine(range('A', 'H'), array_map(fn (string $value): array => ['value' => $value, 'type' => 's'], $headers))];
        for ($i = 0; $i < $count; $i++) {
            $allRows[] = array_replace([
                'A' => ['value' => (string) ($i + 1)],
                'B' => ['value' => (string) ($i + 1), 'style' => 1],
                'C' => ['value' => 'Sintetička JLS '.($i + 1), 'type' => 's'],
                'D' => ['value' => 'HR3100000000000000001', 'type' => 's'],
                'E' => ['value' => 'Testni NN', 'type' => 's'],
                'F' => ['value' => '0.2', 'style' => 2],
                'G' => ['value' => '0.3', 'style' => 2],
                'H' => ['value' => '1.1.2024.', 'type' => 's'],
            ], $this->changedCells($changes[$i] ?? []));
        }
        foreach ($allRows as $index => $cells) {
            $row = $data->appendChild($document->createElementNS($ns, 'row'));
            $row->setAttribute('r', (string) ($index + 1));
            foreach ($cells as $column => $definition) {
                $cell = $row->appendChild($document->createElementNS($ns, 'c'));
                $cell->setAttribute('r', $column.($index + 1));
                $cell->setAttribute('s', (string) ($definition['style'] ?? 0));
                $type = $definition['type'] ?? '';
                if ($type !== '') {
                    $cell->setAttribute('t', $type);
                }
                if (isset($definition['formula'])) {
                    $cell->appendChild($document->createElementNS($ns, 'f', $definition['formula']));
                }
                $value = $definition['value'];
                if ($type === 's') {
                    $strings[] = $value;
                    $value = (string) (count($strings) - 1);
                }
                $parent = $type === 'inlineStr' ? $cell->appendChild($document->createElementNS($ns, 'is')) : $cell;
                $node = $document->createElementNS($ns, $type === 'inlineStr' ? 't' : 'v');
                $node->appendChild($document->createTextNode($value));
                $parent->appendChild($node);
            }
        }
        $shared = new DOMDocument('1.0', 'UTF-8');
        $sst = $shared->appendChild($shared->createElementNS($ns, 'sst'));
        foreach ($strings as $string) {
            $item = $sst->appendChild($shared->createElementNS($ns, 'si'));
            $text = $item->appendChild($shared->createElementNS($ns, 't'));
            $text->appendChild($shared->createTextNode($string));
        }
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('xl/workbook.xml', ($doctype ? '<!DOCTYPE workbook [<!ENTITY x "unsafe">]>' : '').'<workbook xmlns="'.$ns.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Tablica JLS 2026" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"'.($external ? ' TargetMode="External"' : '').'/></Relationships>');
        $zip->addFromString('xl/styles.xml', '<styleSheet xmlns="'.$ns.'"><numFmts count="1"><numFmt numFmtId="164" formatCode="00000"/></numFmts><cellXfs count="3"><xf numFmtId="0"/><xf numFmtId="164"/><xf numFmtId="10"/></cellXfs></styleSheet>');
        $zip->addFromString('xl/sharedStrings.xml', $shared->saveXML());
        $zip->addFromString('xl/worksheets/sheet1.xml', $document->saveXML());
        $zip->close();

        return $path;
    }

    /** @param array<string, array<string, mixed>> $changes
     * @return array<string, array<string, mixed>>
     */
    private function changedCells(array $changes): array
    {
        $cells = [];
        foreach ($changes as $column => $change) {
            $cells[$column] = array_replace(
                in_array($column, ['C', 'D', 'E', 'H', 'I'], true) ? ['type' => 's'] : ($column === 'F' ? ['style' => 2] : ['style' => 1]),
                $change
            );
        }

        return $cells;
    }
}
