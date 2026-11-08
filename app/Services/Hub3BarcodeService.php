<?php
namespace App\Services;

use App\Models\Calculation;
use Le\PaymentBarcodeGenerator\Data;
use Le\PaymentBarcodeGenerator\Generator;
use Le\PaymentBarcodeGenerator\Party;

class Hub3BarcodeService
{
    public function forHonorarium(Calculation $calculation): string
    {
        $data = new Data(
            payer: new Party(
                name: $calculation->organization_name,
                address: $calculation->organization_address,
                city: trim($calculation->organization_postal_code . ' ' . $calculation->organization_city),
            ),
            payee: new Party(
                name: trim($calculation->referee_first_name . ' ' . $calculation->referee_last_name),
                address: $calculation->referee_address,
                city: trim($calculation->referee_postal_code . ' ' . $calculation->referee_city),
            ),
            iban: $calculation->referee_iban,
            currency: 'EUR',
            amount: (int) round((float) $calculation->net_amount * 100),
            model: 'HR69',
            reference: '40002-' . $calculation->organization_oib . '-130',
            code: 'OTHR',
            description: 'Honorari izvjesce ' . $calculation->report_id,
        );

        return (new Generator())->render($data);
    }

    public function forTax(Calculation $calculation): string
    {
        $data = new Data(
            payer: new Party(
                name: $calculation->organization_name,
                address: $calculation->organization_address,
                city: trim($calculation->organization_postal_code . ' ' . $calculation->organization_city),
            ),
            payee: new Party(
                name: $calculation->tax_city,
                address: '',
                city: $calculation->tax_city,
            ),
            iban: $calculation->tax_iban,
            currency: 'EUR',
            amount: (int) round((float) $calculation->tax_amount * 100),
            model: 'HR68',
            reference: '1945-' . $calculation->organization_oib . '-' . $calculation->report_id,
            code: 'OTHR',
            description: 'Porez honorar izvjesce ' . $calculation->report_id,
        );

        return (new Generator())->render($data);
    }
}
