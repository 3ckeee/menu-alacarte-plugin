<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mlac_create_menu_category_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Menu Categories', 'taxonomy general name', 'mlac-textdomain' ),
        'singular_name'              => _x( 'Menu Category', 'taxonomy singular name', 'mlac-textdomain' ),
        'search_items'               => __( 'Search Menu Categories', 'mlac-textdomain' ),
        'all_items'                  => __( 'All Menu Categories', 'mlac-textdomain' ),
        'parent_item'                => __( 'Parent Menu Category', 'mlac-textdomain' ),
        'parent_item_colon'          => __( 'Parent Menu Category:', 'mlac-textdomain' ),
        'edit_item'                  => __( 'Edit Menu Category', 'mlac-textdomain' ),
        'update_item'                => __( 'Update Menu Category', 'mlac-textdomain' ),
        'add_new_item'               => __( 'Add New Menu Category', 'mlac-textdomain' ),
        'new_item_name'              => __( 'New Menu Category Name', 'mlac-textdomain' ),
        'menu_name'                  => __( 'Menu Categories', 'mlac-textdomain' ),
    );
    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'menu-category' ),
    );
    register_taxonomy( 'menu_category', 'menu_alacarte', $args );
}
add_action( 'init', 'mlac_create_menu_category_taxonomy', 0 );

function mlac_insert_default_menu_categories() {
    $default_terms = array(
        'Antipasti',
        'Niečo k vínu',
        'Zuppe',
        'Insalate',
        'Risotto',
        'Pasta',
        'Piatti di carne',
        'Piatti di pesce',
        'Contorno',
        'Dezerty',
        'Pinsa',
        'Detské menu'
    );
    foreach ( $default_terms as $term ) {
        if ( ! term_exists( $term, 'menu_category' ) ) {
            wp_insert_term( $term, 'menu_category' );
        }
    }
}
add_action( 'init', 'mlac_insert_default_menu_categories' );