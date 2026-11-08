<?php

namespace App\Services;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

/**
 * Reads only the verified 2026 JLS OOXML layout, not arbitrary spreadsheets.
 */
class JlsWorkbookReader
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const HEADERS = [
        'A' => 'Rbr.', 'B' => 'Šifra grada/općine', 'C' => 'Ime grada/općine',
        'D' => 'Uplatni račun', 'E' => 'Broj NN', 'F' => 'Niža stopa 2026.',
        'G' => 'Viša stopa 2026.', 'H' => 'Stupanje na snagu',
    ];

    /** @return list<array{code: string, name: string, iban: string, lower_rate: string, valid_from: string}> */
    public function read(string $path): array
    {
        if (! class_exists(ZipArchive::class) || ! class_exists(DOMDocument::class)) {
            throw new RuntimeException('Uvoz zahtijeva PHP ekstenzije zip i dom.');
        }
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'xlsx'
            || ! is_file($path) || ! is_readable($path) || filesize($path) > 10 * 1024 * 1024) {
            throw new RuntimeException('Odaberite čitljivu lokalnu XLSX datoteku do 10 MiB.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('XLSX nije čitljiva ZIP arhiva.');
        }

        try {
            $total = 0;
            $names = [];
            if ($zip->numFiles > 512) {
                throw new RuntimeException('XLSX ima previše zapisa.');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                if ($entry === false || isset($names[$entry['name']])) {
                    throw new RuntimeException('Neispravan ili dupliciran ZIP zapis.');
                }
                $names[$entry['name']] = true;
                $total += $entry['size'];
                if ($total > 32 * 1024 * 1024 || $entry['size'] > 8 * 1024 * 1024) {
                    throw new RuntimeException('Prevelik raspakirani XLSX sadržaj.');
                }
            }

            $workbook = $this->xml($zip, 'xl/workbook.xml');
            $sheets = $workbook->query('/s:workbook/s:sheets/s:sheet[@name="Tablica JLS 2026"]');
            if ($sheets->length !== 1) {
                throw new RuntimeException('Nedostaje jedinstveni list Tablica JLS 2026.');
            }
            $relationshipId = $sheets->item(0)->getAttributeNS(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id'
            );
            $relationships = $this->xml($zip, 'xl/_rels/workbook.xml.rels');
            $sheetPath = null;
            foreach ($relationships->document->documentElement->childNodes as $relationship) {
                if (! $relationship instanceof DOMElement) {
                    continue;
                }
                if ($relationship->getAttribute('TargetMode') === 'External') {
                    throw new RuntimeException('Vanjske poveznice radne knjige nisu podržane.');
                }
                if ($relationship->getAttribute('Id') === $relationshipId) {
                    $target = $relationship->getAttribute('Target');
                    if (! preg_match('~^(?:/xl/)?worksheets/[A-Za-z0-9_-]+\.xml$~D', $target)) {
                        throw new RuntimeException('Neočekivana putanja radnog lista.');
                    }
                    $sheetPath = str_starts_with($target, '/') ? substr($target, 1) : 'xl/'.$target;
                }
            }
            if ($sheetPath === null) {
                throw new RuntimeException('Nedostaje poveznica na radni list.');
            }

            $strings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach ($this->xml($zip, 'xl/sharedStrings.xml')->query('/s:sst/s:si') as $item) {
                    $strings[] = $this->text($item);
                }
            }
            $styles = $this->xml($zip, 'xl/styles.xml');
            $formats = [0 => 'General', 9 => '0%', 10 => '0.00%', 49 => '@'];
            foreach ($styles->query('/s:styleSheet/s:numFmts/s:numFmt') as $format) {
                $formats[(int) $format->getAttribute('numFmtId')] = $format->getAttribute('formatCode');
            }
            $cellFormats = [];
            foreach ($styles->query('/s:styleSheet/s:cellXfs/s:xf') as $style) {
                $cellFormats[] = $formats[(int) $style->getAttribute('numFmtId')] ?? 'unsupported';
            }

            return $this->rows($this->xml($zip, $sheetPath), $strings, $cellFormats);
        } finally {
            $zip->close();
        }
    }

    private function xml(ZipArchive $zip, string $name): DOMXPath
    {
        $xml = $zip->getFromName($name, 8 * 1024 * 1024 + 1);
        if ($xml === false || strlen($xml) > 8 * 1024 * 1024
            || preg_match('/<!DOCTYPE|<!ENTITY/i', $xml)) {
            throw new RuntimeException('Nedostaje ili je nesiguran XML zapis u XLSX-u.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument;
            if (! $document->loadXML($xml, LIBXML_NONET) || $document->doctype !== null) {
                throw new RuntimeException('Neispravan XML zapis u XLSX-u.');
            }
            $xpath = new DOMXPath($document);
            $xpath->registerNamespace('s', self::NS);

            return $xpath;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function text(DOMElement $element): string
    {
        $text = '';
        foreach ($element->getElementsByTagNameNS(self::NS, 't') as $part) {
            $text .= $part->textContent;
        }

        return trim($text);
    }

    /** @param list<string> $strings
     * @param  list<string>  $formats
     * @return list<array{code: string, name: string, iban: string, lower_rate: string, valid_from: string}>
     */
    private function rows(DOMXPath $sheet, array $strings, array $formats): array
    {
        $rows = [];
        $seen = [];
        $headerFound = false;
        foreach ($sheet->query('/s:worksheet/s:sheetData/s:row') as $row) {
            $number = (int) $row->getAttribute('r');
            $cells = [];
            $types = [];
            $cellFormats = [];
            foreach ($row->getElementsByTagNameNS(self::NS, 'c') as $cell) {
                $reference = $cell->getAttribute('r');
                if (! preg_match('/^([A-Z]+)([1-9][0-9]*)$/D', $reference, $match)
                    || (int) $match[2] !== $number || isset($cells[$match[1]])
                    || $cell->getElementsByTagNameNS(self::NS, 'f')->length !== 0) {
                    throw new RuntimeException('Neispravne ćelije ili formule u XLSX-u.');
                }
                $type = $cell->getAttribute('t');
                $value = $cell->getElementsByTagNameNS(self::NS, 'v')->item(0)?->textContent ?? '';
                if ($type === 's') {
                    if (! ctype_digit($value) || ! array_key_exists((int) $value, $strings)) {
                        throw new RuntimeException('Neispravna tablica tekstova u XLSX-u.');
                    }
                    $value = $strings[(int) $value];
                } elseif ($type === 'inlineStr') {
                    $value = $this->text($cell);
                } elseif (! in_array($type, ['', 'n'], true)) {
                    throw new RuntimeException('Nepodržana vrsta ćelije u XLSX-u.');
                }
                $column = $match[1];
                $cells[$column] = trim($value);
                $types[$column] = $type;
                $cellFormats[$column] = $formats[(int) ($cell->getAttribute('s') ?: 0)] ?? 'unsupported';
            }
            if (implode('', $cells) === '') {
                continue;
            }
            if (array_diff(array_keys(array_filter($cells, fn (string $v): bool => $v !== '')), array_keys(self::HEADERS)) !== []) {
                throw new RuntimeException('Neočekivani stupci u XLSX-u.');
            }
            if (! $headerFound) {
                $normalized = array_map(fn (string $v): string => preg_replace('/\s+/u', ' ', $v), $cells);
                foreach (self::HEADERS as $column => $heading) {
                    if (($normalized[$column] ?? '') !== $heading || $number !== 1) {
                        throw new RuntimeException('Neočekivano XLSX zaglavlje za 2026.');
                    }
                }
                $headerFound = true;

                continue;
            }
            if ($number !== count($rows) + 2) {
                throw new RuntimeException('Nepotpuni ili duplicirani redci u XLSX-u.');
            }
            foreach (['B', 'C', 'D', 'F', 'H'] as $column) {
                if (($cells[$column] ?? '') === '') {
                    throw new RuntimeException("Redak {$number}: nedostaje obvezno polje {$column}.");
                }
            }

            $code = $cells['B'];
            if (in_array($types['B'], ['', 'n'], true)
                && ! in_array($cellFormats['B'], ['General', '@'], true)
                && ! preg_match('/^0{1,5}$/D', $cellFormats['B'])) {
                throw new RuntimeException("Redak {$number}: neočekivan format šifre JLS-a.");
            }
            if (in_array($types['B'], ['', 'n'], true) && preg_match('/^0{1,5}$/D', $cellFormats['B'])) {
                $code = str_pad($code, strlen($cellFormats['B']), '0', STR_PAD_LEFT);
            }
            if (! preg_match('/^[0-9]{1,5}$/D', $code) || (int) $code === 0) {
                throw new RuntimeException("Redak {$number}: neispravna šifra JLS-a.");
            }
            $key = ltrim($code, '0');
            if (isset($seen[$key])) {
                throw new RuntimeException("Redak {$number}: duplicirana šifra JLS-a.");
            }
            $seen[$key] = true;
            $name = $cells['C'];
            if (mb_strlen($name) > 255 || ! preg_match('/\p{L}/u', $name)
                || preg_match('/[\p{C}<>]/u', $name)) {
                throw new RuntimeException("Redak {$number}: neispravan naziv JLS-a.");
            }
            $iban = strtoupper(preg_replace('/\s+/', '', $cells['D']));
            if (! preg_match('/^HR[0-9]{19}$/D', $iban) || ! verify_iban($iban, true)) {
                throw new RuntimeException("Redak {$number}: neispravan IBAN.");
            }
            if (! in_array($types['F'], ['', 'n'], true) || ! is_numeric($cells['F'])
                || ! in_array($cellFormats['F'], ['0%', '0.00%'], true)) {
                throw new RuntimeException("Redak {$number}: stopa mora biti broj u Excel postotnom formatu.");
            }
            $rate = (float) $cells['F'] * 100;
            $allowed = [15, 17, 17.5, 18, 19, 20, 20.5, 21, 21.3, 21.4, 21.5, 21.6, 21.8, 22, 22.2, 22.3, 22.4, 23, 23.6];
            if (! is_finite($rate) || abs($rate - round($rate, 2)) > 0.0000001
                || ! in_array(round($rate, 2), $allowed)) {
                throw new RuntimeException("Redak {$number}: stopa nije podržana postojećim obrascem.");
            }
            if (! preg_match('/^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})\.$/D', $cells['H'], $date)
                || ! checkdate((int) $date[2], (int) $date[1], (int) $date[3])) {
                throw new RuntimeException("Redak {$number}: neispravan datum stupanja na snagu.");
            }
            $validFrom = DateTimeImmutable::createFromFormat('!j.n.Y.', $cells['H']);
            $rows[] = [
                'code' => $code, 'name' => $name, 'iban' => $iban,
                'lower_rate' => number_format($rate, 2, '.', ''),
                'valid_from' => $validFrom->format('Y-m-d'),
            ];
        }
        if (count($rows) !== 556) {
            throw new RuntimeException('Očekuje se točno 556 JLS zapisa; pronađeno: '.count($rows).'.');
        }

        return $rows;
    }
}
