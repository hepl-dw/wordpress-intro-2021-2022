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

    if($locations[$location] ?? false) {
        $menu = $locations[$location];

        // Récupérer tous les éléments du menu en question
        $posts = wp_get_nav_menu_items($menu);

        // Traiter chaque élément de menu pour le transformer en objet
        foreach($posts as $post) {
            // Créer une instance d'un objet personnalisé à partir de $post
            $item = new PrimaryMenuItem($post);

            // Ajouter cette instance soit à $items (s'il s'agit d'un élément de niveau 0), soit en tant que sous-élément d'un item déjà existant.
            if($item->isSubItem()) {
                // Ajouter l'instance comme "enfant" d'un item existant
                foreach($items as $existing) {
                    if($existing->isParentFor($item)) {
                        $existing->addSubItem($item);
                    }
                }
            } else {
                // Il s'agit d'un élément de niveau 0, on l'ajoute au tableau
                $items[] = $item;
            }
        }
    }

    // Retourner les éléments de menu de niveau 0
    return $items;
}


