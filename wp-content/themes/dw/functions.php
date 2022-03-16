<?php

// require_once(__DIR__ . '/Menus/PrimaryMenuWalker.php');
require_once(__DIR__ . '/Menus/PrimaryMenuItem.php');

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

// Récupérer les trips via une requête Wordpress
function dw_get_trips($count = 20)
{
    // 1. on instancie l'objet WP_Query
    $trips = new WP_Query([
        'post_type' => 'trip',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $count,
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
        // TODO : afficher les erreurs de validation
        return;
    }
    
    // Stocker en base de données
    $id = wp_insert_post([
        'post_type' => 'message',
        'post_title' => 'Message de ' . $firstname . ' ' . $lastname,
        'post_content' => $message,
        'post_status' => 'publish',
    ]);

    // Envoyer un mail
    $content = 'Bonjour, un nouveau message de contact a été envoyé.<br />';
    $content .= 'Pour le visualiser : ' . get_edit_post_link($id);

    wp_mail('toon@whitecube.be', 'Nouveau message', $content);
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


