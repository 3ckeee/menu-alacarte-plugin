<?php
/*
Plugin Name: Menu À la Carte Plugin
Plugin URI: https://diverzitystudios.sk
Description: A plugin to manage à la carte menus via a custom post type with a fill-out form and custom taxonomy.
Version: 0.9.9
Author: Erik Kokinda
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin directories
define( 'MLAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MLAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include main functionality
require_once MLAC_PLUGIN_DIR . 'includes/cpt.php';
require_once MLAC_PLUGIN_DIR . 'includes/taxonomy.php';
require_once MLAC_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once MLAC_PLUGIN_DIR . 'includes/shortcode.php';


// Admin‐only styles (meta boxes, etc.)
function mlac_enqueue_admin_styles() {
    wp_enqueue_style( 'mlac-admin-style', MLAC_PLUGIN_URL . 'css/admin.css', array(), '0.9.9' );
}
add_action( 'admin_enqueue_scripts', 'mlac_enqueue_admin_styles' );

// Front‐end CSS & JS
function mlac_enqueue_scripts() {
    // Main front‐end CSS
    wp_enqueue_style( 'mlac-style', MLAC_PLUGIN_URL . 'css/style.css', array(), '0.9.9' );
    // Category / submenu animation
    wp_enqueue_script( 'mlac-category-animation', MLAC_PLUGIN_URL . 'js/category-animation.js', array( 'jquery' ), '0.9.9', true );
    // Allergens toggle
    wp_enqueue_script( 'mlac-allergens-toggle', MLAC_PLUGIN_URL . 'js/allergens-toggle.js', array( 'jquery' ), '0.9.9', true );
}
add_action( 'wp_enqueue_scripts', 'mlac_enqueue_scripts' );

// Load Google Fonts (Spectral + Cormorant Garamond)
function mlac_enqueue_google_fonts() {
    wp_enqueue_style(
        'mlac-google-fonts',
        'https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,200;1,300;1,400;1,500;1,600;1,700;1,800&family=Cormorant+Garamond:ital,wght@1,300&display=swap',
        array(),
        null
    );
}
add_action( 'wp_enqueue_scripts', 'mlac_enqueue_google_fonts' );

// Add viewport meta tag in <head>
function mlac_add_viewport_meta() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
}
add_action( 'wp_head', 'mlac_add_viewport_meta' );

// Add custom editor styles in admin for the plugin’s container
function mlac_custom_editor_styles() {
    echo '<style>
        .menu-alacarte-container {
            width: 90% !important;
            max-width: none !important;
            margin: 0 auto !important;
        }
    </style>';
}
add_action( 'admin_head', 'mlac_custom_editor_styles' );

// Force JPEG uploads at 100% quality
add_filter( 'jpeg_quality', function() { return 100; } );


 include_once plugin_dir_path(__FILE__) . 'includes/menu-api-minimal.php';