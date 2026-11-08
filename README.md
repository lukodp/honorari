# Honorari — obračun sudačkih honorara

Honorari je Laravel aplikacija za obračun drugog dohotka za sudačke honorare. Ova radna distribucija priprema buduću verziju **v2.0.2**, bez stare Git povijesti, osobnih evidencija i unaprijed popunjenih poreznih podataka. Ranija priprema prve javne distribucije 1.0.0 ostaje zabilježena u [CHANGELOG.md](CHANGELOG.md); nijedna verzija nije objavljena ovim postupkom.

## Važna napomena

**Aplikacija je informativnog karaktera. Autor ne odgovara za točnost izračuna niti za potencijalne porezne prekršaje proizašle iz korištenja generiranih datoteka.**

## Funkcionalnosti

- Organizacije, aktivni isplatitelj i evidencija sudaca/primatelja.
- Jedinice lokalne samouprave (JLS), porezne stope i razdoblja njihove primjene.
- Pregled obračuna, izračun bruto iznosa i poreza iz neto iznosa te spremljene snimke podataka.
- Status plaćanja, HUB3 nalozi, PDF417 barkodovi i ispis iz preglednika.
- JOPPD pripreme, verzionirane snimke i ponovljivi XML izvoz s provjerom prema lokalnoj XSD shemi.

JOPPD XML se preuzima lokalno. Aplikacija ga ne predaje automatski u ePoreznu i ne potvrđuje da je pojedini obrazac prihvaćen od Porezne uprave.

## Ograničenja i zaštita podataka

Aplikacija nema ugrađenu prijavu ni sustav ovlasti. Koristite je lokalno ili iza zasebne kontrole pristupa. Nemojte je izložiti internetu sa stvarnim podacima bez dodatne zaštite. Javan izvorni kod nije isto što i javno dostupna evidencija osoba.

Prije stvarne isplate provjerite aktualne propise, porezne stope, njihove datume i račune primatelja. Dopuštene stope u obrascu trenutačno su ograničene popisom u validaciji; buduće zakonske promjene mogu zahtijevati prilagodbu aplikacije. Formule nisu mijenjane pri pripremi ove distribucije.

Lokalna JOPPD validacija ima postojeću prilagodbu za P151=00 koju priložena XSD kopija ne prihvaća izravno. Uspješna lokalna validacija nije potvrda prihvata u ePoreznoj. Provjerite aktualnost XSD shema prije uporabe.

## Preduvjeti

- PHP 8.4, Composer 2 i SQLite (PDO SQLite).
- PHP proširenja koja traži zaključani skup ovisnosti: bcmath, ctype, curl, dom, fileinfo, filter, gd, hash, iconv, json, libxml, mbstring, openssl, pcre, phar, session, tokenizer, xml, xmlwriter i zlib. Ekstenzija zip obvezna je za početni XLSX uvoz.
- Node.js 24 ili noviji kompatibilan s engines zahtjevima u package-lock.json, uz npm. Lokalna provjera koristi Node.js 26.
- Dostupna mreža za instalaciju ovisnosti i preuzimanje fontova tijekom frontend builda.
- Za lokalno pokretanje preporučen je Laravel Herd; web poslužitelj mora posluživati samo direktorij public.

Verzije se reproduciraju pomoću composer.lock i package-lock.json; ne koristite update kao korak instalacije. Instaliranu platformu provjerite naredbom `composer check-platform-reqs`.

## Instalacija

Otvorite terminal u raspakiranoj distribuciji:

```sh
composer install --prefer-dist --no-interaction
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate --no-interaction
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

U vlastitoj .env datoteci postavite APP_URL za odabrani lokalni naziv. DB_CONNECTION je sqlite. Ako DB_DATABASE nije zadan, koristi se database/database.sqlite unutar ove instalacije. Za prilagođenu lokaciju zadajte vlastitu apsolutnu putanju, primjerice:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE="C:/putanja/do/instalacije/database/database.sqlite"
```

Svaka instalacija mora imati vlastitu bazu i vlastiti APP_KEY. Ne koristite tuđu .env datoteku, bazu ili ključeve.

Tek nakon provjere da konfiguracija pokazuje na novu, praznu bazu pokrenite:

```sh
php artisan migrate --no-interaction
npm ci --ignore-scripts
npm run build
```

Ne pokrećite migrate:fresh na bazi koju želite sačuvati. Napravite sigurnosnu kopiju prije kasnijih nadogradnji.

## Lokalno pokretanje

U direktoriju instalacije, uz prethodno instaliran Herd:

```sh
herd link honorari-public --isolate=8.4
herd open
```

APP_URL uskladite s adresom koju prikaže Herd. Instalacija ne zahtijeva Redis, vanjsku bazu ili SMTP račun. Za rad s izgrađenim resursima nije potreban Vite razvojni poslužitelj. Za razvoj frontend resursa možete koristiti `npm run dev`.

## Prvi unos i referentni podaci

Baza je nakon migracija prazna. Za početni skup najprije zasebno preuzmite [službenu XLSX tablicu za 2026. sa stranice Porezne uprave](https://porezna-uprava.gov.hr/hr/stope-godisnjeg-poreza-na-dohodak-za-2026-godinu/8166) i provjerite uvjete njezine dopuštene uporabe. Sačuvajte je samo lokalno, primjerice kao `storage/app/private/imports/jls/2026/tablica-2026.xlsx`. Taj direktorij po potrebi izradite sami. XLSX i CSV podaci nisu dio distribucije niti se preuzimaju u CI-ju.

Nakon migracija **nove prazne baze**, provjerite ciljnu putanju u konfiguraciji i najprije pokrenite pregled:

```sh
php artisan jls:import-initial "storage/app/private/imports/jls/2026/tablica-2026.xlsx" --dry-run
```

Za stvarni početni upis pokrenite istu naredbu bez `--dry-run`:

```sh
php artisan jls:import-initial "storage/app/private/imports/jls/2026/tablica-2026.xlsx"
```

Naredba prije bilo kakvog upisa validira cijelu knjigu, prikazuje ciljnu bazu, broj zapisa i prva tri pretvorena retka te traži potvrdu `yes`. Očekivani rezultat je **556 JLS zapisa i 556 poreznih stopa**. Uvoz je dopušten samo ako su obje tablice `local_governments` i `tax_rates` prazne; ponovno pokretanje nad popunjenim tablicama odbija se. Provjera se ponavlja unutar transakcije; pogreška pri upisu poništava sve upise tog uvoza. Nema automatskog osvježavanja pri pokretanju aplikacije ni opcije za prisilno prepisivanje postojećih podataka. Uvoz izvršite prije korištenja instalacije, bez paralelnih unosa u ciljnu bazu. Ne koristite `--no-interaction` za upis: bez potvrde uvoz se otkazuje.

Zatim unesite organizaciju i aktivirajte je, dodajte suce i izradite obračun. Ako ne želite skupni uvoz, postojeći obrazac omogućuje ručni unos potrebnih JLS-a sa službenim šiframa te provjerenih stopa, računa i datuma. Nakon takvog unosa početni skupni uvoz više nije dopušten.

Postojeći seederi ostaju radi kompatibilnosti s ranijim lokalnim postupkom. Opcionalno čitaju zaseban UTF-8 CSV `database/seeders/data/podaci_jls_2026.csv` sa separatorom točka-zarez:

```csv
code;name;iban;lower_rate;valid_from
```

Ta je putanja ignorirana; bez privatnog CSV-a seederi prijavljuju da nedostaje datoteka. Nakon vlastite potpune provjere podataka, backupa i provjere ciljne baze, stari redoslijed je:

```sh
php artisan db:seed --class=LocalGovernmentSeeder --no-interaction
php artisan db:seed --class=TaxRateSeeder --no-interaction
```

Ti seederi nemaju sigurnosne provjere ni atomarnost nove naredbe: mogu ažurirati postojeće zapise i nisu preporučeni instalacijski put. Nemojte ih pokretati nad postojećom evidencijom bez zasebnog plana. Zadani `DatabaseSeeder` stvara samo razvojni testni račun i nije potreban za instalaciju.

## Službeni podaci o poreznim stopama

**MIT licenca obuhvaća vlastiti programski kod aplikacije, ne službene porezne podatke ni njihove izvedene CSV kopije.** Podatkovni skup nije uključen u ovu javnu distribuciju.

Izvor: [objava Porezne uprave za 2026.](https://porezna-uprava.gov.hr/hr/stope-godisnjeg-poreza-na-dohodak-za-2026-godinu/8166) i [službena XLSX tablica za 2026.](https://porezna-uprava.gov.hr/UserDocsImages/Portal_porezne_konkurentnosti/Odluke_JLS/Porezne%20stope%20godi%C5%A1njeg%20poreza%20na%20dohodak/Tablica%20poreznih%20stopa%20godi%C5%A1njeg%20poreza%20na%20dohodak%20za%202026%20godinu.xlsx). Datum preuzimanja i provjere: **2026-11-08**. SHA256 preuzetog XLSX-a: `87733F3EB50A39650813A4EA7A6EFBB8C556D4BF9C119ECE9EA411F37E6F1592`.

Objava je od 16.12.2025. i odnosi se na odluke objavljene do 15. prosinca. Datum naše provjere ne potvrđuje da ta datoteka obuhvaća sve kasnije promjene tijekom 2026. Prije uporabe treba provjeriti novije objave i konkretne odluke JLS-a u Narodnim novinama.

### Provjerena struktura izvora

Radni list `Tablica JLS 2026` ima zaglavlje i **556 podatkovnih redaka**, s ovim stvarnim stupcima:

| XLSX stupac | Sadržaj | Uporaba u postojećem CSV-u |
| --- | --- | --- |
| A | Rbr. | Ne uvozi se kao identifikator. |
| B | Šifra grada/općine | code |
| C | Ime grada/općine | name |
| D | Uplatni račun | iban |
| E | Broj NN | Referenca za provjeru odluke; sadašnji model je ne sprema. |
| F | Niža stopa 2026. | lower_rate |
| G | Viša stopa 2026. | Ne koristi se u sadašnjem obračunu. |
| H | Stupanje na snagu | Izvor vrijednosti valid_from u pregledanom CSV-u. |

Za reprodukciju ovih pet CSV polja **nisu potrebni dodatni izvori šifri ili IBAN-a**: oba podatka postoje u ovom XLSX-u. Za provjeru ispravnosti uplata i kasnijih izmjena dodatno služe [Naputak za 2026., NN 18/2026](https://narodne-novine.nn.hr/clanci/sluzbeni/2026_02_18_197.html), njegove [izmjene NN 39/2026](https://narodne-novine.nn.hr/clanci/sluzbeni/2026_04_39_470.html) i eventualne novije izmjene. Ne izvoditi IBAN iz šifre ako je službeni račun već naveden.

Read-only usporedba ranijeg referentnog CSV-a s preuzetim XLSX-om, spajanjem po šifri, dala je **556/556 podudaranja naziva, IBAN-a, niže stope i datuma**, bez duplikata, nedostajućih šifri ili razlika. Svih 556 računa prolazi instalirani IBAN validator. To potvrđuje podudarnost sadržaja, ne izvorni način nastanka starog CSV-a ni pravo njegove redistribucije.

Stopa je u Excelu pohranjena kao razlomak s postotnim formatom: npr. 0.20 znači 20.00 u CSV-u. Svih 556 datuma u stupcu H pohranjeno je kao tekst d.m.yyyy.; pretvorba za CSV je YYYY-MM-DD. Datumi su raspoređeni u 2024. (449), 2025. (87) i 2026. (20). Ne zamjenjivati ih automatski datumom 2026-01-01. Stupac opisuje stupanje odluke na snagu; XLSX nema zaseban stupac početka porezne primjene niti predstavlja cjelovitu povijest svih stopa. Za točno razdoblje primjene mjerodavan je tekst odgovarajuće odluke.

### Format, početni uvoz i kasnije promjene

Nova naredba `jls:import-initial` izravno pretvara lokalni XLSX u istu strukturu pet polja koju koriste seederi: `code`, `name`, `iban`, `lower_rate`, `valid_from`. Ne stvara CSV ni kopiju službenog skupa u repozitoriju. `LocalGovernmentSeeder` čita prva dva CSV polja položajno, a `TaxRateSeeder` druga polja po nazivu zaglavlja; njihovo ponašanje nije mijenjano.

Čitač je namjenski, bez nove Composer ovisnosti: koristi PHP `ZipArchive` i DOM, samo lokalne datoteke do 10 MiB, do 512 ZIP zapisa, ukupno do 32 MiB raspakiranog sadržaja i do 8 MiB po zapisu. Ne raspakirava datoteke na disk. Odbija XML s DTD/entitetima, vanjske poveznice radne knjige i formule umjesto podatkovnih ćelija. To nije opći Excel uređivač: zahtijeva list `Tablica JLS 2026`, zaglavlje A–H iz gornje tablice, točno 556 potpunih redaka i poznatu strukturu OOXML-a. Promijenjeni službeni predložak zahtijeva pregled čitača.

Podržani su zajednički i ugrađeni tekstovi ćelija. Šifre su tekstualni identifikatori; numerički Excel format `00000` pretvara se u prikaz s pet znamenaka, a tekstualne vodeće nule čuvaju se. Duplikati se traže i neovisno o vodećim nulama. Pregledani stari CSV koristio je šifre bez početnih nula; JOPPD izvoz ionako dopunjava šifru na pet znamenaka. Početni uvoz ne pretvara niti mijenja postojeće zapise.

IBAN mora biti hrvatskog formata s ispravnom kontrolnom znamenkom prema postojećem validatoru. Stopa F mora biti numerički razlomak s Excel postotnim formatom `0%` ili `0.00%`: `0.20` postaje `20.00`. Prihvaćaju se stope s postojećeg popisa ručnog obrasca; nova stopa izvan njega zahtijeva zasebnu provjeru kompatibilnosti. Koeficijent se računa jednako postojećem seederu, na šest decimala, a `valid_until` ostaje prazan.

**Pretpostavka datuma ostaje nepromijenjena:** tekstualni datum H `d.m.yyyy.` pretvara se u `YYYY-MM-DD` za `valid_from`, kao u ranijem CSV-u/seederu. H označava stupanje odluke na snagu; time nije zasebno potvrđen datum porezne primjene. Uvoz ne izmišlja povijest prethodnih stopa niti mijenja datum na 2026-01-01. Za pravilan datum obvezno provjerite odgovarajuću odluku. Sintaktička provjera i uspješan uvoz nisu jamstvo aktualnosti poreznih podataka.

Preuzimanje tijekom instalacije ne rješava licencna ograničenja niti zaobilazi moguća ograničenja komercijalne uporabe. [Uvjeti Porezne uprave](https://porezna-uprava.gov.hr/hr/uvjeti-koristenja/4066) ograničavaju komercijalnu uporabu sadržaja; u pregledanom XLSX paketu nije pronađena posebna licencna datoteka. Otvorena redistribucija skupa nije potvrđena. Ne objavljujte izvorni XLSX ili izvedeni CSV u repozitoriju niti ih preuzimajte u CI-ju. Sačuvajte lokalnu evidenciju URL-a, datuma, SHA256 i uvjeta uporabe, uključujući izvorni broj NN i višu stopu koje model ne sprema. Uključena su ignore pravila za lokalne XLSX/CSV datoteke; ona ne zamjenjuju pregled prije objave.

Ako se prava potvrde, mogući zaseban direktorij `database/reference-data/jls/2026/` trebao bi sadržavati SOURCE.md, zasebne uvjete/licencu podataka i manifest izvora, datuma, hashova i pretvorbi. To ostaje prijedlog: direktorij i podatkovni skup nisu dodani.

Ručni obrasci za JLS i porezne stope ostaju nepromijenjeni: kroz obrazac poreznih stopa mogu se evidentirati provjerena stopa, IBAN i razdoblje koje se ne preklapa s postojećim. Početna naredba nije alat za kasnija ažuriranja. **Postojeće ograničenje:** i početni uvoz i raniji seeder ostavljaju `valid_until = null`; ručni obrazac odbija preklapanje, a sadašnje rute nemaju uređivanje završetka stare stope. Za dodavanje nasljedne stope takvoj otvorenoj stopi treba zasebno odobren postupak zatvaranja prethodnog intervala uz backup, bez gubitka povijesti. Ovo izdanje ne uvodi takvu izmjenu poslovne logike.

## Testovi

```sh
php artisan config:clear --no-interaction
php vendor/bin/phpunit
composer validate --strict
npm run build
```

phpunit.xml prisilno postavlja testing, SQLite :memory:, array sesije/cache/poštu i sync queue. Testovi stvaraju vlastitu shemu u memoriji; ne koriste instalacijsku bazu. Nemojte pokretati testove s predmemoriranom konfiguracijom koja upućuje na radnu bazu.

GitHub Actions izvršava provjeru izolacije, PHPUnit i frontend build na push i pull_request, bez pristupa privatnom repozitoriju, CSV-u ili stvarnim evidencijama. APP_KEY u CI-ju generira se za pojedino pokretanje i nije pohranjen u repozitoriju.

## Sintetički testni podaci

Imena, ulice i nazivi u testovima su izmišljeni. OIB-i su generirani prema ISO 7064 MOD 11,10, prema [postupku REGOS-a](https://regos.hr/app/uploads/2018/07/KONTROLA-OIB-a.pdf).

| Uloga u JOPPD testovima | Sintetički OIB |
| --- | --- |
| Organizacija A | 00000000010 |
| Organizacija B | 00000000028 |
| Primatelj A | 00000000036 |
| Primatelj B | 00000000044 |

Brisanje koristi iste sintetičke vrijednosti, ne stvarne osobe. Za sve uloge testnog računa koristi se HR3100000000000000001: prolazi MOD97 i instalirani HUB3 validator, uz namjerno izmišljeni bankovni dio. Ne upotrebljavajte testne identifikatore za stvarna plaćanja. Generiranje i valjana kontrolna znamenka ne dokazuju da je neki identifikator službeno nedodijeljen; takva potvrda nije pribavljena.

## Datoteke za distribuciju i licenca

Ne objavljujte .env, baze, privatne CSV-ove, logove, sigurnosne kopije, generirane izvještaje ni runtime ključeve. vendor, node_modules i public/build reproduciraju se iz lock datoteka i nisu sadržaj prvog commita.

Vlastiti kod aplikacije licenciran je pod [MIT licencom](LICENSE): Copyright (c) 2026 Luko Del Ponte. MIT ne mijenja niti zamjenjuje licence vanjskih biblioteka, fontova i drugih preuzetih resursa. Službeni porezni podaci, XSD kopije i PNG/ICO grafika navedeni u ovom README-u nisu ovim odobrenjem licencirani pod MIT-om.

### Licence ovisnosti i vanjskih resursa

Provjera 2026-11-08 obuhvatila je deklarirane licence 132 instalirana Composer paketa i 143 zapisa u npm lock datoteci, uključujući opcionalne platformne pakete. Nijedan od tih zapisa nema praznu licencnu deklaraciju. Metapodaci su inventar, a ne zasebna potvrda svih obveza redistribucije.

| Skup | Utvrđene licence / stanje |
| --- | --- |
| Composer | MIT, BSD-3-Clause, Apache-2.0, LGPL-3.0-only, LGPL-3.0-or-later te BSD/GPL alternative kod nette/schema i nette/utils. |
| LGPL primjeri | globalcitizen/php-iban: LGPL-3.0-only; 14 tecnickcom paketa: LGPL-3.0-or-later. Njihove LICENSE datoteke ostaju mjerodavne. |
| npm | MIT, ISC, Apache-2.0, MPL-2.0, BSD-3-Clause, 0BSD i alternativa MIT OR CC0-1.0. |
| Bootstrap Icons | MIT; izvorna obavijest i licenca nalaze se u node_modules/bootstrap-icons/LICENSE. To ne daje licencu zasebnim projektnim PNG slikama. |
| Instrument Sans | [SIL OFL 1.1, izvorni tekst i nositelji prava](https://raw.githubusercontent.com/google/fonts/main/ofl/instrumentsans/OFL.txt); font se pri buildu pribavlja preko Bunny integracije. Nije pod MIT licencom aplikacije. |
| resources/joppd/xsd/*.xsd | Institucionalno podrijetlo djelomično potvrđeno usporedbom sa službenim ZIP-om; dopuštenje redistribucije i uvjeti za konkretne kopije nisu potvrđeni. |
| public/images/*.png i public/favicon.ico | Potvrđeno je da su svih sedam PNG datoteka i favicon.ico izrađeni za ovu aplikaciju pomoću OpenAI alata; nisu automatski obuhvaćeni MIT licencom vlastitog koda. |

Zadržite izvorne licence i copyright obavijesti ovisnosti. Pri distribuciji vendor datoteka, izmijenjenih biblioteka ili produkcijskog builda poštujte uvjete pripadajućih LGPL, MPL, OFL i ostalih licenci. Za fontove i ikone uključite odgovarajuće izvorne licencne tekstove i obavijesti uz distribuirane resurse; poveznica u README-u sama nije zamjena za potrebne priloge.

### Provjera JOPPD XSD shema — 2026-11-08

Službeni izvor: [ePorezna_Schemas.zip](https://e-porezna.porezna-uprava.hr/Upute/G2B/ePorezna_Schemas.zip), povezan sa [službenog portala ePorezna](https://e-porezna.porezna-uprava.hr/Pages/Ousluzi.aspx). Arhiv je ponovno preuzet 2026-11-08. Uspoređena je točno podmapa `ePorezna_Schemas/JOPPD/`, ne istoimene datoteke drugih obrazaca. SHA256 preuzetog ZIP-a: `B1FCE1B67DB21E2DC36D2FC0279502ACB12A73D087BF5785942B0E29B6D3FF79`.

Nazivi, deklarirane verzije, ID-jevi i targetNamespace odgovaraju za svih pet datoteka, ali to ne znači da im je sadržaj jednak. **XSD sheme nisu obuhvaćene MIT licencom aplikacije.**

| Datoteka | Verzija lokalna / službena | SHA256 lokalni | SHA256 službeni | Podudaranje |
| --- | --- | --- | --- | --- |
| MetapodaciTipovi-v2-0.xsd | 2.0 / 2.0 | D0952ED4387557F224C471B04F6D2D6903597B56CAB6FEA48B361A9D99587564 | D0952ED4387557F224C471B04F6D2D6903597B56CAB6FEA48B361A9D99587564 | Da |
| ObrazacJOPPD-v1-1.xsd | 1.1 / 1.1 | E5A303EA2247C93E4759CCE7E89FB1F5A130B2842C89A6122414319B51F4EB55 | E5A303EA2247C93E4759CCE7E89FB1F5A130B2842C89A6122414319B51F4EB55 | Da |
| ObrazacJOPPDmetapodaci-v1-1.xsd | 1.1 / 1.1 | E4B7133B41AA63A84ADF1C495244E0D204AECBB23D1A86FFA1E5C8FFE91C787D | E4B7133B41AA63A84ADF1C495244E0D204AECBB23D1A86FFA1E5C8FFE91C787D | Da |
| ObrazacJOPPDtipovi-v1-1.xsd | 1.1 / 1.1 | 2F634432DCCACCC12020893F9FF675986F0DE3A8E100F5DF1F9953EF40129C1A | 91EAC0FABD8CEDC679BDAD5F4ED8F1A87F282142AE385BE23E2DD6A664604577 | Ne |
| TemeljniTipovi-v2-1.xsd | 2.1 / 2.1 | 333802CCFDA9072644DE59544243AB651B221FA1DAD340D4C0FAE652456FBE6E | F29CCFB86B34A9523344E68CCF7A6A08A054BFA6E6D83346BBBDDEB500A275DA | Ne |

Razlike:

- `ObrazacJOPPDtipovi-v1-1.xsd`: stvarne razlike validacijskih pravila. Lokalni P129 koristi `temeljni:tNonNegativeDecimal15-2`, službeni `temeljni:tDecimal15-2`. Službena kopija ima proširene enumeracije: tOznakaPodnositelja 13 → 15, tOznakaNeoporezivogPrimitka 72 → 99, tOznakaStjecatelja 89 → 94, tOznakaPrimici 238 → 253 i tOznakaInvaliditet 3 → 4 (lokalno → službeno).
- `TemeljniTipovi-v2-1.xsd`: razlikuje se dokumentacijski primjer telefonskog broja, anonimiziran u javnoj kopiji. Nakon uklanjanja anotacija XML kanonikalizacija potvrđuje jednaku strukturu validacijskih pravila.
- Preostale tri datoteke identične su službenim JOPPD kopijama, uključujući SHA256.

Uvjeti: u ZIP-u s 464 zapisa nije pronađena licencna datoteka niti posebna dozvola u pregledanom tekstualnom sadržaju. Pronađene ključne riječi o dopuštenjima odnosile su se na pravila unosa podataka, ne na redistribuciju. [Opći uvjeti Porezne uprave](https://porezna-uprava.gov.hr/hr/uvjeti-koristenja/4066) ograničavaju komercijalnu uporabu sadržaja i navode obuhvat domene porezna-uprava.gov.hr. Primjenjivost te odredbe na ovaj ZIP na starijoj ePorezna domeni nije izričito razjašnjena; na pregledanom portalu ePorezna nije pronađena zasebna dozvola koja bi to riješila. Ne može se potvrditi slobodno prenošenje shema samo uz navođenje izvora.

**Mješovito licenciranje:** izvorni programski kod autora Luke Del Pontea objavljen je pod MIT licencom. Službene JOPPD XSD sheme nisu obuhvaćene MIT licencom aplikacije. Njihov izvor je Ministarstvo financija – Porezna uprava Republike Hrvatske; uključene su radi tehničke validacije i interoperabilnosti. Prava na službene datoteke ostaju odgovarajućim nositeljima. Uvjeti njihove daljnje redistribucije nisu zasebno potvrđeni; navođenje izvora nije zamjena za dopuštenje.

**Odluka za pripremu v2.0.2:** svih pet postojećih datoteka ostaje na istim putanjama i bez promjene sadržaja. Poznata razlika P129 ne blokira ovo izdanje prema odluci održavatelja; lokalna kopija i dalje odbija negativni P129, a novije službene enumeracije nisu automatski preuzete. Globalna XSD validacija ostaje uključena, uz postojeću dokumentiranu prilagodbu P151=00. Ova odluka ne potvrđuje prava redistribucije niti prihvat obrasca u ePoreznoj.

Korisnici trebaju provjeravati postoje li novije službene verzije prije stvarnih predaja i nadogradnji; jednaki naziv i verzijski atribut nisu dovoljni. Prelazak na drugu reviziju zahtijeva zaseban pregled i ponavljanje JOPPD testova. Datoteke u ovom koraku nisu zamijenjene ili premještene.

Potvrđeno je da su svih sedam PNG datoteka u `public/images/` i `public/favicon.ico` izrađeni za ovu aplikaciju pomoću OpenAI alata. Ova potvrda podrijetla ne dodjeljuje im automatski MIT licencu aplikacije.

Prije javne redistribucije neriješenih resursa pribavite dokumentiranu potvrdu autora/nositelja prava ili uvjete službenog izdavatelja koji izričito pokrivaju konkretne datoteke i eventualne prilagodbe. Za XSD je alternativa zasebno korisnikovo pribavljanje pod potvrđenim uvjetima. XSD datoteke nisu automatski uklonjene jer bi to promijenilo JOPPD validaciju i testove; takav zahvat zahtijeva zasebnu odluku. MIT licenciranje vlastitog koda završeno je; pravna neizvjesnost vanjskih resursa ostaje evidentirana. Za XSD vrijedi gore navedena odluka o zadržavanju u pripremi v2.0.2.

## Sigurnosna provjera nakon sanacije (2026-11-08)

Ciljano je nadograđeno pet zaključanih Symfony paketa, bez promjene composer.json, Laravela 13.35.0 ili poslovne logike.

| Paket | Prije | Nakon |
| --- | --- | --- |
| symfony/http-foundation | 8.1.8 | 8.1.11 |
| symfony/http-kernel | 8.1.8 | 8.1.11 |
| symfony/mime | 8.1.7 | 8.1.10 |
| symfony/routing | 8.1.8 | 8.1.11 |
| symfony/yaml | 8.1.9 | 8.1.11 |

Sve prijave odnosile su se na verzije u korištenoj grani 8.1 ispod 8.1.11, osim MIME prijave koja je ispravljena u 8.1.10:

| Paket | Razriješeni identifikator |
| --- | --- |
| http-foundation | [CVE-2026-118514](https://symfony.com/cve-2026-118514) |
| http-foundation | [GHSA-3h27-hjv7-fqjx](https://github.com/symfony/symfony/security/advisories/GHSA-3h27-hjv7-fqjx) |
| http-foundation | [GHSA-xg2q-4rrq-657q](https://github.com/symfony/symfony/security/advisories/GHSA-xg2q-4rrq-657q) |
| http-foundation | [GHSA-5mx8-mwrp-5693](https://github.com/symfony/symfony/security/advisories/GHSA-5mx8-mwrp-5693) |
| http-kernel | [GHSA-wg5q-m2ww-f3c9](https://github.com/symfony/symfony/security/advisories/GHSA-wg5q-m2ww-f3c9) |
| mime | [CVE-2026-45070](https://symfony.com/cve-2026-45070) |
| routing | [CVE-2026-118516](https://symfony.com/cve-2026-118516) |
| yaml | [GHSA-hwfg-qwvh-2wfx](https://github.com/symfony/symfony/security/advisories/GHSA-hwfg-qwvh-2wfx) |

CI koristi `composer audit --locked --abandoned=report`. Ako nema sigurnosnih ranjivosti, naredba vraća **izlazni kod 0**, a napuštene pakete i dalje prikazuje kao upozorenje. Opcija report odnosi se samo na napuštenost; sigurnosne prijave i dalje uzrokuju neuspjeh audita i nisu ignorirane.

bigfish/pdf417 0.3.0 produkcijski se koristi preko leongrdic/pdf417-hub3 1.0.0 za SVG PDF417 barkodove. [Packagist](https://packagist.org/packages/bigfish/pdf417) ga označava napuštenim, bez predložene zamjene; audit za taj paket ne navodi sigurnosnu prijavu.

Zadržana je postojeća funkcionalna HUB3/PDF417 implementacija preko `leongrdic/pdf417-hub3` i `bigfish/pdf417`; zamjena biblioteke nije provedena niti je planirana ovim korakom. Prema provedenom auditu nema poznatih prijavljenih ranjivosti za tu PDF417 ovisnost, ali napuštenost ostaje rizik održavanja. Povremeno i prije svake objave ponovite `composer audit --locked --abandoned=report`; odsutnost prijava nije jamstvo odsutnosti ranjivosti.

Pri samostalnoj CLI provjeri na PHP-u 8.4 postojeći BigFish SVG renderer emitira deprecation upozorenje `DOMImplementation::createDocument(): Passing null to parameter #2 ($qualifiedName) of type string is deprecated` (vendorov SvgRenderer.php). Generiranje oba sintetička barkoda pritom uspijeva i vraća valjan SVG. Ovo je poznat kompatibilnosni dug napuštene biblioteke; vendorov kod i postavke prijavljivanja pogrešaka nisu mijenjani.

Provjere nakon sanacije: composer validate --strict i check-platform-reqs prolaze; 15/15 PHPUnit testova prolazi na SQLite :memory:; 63 PHP datoteke prolaze sintaksu; frontend build prolazi. Na novoj privremenoj sintetičkoj bazi provjereni su ručni unos, obračun, HUB3/PDF417 i ponovljivi JOPPD XML. Fizički ispis i prihvat u ePoreznoj nisu provjereni.

Ostaju prethodno dokumentirana JOPPD P151=00 prilagodba i neblokirajuće build upozorenje za neobavezni fontaine. MIT licenca za vlastiti kod je odobrena; grafika ima potvrđeno OpenAI podrijetlo i nije automatski pod MIT licencom, a prava redistribucije XSD resursa ostaju nepotvrđena.


Završna provjera pripreme v2.0.2 (2026-11-08): **42/42 testna slučaja i 193 asercije prolaze**, uključujući svih 15 ranijih testova; nema neuspješnih asercija ni iznimaka. Uvoz se provjerava isključivo sintetičkim XLSX knjigama i SQLite bazom u memoriji. Dodatno je bez baze i bez kopiranja u distribuciju potvrđeno čitanje 556 redaka ranije preuzete službene tablice. Prolaze sintaksa 65 PHP datoteka, Pint, `composer validate --strict`, `composer audit --locked --abandoned=report` i frontend build. Audit ne navodi sigurnosne prijave, uz upozorenje za napušteni `bigfish/pdf417`; build zadržava poznato neblokirajuće upozorenje za neobavezni `fontaine`. Svih pet XSD hashova i putanja nepromijenjeno je u odnosu na početak ovog koraka. Provjeren je i doslovni tekst podebljanog upozorenja nakon Markdown renderiranja.
