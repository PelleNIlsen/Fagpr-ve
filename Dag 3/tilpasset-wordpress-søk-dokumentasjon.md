# Dokumentasjon for tilpasset WordPress-søk
Denne dokumentasjonen dekker to filer som sammen implementerer en tilpasset søkefunksjonalitet for Flatsome-temaet:
1. `functions.php`: PHP-kode for backend-funksjonalitet
2. `lightbox-search.js`: JavaScript-kode for frontend-funksjonalitet

## Del 1: functions.php
### Oversikt
Denne PHP-filen inneholder flere funksjoner som tilpasser og utvider WordPress sin innebygde søkefunksjonalitet.

### Hovedfunksjoner
1. `custom_search_filter($query)`
- Formål: Tilpasser standard WordPress-søk på frontend
- Funksjonalitet:
    - Begrenser søket til spesifikke post-types (product, post, page)
    - Henter og sorterer søkeresultater
    - Modifiserer hovedspørringen for å bruke de sorterte resultatene

2. `custom_sort_search_results($posts, $search_term)`
- Formål: Sortere søkeresultater i en spesfikk rekkefølge
- Funksjonalitet:
    - Kategorisere innlegg basert på type (product, spesifikke sider, blogginnlegg, andre sider)
    - Sortere spesfikke sider i en forhåndsdefinert rekkefølge.
 
3. `custom_lightbox_search()`
- Formål: Håndtere AJAX-forespørsler for lightbox-søk
- Funksjonalitet:
    - Utfører søk basert på innsendt search-term
    - Sorterer resultatene
    - Bygger HTML for søkeresultatene
    - Sender resultatet tilbake som JSON

4. `enqueue_lightbox_search_script()`
- Formål: Legger til JavaScript-filen for lightbox-søk
- Funksjonalitet:
    - Registrerer og queuer lightbox-search.js
    - Sender nødvendig data til JavaScript (AJAX URL)

5. `output_lightbox_search_script()`
- Formål: Skriver ut inline JavaScript for lightbox-søk
- Funksjonalitet:
    - Legger til JavaScript-kode direkte i footer for å håndtere søkeskjema-innsendinger
 
6. `custom_flatsome_ajax_search_products($suggestions)`
- Formål: Tilpasser Flatsome-temaets AJAX-produktsøk
- Funksjonalitet:
    - Utfører et tilpasset søk på tvers av produkter, innlegg og sider
    - Sorterer resultatene
    - Formaterer resultatene for Flatsome's AJAX-søk
 
### Hooks og Filters
- `add_action('pre_get_posts', 'custom_search_filter', 999);`
- `add_action('wp_ajax_lightbox_search', 'custom_lightbox_search');`
- `add_action('wp_ajax_nopriv_lightbox_search', 'custom_lightbox_search');`
- `add_action('wp_enqueue_scripts', 'enqueue_lightbox_search_script');`
- `add_action('wp_footer', 'output_lightbox_search_script');`
- `add_filter('flatsome_ajax_search_products', 'custom_flatsome_ajax_search_products', 10, 1);`

### Viktige detaljer
- Søket inkluderer produkter, innlegg og sider
- Spesifikke sider (ID: 56, 1389, 58, 60, 62) har høyere prioritet i søkeresultatene
- Det brukes en tilpasset feillogginsfunksjon (`custom_error_log`) for debugging

## Del 2: lightbox-search.js
### Oversikt
Denne JavaScript-filen implementerer frontend-funksjonaliteten for dynamisk søk i FlatSome-temaet.

### Hovedfunksjonalitet
1. Initialisering
    - Kjører når DOM-en er fullstendig lastet
    - Logger en melding for å bekrefte at skriptet er lastet.
2. Søkeforespørselhåndtering
    - Lytter på `keyup`-event i søkefeltet `.search-field`
    - Sender en AJAX-forespørsel til WordPress når søkeordet ikke er tomt
3. AJAX-forespørsel
    - Bruker `flatsome_ajax_search_products` action
    - Sender søkeordet til serveren
4. Resultatvisning
    - Funksjonen `displaySearchResults(results)` tar seg av visning av søkeresultater
    - Tømmer tidligere resultater
    - Bygger HTML for hvert resultat og legger det i resultat-containeren
    - Viser en melding hvis ingen resultater ble funnet

### Viktige detaljer
- Resultat-containeren må ha ID-en `#lightbox-search-results`
- Forventer at serveren returnerer et objekt med en `suggestions` array
- Hvert forslag (suggestion) bør ha egenskapene `url`, `value` og `type`

### Samspill mellom PHP og JavaScript
- PHP-koden setter opp backend-funksjonaliteten og registrerer nødvendige AJAX-handlinger
- JavaScript-koden sender forespørsler til disse AJAX-endepunktene og håndterer resultatvisningen på frontend
- `enqueue_lightbox_search_script()` i PHP sørger for at JavaScript-filen lastes og får tilgang til riktig AJAX-URL

### Vedlikehold og tilpasning
- Ved endringer i post-typer eller søkeprioriteringer, oppdater custom_sort_search_results() i PHP.
- For å endre resultatformatet, juster både PHP-koden i `custom_flatsome_ajax_search_products()` og JavaScript-koden i `displaySearchResults()`
- Ved endringer i Flatsome-temaet, sjekk kompatibiliteten med `flatsome_ajax_search_products` filter

### Feilsøking
- Sjekk WordPress-feilloggen og nettleserens konsoll for feilmeldinger
- Verifiser at AJAX-URL-en er korrekt i både PHP og JavaScript
- Kontroller at alle nødvendige HTML-elementer (søkefelt, resultat-container) eksisterer med riktige klasser/ID-er
