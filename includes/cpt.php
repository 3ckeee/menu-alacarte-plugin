<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mlac_create_menu_alacarte_cpt() {
    $labels = array(
        'name'                  => _x( 'Menu À la Carte', 'Post Type General Name', 'mlac-textdomain' ),
        'singular_name'         => _x( 'Menu Item', 'Post Type Singular Name', 'mlac-textdomain' ),
        'menu_name'             => __( 'Menu À la Carte', 'mlac-textdomain' ),
        'name_admin_bar'        => __( 'Menu Item', 'mlac-textdomain' ),
        'archives'              => __( 'Menu Item Archives', 'mlac-textdomain' ),
        'attributes'            => __( 'Menu Item Attributes', 'mlac-textdomain' ),
        'all_items'             => __( 'All Menu Items', 'mlac-textdomain' ),
        'add_new_item'          => __( 'Add New Menu Item', 'mlac-textdomain' ),
        'add_new'               => __( 'Add New', 'mlac-textdomain' ),
        'new_item'              => __( 'New Menu Item', 'mlac-textdomain' ),
        'edit_item'             => __( 'Edit Menu Item', 'mlac-textdomain' ),
        'update_item'           => __( 'Update Menu Item', 'mlac-textdomain' ),
        'view_item'             => __( 'View Menu Item', 'mlac-textdomain' ),
        'view_items'            => __( 'View Menu Items', 'mlac-textdomain' ),
        'search_items'          => __( 'Search Menu Item', 'mlac-textdomain' ),
        'not_found'             => __( 'Not found', 'mlac-textdomain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'mlac-textdomain' ),
    );
    $args = array(
        'label'                 => __( 'Menu À la Carte', 'mlac-textdomain' ),
        'labels'                => $labels,
        // We'll remove the default editor since we use meta boxes.
        'supports'              => array( 'title', 'thumbnail' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-food',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
    );
    register_post_type( 'menu_alacarte', $args );
}
add_action( 'init', 'mlac_create_menu_alacarte_cpt', 0 );