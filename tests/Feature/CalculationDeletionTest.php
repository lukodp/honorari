<?php

namespace Tests\Feature;

use App\Models\Calculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculationDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculation_can_be_deleted(): void
    {
        $calculation = Calculation::create([
            'report_id' => '26001',
            'organization_name' => 'Organizacija',
            'organization_address' => 'Ulica 1',
            'organization_postal_code' => '10000',
            'organization_city' => 'Zagreb',
            'organization_oib' => '00000000036',
            'organization_iban' => 'HR3100000000000000001',
            'referee_first_name' => 'Test',
            'referee_last_name' => 'Primatelj',
            'referee_oib' => '00000000036',
            'referee_address' => 'Testna adresa 2',
            'referee_postal_code' => '10000',
            'referee_city' => 'Testograd',
            'referee_iban' => 'HR3100000000000000001',
            'payment_date' => '2026-01-01',
            'net_amount' => 100.00,
            'gross_amount' => 125.00,
            'tax_rate' => 20.00,
            'coefficient' => 1.250000,
            'tax_amount' => 25.00,
            'tax_city' => 'Zagreb',
            'tax_valid_from' => '2026-01-01',
            'status' => 'draft',
        ]);

        $response = $this->delete(route('calculations.destroy', $calculation));

        $response->assertRedirect(route('calculations.index'));
        $this->assertDatabaseMissing('calculations', ['id' => $calculation->id]);
    }
}
