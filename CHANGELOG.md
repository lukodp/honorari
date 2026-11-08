# Promjene

## 2.0.2 — priprema javne distribucije (neobjavljeno)

- Dodana naredba `jls:import-initial` za korisnikov lokalni službeni XLSX za 2026.: provjera 556 redaka, pregled, potvrda i transakcijski početni unos samo u prazne tablice.
- Sačuvani tekstualni identifikatori i Excel format šifri s vodećim nulama; postotni razlomci pretvaraju se u postojeću strukturu poreznih stopa.
- Zadržana pretpostavka postojećeg seedera: datum H koristi se kao `valid_from`, bez novog poreznog tumačenja ili promjene obračunskih formula.
- Dodani sintetički testovi uspješnog uvoza, odbijanja neispravnih/neočekivanih knjiga, popunjenih tablica i potpunog rollbacka te primjene uvezene stope u postojećem obračunu.
- Zadržano svih pet postojećih JOPPD XSD datoteka, putanje i globalna validacija; dodane provjere referenci i izvoza. Poznata razlika P129 dokumentirana je i po odluci održavatelja ne blokira pripremu izdanja.
- Dopunjene instalacijske upute i mješovito licenciranje: vlastiti kod MIT, službeni resursi izvan MIT-a s nepotvrđenim zasebnim uvjetima redistribucije.
- Službeni XLSX/CSV skup nije uključen; dodana ignore pravila. Nema promjene postojećih baza, migracija, ručnog unosa ni poslovne logike.


## 1.0.0 — priprema prve javne distribucije, 2026-11-08

Ova verzija je pripremljena za pregled i nije objavljeni GitHub Release.

- Neovisna distribucija bez stare Git povijesti i privatne konfiguracije.
- Evidencija organizacija i sudaca, porezne stope po datumima, obračuni i spremljene snimke.
- HUB3 nalozi, PDF417 i ispis; JOPPD pripreme i XML izvoz.
- Uključeni potvrđeni ispravci polja Organization i početka migracije za suce.
- Anonimizirani testni identiteti, adrese, OIB-i i IBAN-i.
- CSV s 556 JLS zapisa izostavljen dok se ne potvrde izvor i dopuštenje redistribucije; ostaje ručni unos i zaseban opcionalni uvoz.
- Hrvatski README, siguran .env.example i CI za izolirane testove i build.
- Vlastiti kod licenciran pod MIT licencom, Copyright (c) 2026 Luko Del Ponte; vanjski resursi zadržavaju vlastite licence i nerazriješena prava nisu obuhvaćena MIT-om.

- Ciljanim patch nadogradnjama pet Symfony paketa uklonjeno 8 sigurnosnih prijava; Laravel i poslovna logika nisu mijenjani. Ostaje audit upozorenje za napušteni bigfish/pdf417.
