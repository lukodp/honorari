<?php

namespace App\Services;

use App\Models\JoppdPreparation;
use DOMDocument;
use DOMElement;

class JoppdXmlService
{
    private const NS = 'http://e-porezna.porezna-uprava.hr/sheme/zahtjevi/ObrazacJOPPD/v1-1';

    private const META_NS = 'http://e-porezna.porezna-uprava.hr/sheme/Metapodaci/v2-0';

    public function generate(JoppdPreparation $preparation): string
    {
        $snapshot = $preparation->snapshot;
        $header = $snapshot['header'];
        $organization = $header['organization'];
        $rows = $snapshot['rows'];
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = false;
        $root = $document->createElementNS(self::NS, 'ObrazacJOPPD');
        $root->setAttribute('verzijaSheme', '1.1');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:meta', self::META_NS);
        $document->appendChild($root);

        $metadata = $document->createElementNS(self::META_NS, 'meta:Metapodaci');
        $root->appendChild($metadata);
        $this->metadataElement($document, $metadata, 'Naslov', 'http://purl.org/dc/elements/1.1/title', 'Izvjesce o primicima, porezu na dohodak i prirezu te doprinosima za obvezna osiguranja');
        $this->metadataElement($document, $metadata, 'Autor', 'http://purl.org/dc/elements/1.1/creator', $organization['name']);
        $this->metadataElement($document, $metadata, 'Datum', 'http://purl.org/dc/elements/1.1/date', $snapshot['metadata']['created_at']);
        $this->metadataElement($document, $metadata, 'Format', 'http://purl.org/dc/elements/1.1/format', 'text/xml');
        $this->metadataElement($document, $metadata, 'Jezik', 'http://purl.org/dc/elements/1.1/language', 'hr-HR');
        $this->metadataElement($document, $metadata, 'Identifikator', 'http://purl.org/dc/elements/1.1/identifier', $snapshot['metadata']['uuid']);
        $this->metadataElement($document, $metadata, 'Uskladjenost', 'http://purl.org/dc/terms/conformsTo', 'ObrazacJOPPD-v1-1');
        $this->metadataElement($document, $metadata, 'Tip', 'http://purl.org/dc/elements/1.1/type', 'Elektronički obrazac');
        $this->appendText($document, $metadata, 'meta:Adresant', 'Ministarstvo Financija, Porezna uprava, Zagreb', self::META_NS);

        $pageA = $document->createElementNS(self::NS, 'StranaA');
        $root->appendChild($pageA);
        foreach ([
            'DatumIzvjesca' => $header['payment_date'],
            'OznakaIzvjesca' => $header['report_id'],
            'VrstaIzvjesca' => '1',
        ] as $name => $value) {
            $this->appendText($document, $pageA, $name, (string) $value);
        }

        $this->appendOrganizationBlock($document, $pageA, 'PodnositeljIzvjesca', $organization, true);
        $this->appendOrganizationBlock($document, $pageA, 'ObveznikPlacanja', $organization, false);
        $this->appendText($document, $pageA, 'BrojOsoba', (string) $snapshot['summary']['people']);
        $this->appendText($document, $pageA, 'BrojRedaka', (string) $snapshot['summary']['rows']);

        $taxAdvance = $document->createElementNS(self::NS, 'PredujamPoreza');
        $pageA->appendChild($taxAdvance);
        foreach (['P1', 'P11', 'P12', 'P2', 'P3', 'P4'] as $name) {
            $this->appendText($document, $taxAdvance, $name, '0.00');
        }
        $this->appendText($document, $taxAdvance, 'P5', (string) $snapshot['summary']['tax']);
        $this->appendText($document, $taxAdvance, 'P6', '0.00');

        $contributions = $document->createElementNS(self::NS, 'Doprinosi');
        $pageA->appendChild($contributions);
        foreach ([
            'GeneracijskaSolidarnost' => 7,
            'KapitaliziranaStednja' => 6,
            'ZdravstvenoOsiguranje' => 12,
            'Zaposljavanje' => 4,
        ] as $block => $count) {
            $element = $document->createElementNS(self::NS, $block);
            $contributions->appendChild($element);
            for ($index = 1; $index <= $count; $index++) {
                $this->appendText($document, $element, 'P'.$index, '0.00');
            }
        }

        foreach ([
            'IsplaceniNeoporeziviPrimici' => '0.00',
            'KamataMO2' => '0.00',
            'UkupniNeoporeziviPrimici' => '0.00',
        ] as $name => $value) {
            $this->appendText($document, $pageA, $name, $value);
        }
        $disability = $document->createElementNS(self::NS, 'NaknadaZaposljavanjeInvalida');
        $pageA->appendChild($disability);
        $this->appendText($document, $disability, 'P1', '0');
        $this->appendText($document, $disability, 'P2', '0.00');
        $composer = $document->createElementNS(self::NS, 'IzvjesceSastavio');
        $pageA->appendChild($composer);
        $this->appendText($document, $composer, 'Ime', $organization['responsible_person_first_name']);
        $this->appendText($document, $composer, 'Prezime', $organization['responsible_person_last_name']);

        $pageB = $document->createElementNS(self::NS, 'StranaB');
        $root->appendChild($pageB);
        $recipients = $document->createElementNS(self::NS, 'Primatelji');
        $pageB->appendChild($recipients);
        foreach ($rows as $row) {
            $recipient = $document->createElementNS(self::NS, 'P');
            $recipients->appendChild($recipient);
            foreach ($row as $name => $value) {
                $this->appendText($document, $recipient, $name, (string) $value);
            }
        }

        $xml = $document->saveXML();
        if ($xml === false) {
            throw new \RuntimeException('JOPPD XML nije moguće serijalizirati.');
        }

        return $xml;
    }

    private function appendOrganizationBlock(
        DOMDocument $document,
        DOMElement $parent,
        string $name,
        array $organization,
        bool $includeType
    ): void {
        $block = $document->createElementNS(self::NS, $name);
        $parent->appendChild($block);
        $this->appendText($document, $block, 'Naziv', $organization['name']);
        $address = $document->createElementNS(self::NS, 'Adresa');
        $block->appendChild($address);
        $this->appendText($document, $address, 'Mjesto', $organization['city']);
        $this->appendText($document, $address, 'Ulica', $organization['street']);
        $this->appendText($document, $address, 'Broj', $organization['house_number']);
        $this->appendText($document, $block, 'Email', $organization['email']);
        $this->appendText($document, $block, 'OIB', $organization['oib']);
        if ($includeType) {
            $this->appendText($document, $block, 'Oznaka', '1');
        }
    }

    private function metadataElement(
        DOMDocument $document,
        DOMElement $parent,
        string $name,
        string $attributeNamespace,
        string $value
    ): void {
        $element = $document->createElementNS(self::META_NS, 'meta:'.$name);
        $element->setAttribute('dc', $attributeNamespace);
        $element->appendChild($document->createTextNode($value));
        $parent->appendChild($element);
    }

    private function appendText(
        DOMDocument $document,
        DOMElement $parent,
        string $name,
        string $value,
        string $namespace = self::NS
    ): void {
        $element = $document->createElementNS($namespace, $name);
        $element->appendChild($document->createTextNode($value));
        $parent->appendChild($element);
    }
}
