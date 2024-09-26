# Dokumentasjon for kategorifiltrering av blogginnlegg
## Oversikt
Denne JavaScript løsning implementerer en funksjonalitet for å filtrere blogginnlegg basert på kategorier. Den lar brukeren klikke på kategoriknapper for å vise innlegg som tilhører den valgte kategorien, samt en "Se alle" knapp for å vise alle innleggene.

## Hovedfunksjon `sortCategories()`
Denne funksjonen er hovedfunksjonen som setter opp hele filtreringssystemet.
### Trinn 1: Hente elementer
- Henter alle filtreringsknapper `.kat-filter a`
- Henter alle blogginnlegg `.post-item`
- Avslutter funksjonen hvis ingen knapper blir funnet

### Trinn 2: Definere `selectCategory(cat)` funksjon
Denne indre funksjonen håndterer visningen av innlegg basert på valgt kategori.
- Hvis "Se alle" er valgt, vises alle innlegg
- For andre kategorier
  - Går gjennom hvert innlegg
  - Finner kategori-etiketten `.cat-label`
  - Sammenligner innleggets kategori(er) med den valgte kategorien
  - Viser innlegget hvis det matcher, skjuler det hvis ikke

### Trinn 3: Sette opp Eventlisteners
For hver kategoriknapp:

- Legger til en klikk-hendelseslytter
- Forhindrer standard lenkeoppførsel
- Håndterer "Se alle" knappen spesielt
- Finner kategoriclassen for knappen
- Konverterer klassenavnet til kategorinavnet
- Kaller `selectCategory()` med det riktige kategorinavnet

## Oppstart
Koden kjøres når DOM-innholdet er fullstendig lastet, ved hjelp av `DOMContentLoaded`-eventet.

## Viktige detaljer
1. Kategoriknapper må ha en klasse som starter med `kat-filter_`
2. "Se alle" knappen må ha klassen `kat-filter_show_all`
3. Hvert blogginnlegg må ha en `.cat-label` med kategorinavnene
4. Kategorinavnene er ikke case-sensitive og ekstra mellomrom ignoreres

## Bruk
For å bruke denne koden:
1. Sørg for at HTML-strukturen matcher kravene (kategoriknapper, innleggsstruktur)
2. Inkluder skriptet i HTML-filen
3. Koden vil automatisk sette opp filtreringsfunksjonaliteten når siden lastes.

## Vedlikehold
Ved endringer i HTML-struktur eller klassenavn, oppdater følgende:
- Selektor for knappen og innlegg i `sortCategories()`
- Klassenavn og selektorer i Eventlistener
- Strukturen for `.cat-label` hvis den endres
