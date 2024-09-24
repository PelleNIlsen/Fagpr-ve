jQuery(document).ready(function ($) {
  console.log("Lightbox search script loaded from child theme");

  // Fang opp Flatsome AJAX-søkeforespørsler
  $(document).on("keyup", ".search-field", function () {
    var searchTerm = $(this).val();
    console.log("Search term: " + searchTerm);

    // Fortsett kun hvis søkeordet ikke er tomt
    if (searchTerm.length > 0) {
      // Send AJAX-forespørsel til WordPress
      $.ajax({
        url: ajax_object.ajax_url,
        type: "GET",
        data: {
          action: "flatsome_ajax_search_products",
          query: searchTerm,
        },
        success: function (response) {
          console.log("AJAX response received", response);
          // Behandle og vis resultatene
          displaySearchResults(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error(
            "AJAX request failed: " + textStatus + ", " + errorThrown
          );
        },
      });
    }
  });

  // Funksjon for å vise søkeresultatene
  function displaySearchResults(results) {
    var resultsContainer = $("#lightbox-search-results");
    resultsContainer.empty(); // Tøm tidligere resultater

    // Sjekk om vi har resultater å vise
    if (results && results.suggestions && results.suggestions.length > 0) {
      // Gå gjennom hvert resultat og legg det til i containeren
      results.suggestions.forEach(function (item) {
        var resultHtml =
          '<div class="search-result">' +
          '<h3><a href="' +
          item.url +
          '">' +
          item.value +
          "</a></h3>" +
          "<p>Type: " +
          item.type +
          "</p>" +
          "</div>";
        resultsContainer.append(resultHtml);
      });
    } else {
      // Vis en melding hvis ingen resuktater ble funnet
      resultsContainer.html("<p>Ingen resultater funnet</p>");
    }
  }
});
