<?php

namespace Tests\Feature;

use App\Models\Referee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefereeDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_referee_can_be_deleted(): void
    {
        $referee = Referee::create([
            'first_name' => 'Test',
            'last_name' => 'Primatelj',
            'oib' => '00000000036',
            'address' => 'Testna adresa 1',
            'postal_code' => '10000',
            'city' => 'Testograd',
            'iban' => 'HR3100000000000000001',
        ]);

        $response = $this->delete(route('primatelji.destroy', $referee));

        $response->assertRedirect(route('primatelji.index'));
        $this->assertDatabaseMissing('referees', ['id' => $referee->id]);
    }
}
