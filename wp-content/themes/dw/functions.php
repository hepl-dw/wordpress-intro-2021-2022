<?php

require_once(__DIR__ . '/CustomSearchQuery.php');
// require_once(__DIR__ . '/Menus/PrimaryMenuWalker.php');
require_once(__DIR__ . '/Menus/PrimaryMenuItem.php');

// Lancer la session PHP

add_action('init', 'dw_boot_theme', 1);

function dw_boot_theme()
{
    load_theme_textdomain('dw', __DIR__ . '/locales');

    if(! session_id()) {
        session_start();
    }
}

// Désactiver l'éditeur "Gutenberg" de Wordpress
add_filter('use_block_editor_for_post', '__return_false');

// Activer les images sur les articles
add_theme_support('post-thumbnails');

// Enregistrer un seul custom post-type pour "nos voyages"
register_post_type('trip', [
    'label' => 'Voyages',
    'labels' => [
        'name' => 'Voyages',
        'singular_name' => 'Voyage',
    ],
    'description' => 'Tous les articles qui décrivent un voyage',
    'public' => true,
    'has_archive' => true,
    'menu_position' => 5,
    'menu_icon' => 'dashicons-palmtree',
    'supports' => ['title','editor','thumbnail'],
    'rewrite' => ['slug' => 'voyages'],
]);

register_post_type('message', [
    'label' => 'Messages de contact',
    'labels' => [
        'name' => 'Messages de contact',
        'singular_name' => 'Message de contact',
    ],
    'description' => 'Les messages envoyés par les utilisateurs via le formulaire de contact.',
    'public' => false,
    'show_ui' => true,
    'menu_position' => 15,
    'menu_icon' => 'dashicons-buddicons-pm',
    'capabilities' => [
        'create_posts' => false,
    ],
    'map_meta_cap' => true,
]);

// Enregistrer une taxonomie (façon de classifier des posts) pour les pays où des voyages ont eu lieu.

register_taxonomy('country', ['trip'], [
    'labels' => [
        'name' => 'Pays',
        'singular_name' => 'Pays',
    ],
    'description' => 'Pays visités et exploités dans nos récits de voyage.',
    'public' => true,
    'hierarchical' => true,
]);

// Récupérer les trips via une requête Wordpress
function dw_get_trips($count = 20, $search = null)
{
    // 1. on instancie l'objet WP_Query
    $trips = new DW_CustomSearchQuery([
        'post_type' => 'trip',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $count,
        's' => strlen($search) ? $search : null,
    ]);

    // 2. on retourne l'objet WP_Query
    return $trips;
}

// Enregistrer les menus de navigation

register_nav_menu('primary', 'Emplacement de la navigation principale de haut de page');
register_nav_menu('footer', 'Emplacement de la navigation de pied de page');

// Définition de la fonction retournant un menu de navigation sous forme d'un tableau de liens de niveau 0.

function dw_get_menu_items($location)
{
    $items = [];

    // Récupérer le menu qui correspond à l'emplacement souhaité
    $locations = get_nav_menu_locations();

    if(! ($locations[$location] ?? false)) {
        return $items;
    }

    $menu = $locations[$location];

    // Récupérer tous les éléments du menu en question
    $posts = wp_get_nav_menu_items($menu);

    // Traiter chaque élément de menu pour le transformer en objet
    foreach($posts as $post) {
        // Créer une instance d'un objet personnalisé à partir de $post
        $item = new PrimaryMenuItem($post);

        // Ajouter cette instance soit à $items (s'il s'agit d'un élément de niveau 0), soit en tant que sous-élément d'un item déjà existant.
        if(! $item->isSubItem()) {
            // Il s'agit d'un élément de niveau 0, on l'ajoute au tableau
            $items[] = $item;
            continue;
        }

        // Ajouter l'instance comme "enfant" d'un item existant
        foreach($items as $existing) {
            if(! $existing->isParentFor($item)) continue;
            
            $existing->addSubItem($item);
        }
    }

    // Retourner les éléments de menu de niveau 0
    return $items;
}

// Enregistrer le traitement du formulaire de contact personnalisé.

add_action('admin_post_submit_contact_form', 'dw_handle_submit_contact_form');

function dw_handle_submit_contact_form()
{
    if(! dw_verify_contact_form_nonce()) {
        // C'est pas OK.
        // TODO : afficher un message d'erreur "unauthorized"
        return;
    }

    $data = dw_sanitize_contact_form_data();

    if($errors = dw_validate_contact_form_data($data)) {
        $_SESSION['feedback_contact_form'] = [
            'success' => false,
            'data' => $data,
            'errors' => $errors,
        ];

        return wp_redirect($_POST['_wp_http_referer']);
    }
    
    // Stocker en base de données
    $id = wp_insert_post([
        'post_type' => 'message',
        'post_title' => 'Message de ' . $data['firstname'] . ' ' . $data['lastname'],
        'post_content' => $data['message'],
        'post_status' => 'publish',
    ]);

    // Envoyer un mail
    $content = 'Bonjour, un nouveau message de contact a été envoyé.<br />';
    $content .= 'Pour le visualiser : ' . get_edit_post_link($id);

    wp_mail(get_bloginfo('admin_email'), 'Nouveau message', $content);

    // Tout est OK, afficher le feedback positif
    $_SESSION['feedback_contact_form'] = [
        'success' => true,
    ];

    return wp_redirect($_POST['_wp_http_referer']);
}

function dw_verify_contact_form_nonce()
{
    $nonce = $_POST['_wpnonce'];

    return wp_verify_nonce($nonce, 'nonce_check_contact_form');
}

function dw_sanitize_contact_form_data()
{
    return [
        'firstname' => sanitize_text_field($_POST['firstname'] ?? null),
        'lastname' => sanitize_text_field($_POST['lastname'] ?? null),
        'email' => sanitize_email($_POST['email'] ?? null),
        'phone' => sanitize_text_field($_POST['phone'] ?? null),
        'message' => sanitize_text_field($_POST['message'] ?? null),
        'rules' => $_POST['rules'] ?? null
    ];
}

function dw_validate_contact_form_data($data)
{
    $errors = [];

    $required = ['firstname','lastname','email','message'];
    $email = ['email'];
    $accepted = ['rules'];

    foreach($data as $key => $value) {
        if(in_array($key, $required) && ! $value) {
            $errors[$key] = 'required';
            continue;
        }

        if(in_array($key, $email) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$key] = 'email';
            continue;
        }

        if(in_array($key, $accepted) && $value !== '1') {
            $errors[$key] = 'accepted';
            continue;
        }
    }

    return $errors ?: false;
}

function dw_get_contact_field_value($field)
{
    if(! isset($_SESSION['feedback_contact_form'])) {
        return '';
    }

    return $_SESSION['feedback_contact_form']['data'][$field] ?? '';
}

function dw_get_contact_field_error($field)
{
    if(! isset($_SESSION['feedback_contact_form'])) {
        return '';
    }

    if(! isset($_SESSION['feedback_contact_form']['errors'][$field])) {
        return '';
    }

    return '<p class="form__error">Problème : ' . $_SESSION['feedback_contact_form']['errors'][$field] . '</p>';
}

// Utilitaire pour charger un fichier compilé par mix, avec cache bursting.

function dw_mix($path)
{
    $path = '/' . ltrim($path, '/');

    // Checker si le fichier demandé existe bien, sinon retourner NULL
    if(! realpath(__DIR__ . '/public' . $path)) {
        return;
    }

    // Check si le fichier mix-manifest existe bien, sinon retourner le fichier sans cache-bursting
    if(! ($manifest = realpath(__DIR__ . '/public/mix-manifest.json'))) {
        return get_stylesheet_directory_uri() . '/public' . $path;
    }

    // Ouvrir le fichier mix-manifest et lire le JSON
    $manifest = json_decode(file_get_contents($manifest), true);

    // Check si le fichier demandé est bien présent dans le mix manifest, sinon retourner le fichier sans cache-bursting
    if(! array_key_exists($path, $manifest)) {
        return get_stylesheet_directory_uri() . '/public' . $path;
    }

    // C'est OK, on génère l'URL vers la ressource sur base du nom de fichier avec cache-bursting.
    return get_stylesheet_directory_uri() . '/public' . $manifest[$path];
}

// On va se plugger dans l'exécution de la requête de recherche pour la contraindre à chercher dans les articles uniquement.

function dw_configure_search_query($query)
{
    if($query->is_search && ! is_admin() && ! is_a($query, DW_CustomSearchQuery::class)) {
        $query->set('post_type', 'post');
    }

    // Faire un système de filtres "custom" (sans passer par la méthode WP) :
    // if(is_archive() && isset($_GET['filter-country'])) {
    //     $query->set('tax_query', [
    //         ['taxonomy' => 'country', 'field' => 'slug', 'terms' => explode(',', $_GET['filter-country'])]
    //     ]);
    // }

    return $query;
}

add_filter('pre_get_posts', 'dw_configure_search_query');

// Fonction permettant d'inclure des composants et d'y injecter des variables locales (scope de l'appel de fonction)

function dw_include(string $partial, array $variables = [])
{
    extract($variables);

    include(__DIR__ . '/partials/' . $partial . '.php');
}
