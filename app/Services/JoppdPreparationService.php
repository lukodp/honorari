<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\JoppdPreparation;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JoppdPreparationService
{
    public function __construct(
        private JoppdXmlService $xmlService,
        private JoppdXmlValidationService $xmlValidator
    ) {}

    /**
     * @return array{organization: ?Organization, calculations: Collection, rows: array<int, array<string, mixed>>, errors: array<int, string>, summary: array<string, int|string>}
     */
    public function inspect(string $paymentDate): array
    {
        $organizations = Organization::query()->where('is_active', true)->get();
        $calculations = Calculation::query()
            ->whereDate('payment_date', $paymentDate)
            ->orderBy('id')
            ->get();
        $errors = [];
        $rows = [];
        $organization = $organizations->count() === 1 ? $organizations->first() : null;

        if ($organizations->count() !== 1) {
            $errors[] = $organizations->isEmpty()
                ? 'Nije odabrana aktivna organizacija.'
                : 'Pronađeno je više aktivnih organizacija. Aktivna mora biti točno jedna.';
        }

        if ($calculations->isEmpty()) {
            $errors[] = 'Za odabrani datum isplate nema spremljenih obračuna.';
        }

        if ($organization && $calculations->contains(
            fn (Calculation $calculation): bool => $calculation->organization_oib !== $organization->oib
        )) {
            $errors[] = 'OIB organizacije u jednom ili više obračuna ne odgovara aktivnoj organizaciji.';
        }

        $duplicateOibs = $calculations->groupBy('referee_oib')->filter(
            fn ($group): bool => $group->count() > 1
        );
        if ($duplicateOibs->isNotEmpty()) {
            $errors[] = 'Skup sadrži duplog primatelja. Svaki primatelj smije imati samo jedan redak.';
        }

        $reportIds = $calculations->pluck('report_id')->unique();
        if ($calculations->isNotEmpty() && ($reportIds->count() !== 1 || ! preg_match('/^\d{5}$/', (string) $reportIds->first()))) {
            $errors[] = 'Obračuni nemaju isti ispravni petoznamenkasti identifikator izvješća.';
        } elseif ($calculations->isNotEmpty()) {
            $date = Carbon::parse($paymentDate);
            $expectedReportId = $date->format('y').str_pad((string) $date->dayOfYear, 3, '0', STR_PAD_LEFT);
            if ((string) $reportIds->first() !== $expectedReportId) {
                $errors[] = 'Identifikator izvješća ne odgovara odabranom datumu isplate.';
            }
        }

        if ($organization) {
            foreach ([
                'street' => 'Ulica organizacije nije unesena.',
                'house_number' => 'Kućni broj organizacije nije unesen.',
                'city' => 'Mjesto organizacije nije uneseno.',
                'email' => 'E-pošta organizacije nije unesena ili nije ispravna.',
                'responsible_person_first_name' => 'Ime odgovorne osobe nije uneseno.',
                'responsible_person_last_name' => 'Prezime odgovorne osobe nije uneseno.',
            ] as $field => $message) {
                if (blank($organization->{$field})) {
                    $errors[] = $message;
                }
            }

            if (filled($organization->email) && ! filter_var($organization->email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'E-pošta organizacije nije unesena ili nije ispravna.';
            }
            if (! preg_match('/^\d{11}$/', (string) $organization->oib)) {
                $errors[] = 'OIB aktivne organizacije mora sadržavati 11 znamenki.';
            }
        }

        foreach ($calculations as $index => $calculation) {
            $localGovernmentCode = $calculation->referee_local_government_code;
            if (blank($localGovernmentCode) && $calculation->referee) {
                $localGovernmentCode = $calculation->referee->localGovernment?->code;
            }

            if (blank($localGovernmentCode) || ! preg_match('/^\d{2,5}$/', (string) $localGovernmentCode)) {
                $errors[] = 'Za primatelja '.trim($calculation->referee_first_name.' '.$calculation->referee_last_name)
                    .' nedostaje valjana šifra općine ili grada.';

                continue;
            }

            if (! preg_match('/^\d{11}$/', (string) $calculation->referee_oib)) {
                $errors[] = 'Obračun sadrži neispravan OIB primatelja.';
            }

            if (blank($calculation->referee_first_name) || blank($calculation->referee_last_name)) {
                $errors[] = 'Obračun sadrži primatelja bez imena ili prezimena.';
            }

            foreach (['gross_amount', 'tax_amount', 'net_amount'] as $amountField) {
                if (! is_numeric($calculation->{$amountField}) || (float) $calculation->{$amountField} < 0) {
                    $errors[] = 'Obračun sadrži neispravan iznos za JOPPD.';
                    break;
                }
            }

            $rows[] = [
                'P1' => (string) ($index + 1),
                'P2' => str_pad((string) $localGovernmentCode, 5, '0', STR_PAD_LEFT),
                'P3' => '00000',
                'P4' => (string) $calculation->referee_oib,
                'P5' => trim($calculation->referee_first_name.' '.$calculation->referee_last_name),
                'P61' => '4001',
                'P62' => '4013',
                'P71' => '0',
                'P72' => '0',
                'P8' => '0',
                'P9' => '0',
                'P10' => '0',
                'P100' => '0',
                'P101' => Carbon::parse($paymentDate)->startOfYear()->toDateString(),
                'P102' => Carbon::parse($paymentDate)->endOfYear()->toDateString(),
                'P11' => $this->money($calculation->gross_amount),
                'P12' => '0.00',
                'P121' => '0.00', 'P122' => '0.00', 'P123' => '0.00',
                'P124' => '0.00', 'P125' => '0.00', 'P126' => '0.00',
                'P127' => '0.00', 'P128' => '0.00', 'P129' => '0.00',
                'P131' => '0.00', 'P132' => '0.00',
                'P133' => $this->money($calculation->gross_amount),
                'P134' => '0.00',
                'P135' => $this->money($calculation->gross_amount),
                'P141' => $this->money($calculation->tax_amount),
                'P142' => '0.00',
                'P151' => '00',
                'P152' => '0.00',
                'P161' => '1',
                'P162' => $this->money($calculation->net_amount),
                'P17' => '0.00',
            ];
        }

        return [
            'organization' => $organization,
            'calculations' => $calculations,
            'rows' => $rows,
            'errors' => array_values(array_unique($errors)),
            'summary' => [
                'people' => $calculations->count(),
                'rows' => $calculations->count(),
                'gross' => $this->money($calculations->sum('gross_amount')),
                'tax' => $this->money($calculations->sum('tax_amount')),
                'net' => $this->money($calculations->sum('net_amount')),
            ],
        ];
    }

    public function prepare(string $paymentDate): JoppdPreparation
    {
        return DB::transaction(function () use ($paymentDate): JoppdPreparation {
            $inspection = $this->inspect($paymentDate);
            if ($inspection['errors'] !== []) {
                throw new \DomainException(implode(' ', $inspection['errors']));
            }

            $organization = $inspection['organization'];
            $calculations = $inspection['calculations'];
            $reportId = (string) $calculations->first()->report_id;
            $businessSnapshot = $this->businessSnapshot($inspection, $paymentDate, $reportId);
            $fingerprint = hash('sha256', json_encode($businessSnapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            $snapshot = $businessSnapshot + [
                'metadata' => [
                    'uuid' => (string) Str::uuid(),
                    'created_at' => now()->format('Y-m-d\TH:i:s.u'),
                ],
            ];

            $validation = $this->validateSnapshot($snapshot);
            if (! $validation['valid']) {
                throw new \DomainException(implode(' ', $validation['errors']));
            }

            $current = JoppdPreparation::query()
                ->where('organization_id', $organization->id)
                ->whereDate('payment_date', $paymentDate)
                ->where('status', 'ready')
                ->lockForUpdate()
                ->orderByDesc('revision')
                ->first();

            if ($current && hash_equals($current->fingerprint, $fingerprint)) {
                return $current;
            }

            $revision = ((int) JoppdPreparation::query()
                ->where('organization_id', $organization->id)
                ->whereDate('payment_date', $paymentDate)
                ->max('revision')) + 1;

            JoppdPreparation::query()
                ->where('organization_id', $organization->id)
                ->whereDate('payment_date', $paymentDate)
                ->where('status', 'ready')
                ->update(['status' => 'superseded']);

            $preparation = JoppdPreparation::create([
                'organization_id' => $organization->id,
                'organization_oib' => $organization->oib,
                'payment_date' => $paymentDate,
                'revision' => $revision,
                'status' => 'ready',
                'fingerprint' => $fingerprint,
                'snapshot' => $snapshot,
            ]);
            $preparation->calculations()->attach($calculations->modelKeys());

            return $preparation;
        });
    }

    /**
     * @param  array<string, mixed>  $inspection
     * @return array{valid: bool, errors: array<int, string>, warnings: array<int, string>}
     */
    public function validateInspection(array $inspection, string $paymentDate): array
    {
        if ($inspection['errors'] !== []) {
            return ['valid' => false, 'errors' => [], 'warnings' => []];
        }

        $reportId = (string) $inspection['calculations']->first()->report_id;
        $snapshot = $this->businessSnapshot($inspection, $paymentDate, $reportId) + [
            'metadata' => [
                'uuid' => (string) Str::uuid(),
                'created_at' => now()->format('Y-m-d\TH:i:s.u'),
            ],
        ];

        return $this->validateSnapshot($snapshot);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array{valid: bool, errors: array<int, string>, warnings: array<int, string>}
     */
    private function validateSnapshot(array $snapshot): array
    {
        $preparation = new JoppdPreparation;
        $preparation->setAttribute('snapshot', $snapshot);

        return $this->xmlValidator->validate($this->xmlService->generate($preparation));
    }

    /**
     * @param  array<string, mixed>  $inspection
     * @return array<string, mixed>
     */
    private function businessSnapshot(array $inspection, string $paymentDate, string $reportId): array
    {
        $organization = $inspection['organization'];
        $calculations = $inspection['calculations'];

        return [
            'header' => [
                'payment_date' => $paymentDate,
                'report_id' => $reportId,
                'organization' => [
                    'name' => $organization->name,
                    'street' => $organization->street,
                    'house_number' => $organization->house_number,
                    'city' => $organization->city,
                    'email' => $organization->email,
                    'oib' => $organization->oib,
                    'responsible_person_first_name' => $organization->responsible_person_first_name,
                    'responsible_person_last_name' => $organization->responsible_person_last_name,
                ],
            ],
            'rows' => $inspection['rows'],
            'summary' => $inspection['summary'],
            'calculation_ids' => $calculations->modelKeys(),
        ];
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
