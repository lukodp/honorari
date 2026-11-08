<?php

namespace App\Services;

use DOMDocument;
use LibXMLError;

class JoppdXmlValidationService
{
    private const NS = 'http://e-porezna.porezna-uprava.hr/sheme/zahtjevi/ObrazacJOPPD/v1-1';

    /**
     * @return array{valid: bool, errors: array<int, string>, warnings: array<int, string>}
     */
    public function validate(string $xml): array
    {
        $schemaPath = resource_path('joppd/xsd/ObrazacJOPPD-v1-1.xsd');
        if (! is_file($schemaPath)) {
            return [
                'valid' => false,
                'errors' => ['Lokalna JOPPD XSD shema nije dostupna.'],
                'warnings' => [],
            ];
        }

        $document = $this->loadDocument($xml);
        if (! $document) {
            return ['valid' => false, 'errors' => ['Generirani XML nije dobro oblikovan.'], 'warnings' => []];
        }

        if ($this->validates($document, $schemaPath)) {
            return ['valid' => true, 'errors' => [], 'warnings' => []];
        }

        $validationCopy = $this->loadDocument($xml);
        $changedP151 = false;
        foreach ($validationCopy->getElementsByTagNameNS(self::NS, 'P151') as $element) {
            if ($element->textContent === '00') {
                $element->nodeValue = '0';
                $changedP151 = true;
            }
        }

        if ($changedP151 && $this->validates($validationCopy, $schemaPath)) {
            libxml_clear_errors();

            return [
                'valid' => true,
                'errors' => [],
                'warnings' => ['Lokalna XSD kopija ne prihvaća potvrđeni P151=00; XML zadržava vrijednost 00 prema referentnom obrascu.'],
            ];
        }

        return [
            'valid' => false,
            'errors' => $this->errorMessages(),
            'warnings' => [],
        ];
    }

    private function loadDocument(string $xml): ?DOMDocument
    {
        $document = new DOMDocument;
        $document->loadXML($xml, LIBXML_NONET);

        return $document->documentElement ? $document : null;
    }

    private function validates(DOMDocument $document, string $schemaPath): bool
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        return $document->schemaValidate($schemaPath, LIBXML_NONET);
    }

    /**
     * @return array<int, string>
     */
    private function errorMessages(): array
    {
        $messages = array_map(
            fn (LibXMLError $error): string => trim($error->message),
            libxml_get_errors()
        );
        libxml_clear_errors();

        return $messages !== [] ? $messages : ['XML ne prolazi provjeru prema lokalnoj JOPPD XSD shemi.'];
    }
}
