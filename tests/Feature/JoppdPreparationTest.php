<?php

namespace Tests\Feature;

use App\Models\Calculation;
use App\Models\JoppdPreparation;
use App\Models\LocalGovernment;
use App\Models\Organization;
use App\Models\Referee;
use App\Services\JoppdPreparationService;
use App\Services\JoppdXmlService;
use App\Services\JoppdXmlValidationService;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JoppdPreparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_preparation_blocks_when_there_are_no_or_multiple_active_organizations(): void
    {
        $service = app(JoppdPreparationService::class);
        $this->assertStringContainsString('Nije odabrana aktivna organizacija', implode(' ', $service->inspect('2026-10-05')['errors']));

        $this->createOrganization('00000000010');
        $this->createOrganization('00000000028');

        $this->assertStringContainsString('više aktivnih organizacija', implode(' ', $service->inspect('2026-10-05')['errors']));
    }

    public function test_preparation_blocks_when_no_calculations_exist_for_payment_date(): void
    {
        $this->createOrganization('00000000010');

        $inspection = app(JoppdPreparationService::class)->inspect('2026-10-05');

        $this->assertStringContainsString('nema spremljenih obračuna', implode(' ', $inspection['errors']));
    }

    public function test_preparation_blocks_calculations_with_another_organization_oib(): void
    {
        $organization = $this->createOrganization('00000000010');
        $calculation = $this->createCalculation($organization, '00000000036');
        $calculation->update(['organization_oib' => '00000000028']);

        $inspection = app(JoppdPreparationService::class)->inspect('2026-10-05');

        $this->assertStringContainsString('ne odgovara aktivnoj organizaciji', implode(' ', $inspection['errors']));
    }

    public function test_legacy_calculation_uses_current_referee_municipality_when_snapshot_is_missing(): void
    {
        $organization = $this->createOrganization('00000000010');
        $calculation = $this->createCalculation($organization, '00000000036');
        $calculation->update(['referee_local_government_code' => null]);

        $inspection = app(JoppdPreparationService::class)->inspect('2026-10-05');

        $this->assertSame([], $inspection['errors']);
        $this->assertSame('02208', $inspection['rows'][0]['P2']);
    }

    public function test_preparation_blocks_duplicate_recipient_and_missing_municipality_code(): void
    {
        $organization = $this->createOrganization('00000000010');
        $this->createCalculation($organization, '00000000036');
        $secondCalculation = $this->createCalculation($organization, '00000000036', '26278', null);
        $secondCalculation->update(['referee_id' => null]);

        $inspection = app(JoppdPreparationService::class)->inspect('2026-10-05');
        $messages = implode(' ', $inspection['errors']);

        $this->assertStringContainsString('duplog primatelja', $messages);
        $this->assertStringContainsString('nedostaje valjana šifra', $messages);
    }

    public function test_preparation_blocks_mismatched_report_ids(): void
    {
        $organization = $this->createOrganization('00000000010');
        $this->createCalculation($organization, '00000000036');
        $this->createCalculation($organization, '00000000044', '26279');

        $inspection = app(JoppdPreparationService::class)->inspect('2026-10-05');

        $this->assertStringContainsString('isti ispravni petoznamenkasti', implode(' ', $inspection['errors']));
    }

    public function test_preparation_blocks_report_id_that_does_not_match_selected_date(): void
    {
        $organization = $this->createOrganization('00000000010');
        $this->createCalculation($organization, '00000000036', '26001');

        $inspection = app(JoppdPreparationService::class)->inspect('2026-10-05');

        $this->assertStringContainsString('ne odgovara odabranom datumu', implode(' ', $inspection['errors']));
    }

    public function test_preparation_maps_totals_calendar_year_and_p151_values(): void
    {
        $organization = $this->createOrganization('00000000010');
        $this->createCalculation($organization, '00000000036', gross: '56.25', tax: '11.25', net: '45.00');
        $this->createCalculation($organization, '00000000044', gross: '100.00', tax: '20.00', net: '80.00', refereeCode: '123');

        $preparation = app(JoppdPreparationService::class)->prepare('2026-10-05');
        $xml = app(JoppdXmlService::class)->generate($preparation);
        $document = new DOMDocument;
        $this->assertTrue($document->loadXML($xml));
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('j', 'http://e-porezna.porezna-uprava.hr/sheme/zahtjevi/ObrazacJOPPD/v1-1');

        $this->assertSame('2', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaA/j:BrojOsoba)'));
        $this->assertSame('2', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaA/j:BrojRedaka)'));
        $this->assertSame('31.25', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaA/j:PredujamPoreza/j:P5)'));
        $this->assertSame('2026-01-01', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P[1]/j:P101)'));
        $this->assertSame('2026-12-31', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P[1]/j:P102)'));
        $this->assertSame('00', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P[1]/j:P151)'));
        $this->assertSame('0.00', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P[1]/j:P152)'));
        $this->assertSame('00123', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P[2]/j:P2)'));
        $this->assertDatabaseHas('local_governments', ['code' => '123']);
        $this->assertStringContainsString('encoding="UTF-8"', $xml);
    }

    public function test_xml_has_expected_namespaces_order_and_required_zero_elements(): void
    {
        $organization = $this->createOrganization('00000000010');
        $organization->update(['name' => 'Klub & udruga <test>']);
        $this->createCalculation($organization, '00000000036');
        $preparation = app(JoppdPreparationService::class)->prepare('2026-10-05');
        $document = new DOMDocument;
        $document->loadXML(app(JoppdXmlService::class)->generate($preparation));
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('j', 'http://e-porezna.porezna-uprava.hr/sheme/zahtjevi/ObrazacJOPPD/v1-1');
        $xpath->registerNamespace('m', 'http://e-porezna.porezna-uprava.hr/sheme/Metapodaci/v2-0');

        $this->assertSame('1.1', $document->documentElement?->getAttribute('verzijaSheme'));
        $this->assertSame('http://e-porezna.porezna-uprava.hr/sheme/zahtjevi/ObrazacJOPPD/v1-1', $document->documentElement?->namespaceURI);
        $this->assertSame('Klub & udruga <test>', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaA/j:PodnositeljIzvjesca/j:Naziv)'));
        $this->assertSame('ObrazacJOPPD-v1-1', $xpath->evaluate('string(/j:ObrazacJOPPD/m:Metapodaci/m:Uskladjenost)'));
        $this->assertSame('02208', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P/j:P2)'));
        $this->assertSame('0.00', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P/j:P121)'));
        $this->assertSame('0.00', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P/j:P129)'));
        $this->assertSame('0.00', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P/j:P17)'));
        $this->assertSame('Testni', $xpath->evaluate('string(/j:ObrazacJOPPD/j:StranaA/j:IzvjesceSastavio/j:Ime)'));
        $rowNodes = $xpath->query('/j:ObrazacJOPPD/j:StranaB/j:Primatelji/j:P/*');
        $elementNames = [];
        foreach ($rowNodes as $rowNode) {
            $elementNames[] = $rowNode->localName;
        }
        $this->assertLessThan(array_search('P152', $elementNames, true), array_search('P151', $elementNames, true));
    }

    public function test_preparation_versions_changed_snapshots_and_preserves_old_xml_bytes(): void
    {
        $organization = $this->createOrganization('00000000010');
        $this->createCalculation($organization, '00000000036');
        $service = app(JoppdPreparationService::class);
        $xmlService = app(JoppdXmlService::class);

        $first = $service->prepare('2026-10-05');
        $originalXml = $xmlService->generate($first);
        $this->assertSame($first->id, $service->prepare('2026-10-05')->id);

        $organization->update(['street' => 'Druga testna ulica']);
        $second = $service->prepare('2026-10-05');

        $this->assertSame(2, $second->revision);
        $this->assertSame('superseded', $first->fresh()->status);
        $this->assertSame($originalXml, $xmlService->generate($first->fresh()));
        $this->assertStringContainsString('Testna ulica', $originalXml);
    }

    public function test_routes_allow_preview_prepare_and_repeat_download(): void
    {
        $organization = $this->createOrganization('00000000010');
        $this->createCalculation($organization, '00000000036');

        $preview = $this->post(route('joppd.preview'), ['payment_date' => '2026-10-05']);
        $preview->assertOk()->assertSee('Pregled skupa')->assertSee('02208');
        $prepared = $this->post(route('joppd.prepare'), ['payment_date' => '2026-10-05']);
        $preparation = JoppdPreparation::firstOrFail();
        $prepared->assertRedirect(route('joppd.show', $preparation));

        $firstDownload = $this->get(route('joppd.download', $preparation));
        $secondDownload = $this->get(route('joppd.download', $preparation));
        $firstDownload->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertSame($firstDownload->getContent(), $secondDownload->getContent());
    }

    public function test_all_five_local_schemas_and_relative_references_are_available(): void
    {
        $directory = resource_path('joppd/xsd');
        $expected = [
            'MetapodaciTipovi-v2-0.xsd', 'ObrazacJOPPD-v1-1.xsd',
            'ObrazacJOPPDmetapodaci-v1-1.xsd', 'ObrazacJOPPDtipovi-v1-1.xsd',
            'TemeljniTipovi-v2-1.xsd',
        ];
        $actual = array_map('basename', glob($directory.'/*.xsd'));
        sort($actual);
        sort($expected);
        $this->assertSame($expected, $actual);

        foreach ($expected as $file) {
            $document = new DOMDocument;
            $this->assertTrue($document->load($directory.'/'.$file, LIBXML_NONET));
            $xpath = new \DOMXPath($document);
            $xpath->registerNamespace('xs', 'http://www.w3.org/2001/XMLSchema');
            foreach ($xpath->query('//xs:import | //xs:include') as $reference) {
                $location = $reference->getAttribute('schemaLocation');
                $this->assertNotEmpty($location);
                $this->assertContains($location, $expected);
                $this->assertFileExists($directory.'/'.$location);
            }
        }
    }

    public function test_schema_validation_accepts_synthetic_export_and_still_rejects_invalid_other_fields(): void
    {
        $organization = $this->createOrganization('00000000010');
        $this->createCalculation($organization, '00000000036');
        $preparation = app(JoppdPreparationService::class)->prepare('2026-10-05');
        $xml = app(JoppdXmlService::class)->generate($preparation);
        $validator = app(JoppdXmlValidationService::class);

        $this->assertTrue($validator->validate($xml)['valid']);
        $document = new DOMDocument;
        $document->loadXML($xml, LIBXML_NONET);
        $document->getElementsByTagNameNS(
            'http://e-porezna.porezna-uprava.hr/sheme/zahtjevi/ObrazacJOPPD/v1-1', 'P2'
        )->item(0)->nodeValue = 'ABCDE';
        $result = $validator->validate($document->saveXML());

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->get(route('joppd.download', $preparation))->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function createOrganization(string $oib): Organization
    {
        return Organization::create([
            'name' => 'Testna organizacija',
            'street' => 'Testna ulica',
            'house_number' => '1',
            'postal_code' => '10000',
            'city' => 'Testograd',
            'oib' => $oib,
            'iban' => 'HR3100000000000000001',
            'email' => 'info@example.test',
            'responsible_person_first_name' => 'Testni',
            'responsible_person_last_name' => 'Predstavnik',
            'is_active' => true,
        ]);
    }

    private function createCalculation(
        Organization $organization,
        string $refereeOib,
        string $reportId = '26278',
        ?string $refereeCode = '2208',
        string $gross = '56.25',
        string $tax = '11.25',
        string $net = '45.00'
    ): Calculation {
        $localGovernment = LocalGovernment::firstOrCreate(
            ['code' => $refereeCode ?? '2208'],
            ['name' => 'Testograd']
        );
        $referee = Referee::firstOrCreate(['oib' => $refereeOib], [
            'first_name' => 'Testni',
            'last_name' => 'Sudac',
            'address' => 'Ulica primatelja 1',
            'postal_code' => '10000',
            'city' => 'Testograd',
            'local_government_id' => $refereeCode === null ? null : $localGovernment->id,
            'iban' => 'HR3100000000000000001',
            'is_active' => true,
        ]);

        return Calculation::create([
            'report_id' => $reportId,
            'organization_name' => $organization->name,
            'organization_address' => $organization->street.' '.$organization->house_number,
            'organization_postal_code' => $organization->postal_code,
            'organization_city' => $organization->city,
            'organization_oib' => $organization->oib,
            'organization_iban' => $organization->iban,
            'referee_id' => $referee->id,
            'referee_first_name' => $referee->first_name,
            'referee_last_name' => $referee->last_name,
            'referee_oib' => $referee->oib,
            'referee_address' => $referee->address,
            'referee_postal_code' => $referee->postal_code,
            'referee_city' => $referee->city,
            'referee_iban' => $referee->iban,
            'referee_local_government_code' => $refereeCode,
            'payment_date' => '2026-10-05',
            'net_amount' => $net,
            'gross_amount' => $gross,
            'tax_rate' => '20.00',
            'coefficient' => '1.250000',
            'tax_amount' => $tax,
            'tax_iban' => 'HR3100000000000000001',
            'tax_city' => 'Testograd',
            'tax_valid_from' => '2026-01-01',
            'status' => 'draft',
        ]);
    }
}
