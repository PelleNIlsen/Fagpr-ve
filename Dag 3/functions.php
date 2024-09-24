<?php
// Add custom Theme Functions here

// Tilpasset søkefilter for nettsiden
function custom_search_filter($query)
{
    // Kjør bare på frontend-søk og ikke i admin-panelet
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        error_log("Processing front-end search. Search term: " . $query->get('s'));

        $search_term = $query->get('s');
        // Sett post-types som skal søkes i
        $query->set('post_type', array('product', 'post', 'page'));

        // Hent alle resultater
        $matched_posts = get_posts(array(
            's' => $search_term,
            'post_type' => array('product', 'post', 'page'),
            'posts_per_page' => -1,
        ));

        error_log("Matched posts count: " . count($matched_posts));

        // Sorter resultatene
        $ordered_posts = custom_sort_search_results($matched_posts, $search_term);
        $ordered_post_ids = wp_list_pluck($ordered_posts, 'ID');

        error_log("Ordered post IDs: " . implode(', ', $ordered_post_ids));

        // Sett spørringen til å bruke våre sorterte resultater
        $query->set('post__in', $ordered_post_ids);
        $query->set('orderby', 'post__in');
    }
}

// Når WordPress er i ferd med å utføre en spørring for å hente innlegg (pre_get_posts), kjører funksjonen custom_search_filter, som bestemmer hvilke resultater WordPress skal hente ut, og denne funksjonen kjører helt sist i prosessen (999), slik at den ikke blir overskrevet av noen andre plugins eller temaet som kan påvirket søkefunksjonen.
add_action('pre_get_posts', 'custom_search_filter', 999);

// Tilpasset sorteringsfunksjon 
function custom_sort_search_results($posts, $search_term)
{
    custom_error_log("custom_sort_search_results called");

    // Dette er ID'ene til tjeneste-sidene som skal sorteres over blog-innlegg
    $specific_page_ids = array(56, 1389, 58, 60, 62);
    $sorted = array('products' => [], 'specific_pages' => [], 'blog_posts' => [], 'remaining_pages' => []);

    // Kategoriser innlegg basert på type
    foreach ($posts as $post) {
        $post_type = get_post_type($post->ID);
        if ($post_type == 'product') {
            $sorted['products'][] = $post;
        } elseif ($post_type == 'page' && in_array($post->ID, $specific_page_ids)) {
            $sorted['specific_pages'][] = $post;
        } elseif ($post_type == 'post') {
            $sorted['blog_posts'][] = $post;
        } elseif ($post_type == 'page') {
            $sorted['remaining_pages'][] = $post;
        }
        custom_error_log("Added {$post_type}: " . $post->ID . " - " . $post->post_title);
    }

    // Sorter spesifikke sider for å matche rekkefølgen i $specific_page_ids
    usort($sorted['specific_pages'], function ($a, $b) use ($specific_page_ids) {
        return array_search($a->ID, $specific_page_ids) - array_search($b->ID, $specific_page_ids);
    });

    // Slå sammen alle sorterte resultater
    $ordered_posts = array_merge($sorted['products'], $sorted['specific_pages'], $sorted['blog_posts'], $sorted['remaining_pages']);
    custom_error_log("Total ordered posts: " . count($ordered_posts));
    return $ordered_posts;
}

// AJAX handler for lightbox-søk
function custom_lightbox_search()
{
    error_log("custom_lightbox_search called");

    if (!isset($_POST['search_term'])) {
        error_log("No search term provided in lightbox search");
        wp_send_json_error("No search term provided");
        return;
    }

    $search_term = sanitize_text_field($_POST['search_term']);
    error_log("Lightbox search term: " . $search_term);

    // Utfør søk
    $posts = get_posts(array(
        's' => $search_term,
        'post_type' => array('product', 'post', 'page'),
        'posts_per_page' => -1,
    ));

    error_log("Lightbox search matched posts: " . count($posts));

    // Sorter resultatene
    $sorted_posts = custom_sort_search_results($posts, $search_term);

    // Bygg HTML for søkeresultatene
    $html = '';
    foreach ($sorted_posts as $post) {
        $html .= '<div class="search-result">';
        $html .= '<h3><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></h3>';
        $html .= '<p>' . wp_trim_words($post->post_content, 20) . '</p>';
        $html .= '</div>';
        error_log("Added to lightbox results: " . $post->ID . " - " . $post->post_title);
    }

    error_log("Lightbox search completed, sending response");
    wp_send_json_success($html);
}

// Når en innlogget bruker sender en AJAX-forespørsel med action 'lightbox_search, kjører funksjonen custom_lightbox_search for å håndtere søket og retunere resultatene
add_action('wp_ajax_lightbox_search', 'custom_lightbox_search');
// Når en ikke-innlogget bruker sender en AJAX-forespørsel med action 'lightbox_search, kjører funksjonen custom_lightbox_search for å håndtere søket og retunere resultatene, slik at søkefunksjonaliteten virker for alle besøkende på nettstedet.
add_action('wp_ajax_nopriv_lightbox_search', 'custom_lightbox_search');

// Legg til JavaScript for lightbox-søk
function enqueue_lightbox_search_script()
{
    wp_enqueue_script('lightbox-search', get_stylesheet_directory_uri() . '/js/lightbox-search.js', array('jquery'), '1.0', true);
    wp_localize_script('lightbox-search', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    custom_error_log("Lightbox search script enqueued from child theme");
}

// Når WordPress laster inn scripts og stiler for frontend (wp_enqueue_scripts), kjører funksjonen enqueue_lightbox_search_script for å legge til denne egendefinerte lightbox-søkescriptet, slik at det blir tilgjengelig på nettsiden.
add_action('wp_enqueue_scripts', 'enqueue_lightbox_search_script');

// Skriv ut lightbox-søkescript
function output_lightbox_search_script()
{
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Lightbox search script loaded');
            $('.searchform').on('submit', function(e) {
                e.preventDefault();
                console.log('Lightbox search form submitted');
                var searchInput = $(this).find('input[type="search"], input[name="s"]');
                if (searchInput.length > 0) {
                    var searchTerm = searchInput.val();
                    console.log("Sending AJAX request for search term: " + searchTerm);
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'lightbox_search',
                            search_term: searchTerm
                        },
                        success: function(response) {
                            console.log("AJAX response received", response);
                            if (response.success) {
                                $('#lightbox-search-results').html(response.data);
                            } else {
                                console.error("AJAX request failed: " + response.data);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX request failed: " + textStatus + ", " +
                                errorThrown);
                        }
                    });
                } else {
                    console.error('Search input not found in the form');
                }
            });
        });
    </script>
<?php
}

// Før WordPress avslutter genereringen av siden og skriver ut bunnteksten (wp_footer), kjører funksjonen ouput_lightbox_search_script for å legge til den nødvendige JavaScript-koden direkte i HTML-en, slik at lightbox-søkefunksjonaliten er klar til bruk på siden.
add_action('wp_footer', 'output_lightbox_search_script');

// Tilpasset Flatsome AJAX-søk for produkter
function custom_flatsome_ajax_search_products($suggestions)
{
    custom_error_log("custom_flatsome_ajax_search_products called");

    if (!isset($_GET['query'])) {
        custom_error_log("No search query found");
        return $suggestions;
    }

    $search_term = sanitize_text_field($_GET['query']);
    custom_error_log("Search term: " . $search_term);

    // Utfør søk
    $posts = get_posts(array(
        's' => $search_term,
        'post_type' => array('product', 'post', 'page'),
        'posts_per_page' => -1,
    ));

    custom_error_log("Total matched posts: " . count($posts));

    // Sorter resultatene
    $sorted_posts = custom_sort_search_results($posts, $search_term);

    // Bygg nye forslag basert på sorterte resultater
    $new_suggestions = array();
    foreach ($sorted_posts as $post) {
        $post_type = get_post_type($post->ID);
        $suggestion = array(
            'type' => ($post_type == 'page') ? 'Page' : (($post_type == 'post') ? 'Blog Post' : ucfirst($post_type)),
            'id' => $post->ID,
            'value' => html_entity_decode($post->post_title),
            'url' => get_permalink($post->ID),
        );

        $image = get_the_post_thumbnail_url($post->ID, 'thumbnail');
        if ($image) {
            $suggestion['img'] = $image;
        }

        $new_suggestions[] = $suggestion;
        custom_error_log("Added to suggestions: " . $post->ID . " - " . $post->post_title . " (Type: " . $suggestion['type'] . ")");
    }

    custom_error_log("New suggestions: " . print_r($new_suggestions, true));

    return $new_suggestions;
}

// Før Flatsome-temaet genrerer AJAX-søkeresultater for produkter (flatsome_ajax_search_products), kjører funksjonen custom_flatsome_ajax_search_products for å sortere resultatene etter ønsket rekkefølge.
add_filter('flatsome_ajax_search_products', 'custom_flatsome_ajax_search_products', 10, 1);

// Funksjon for å logge feilmeldinger
function custom_error_log($message)
{
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, ABSPATH . '/wp-content/debug.log');
}
