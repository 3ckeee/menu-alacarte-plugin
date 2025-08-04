<?php 
 // Prevent direct access
     if (!defined('ABSPATH')) {
         exit;
     }

     // Add REST API support - this just enables the endpoints, doesn't change your existing setup
     add_action('init', 'menu_alacarte_minimal_rest_support');
     function menu_alacarte_minimal_rest_support() {
         // Enable REST API for existing custom post type (if not already enabled)
         global $wp_post_types;
         if (isset($wp_post_types['menu_alacarte'])) {
             $wp_post_types['menu_alacarte']->show_in_rest = true;
             $wp_post_types['menu_alacarte']->rest_base = 'menu_alacarte';
         }

         // Enable REST API for existing taxonomy (if not already enabled)
         global $wp_taxonomies;
         if (isset($wp_taxonomies['menu_category'])) {
             $wp_taxonomies['menu_category']->show_in_rest = true;
             $wp_taxonomies['menu_category']->rest_base = 'menu_category';
         }
     }

     // Add meta fields to REST API responses (doesn't change your admin interface)
     add_action('rest_api_init', 'menu_alacarte_minimal_meta_fields');
     function menu_alacarte_minimal_meta_fields() {
         // Register meta fields that might exist in your current setup
         $possible_meta_fields = [
             'dish_name_slovak',
             'dish_name_english',
             'measurement_type',
             'measurement_value',
             'price',
             'allergens',
             // Add any other meta fields your plugin currently uses
         ];

         foreach ($possible_meta_fields as $field) {
             register_rest_field('menu_alacarte', $field, array(
                 'get_callback' => function($post) use ($field) {
                     return get_post_meta($post['id'], $field, true);
                 },
                 'schema' => array(
                     'description' => 'Menu item ' . $field,
                     'type' => 'string',
                     'context' => array('view', 'edit'),
                 ),
             ));
         }

         // Add featured image URL
         register_rest_field('menu_alacarte', 'featured_image_url', array(
             'get_callback' => function($post) {
                 $image_id = get_post_thumbnail_id($post['id']);
                 if ($image_id) {
                     return wp_get_attachment_image_url($image_id, 'medium');
                 }
                 return null;
             },
         ));

         // Add category IDs
         register_rest_field('menu_alacarte', 'menu_categories', array(
             'get_callback' => function($post) {
                 $terms = get_the_terms($post['id'], 'menu_category');
                 if (is_array($terms)) {
                     return array_map(function($term) {
                         return $term->term_id;
                     }, $terms);
                 }
                 return array();
             },
         ));
     }

     // Simple custom endpoint for iOS app (optional - can use standard WordPress API)
     add_action('rest_api_init', 'menu_alacarte_minimal_endpoints');
     function menu_alacarte_minimal_endpoints() {
         // Simple endpoint that returns menu items with all data
         register_rest_route('menu-alacarte/v1', '/items', array(
             'methods' => 'GET',
             'callback' => 'menu_alacarte_get_all_items',
             'permission_callback' => '__return_true',
         ));

         // Simple endpoint for categories
         register_rest_route('menu-alacarte/v1', '/categories', array(
             'methods' => 'GET',
             'callback' => 'menu_alacarte_get_all_categories',
             'permission_callback' => '__return_true',
         ));
     }

     // Get all menu items with full data
     function menu_alacarte_get_all_items($request) {
         $items = get_posts(array(
             'post_type' => 'menu_alacarte',
             'post_status' => 'publish',
             'numberposts' => -1,
             'orderby' => 'menu_order title',
             'order' => 'ASC'
         ));

         $formatted_items = array();

         foreach ($items as $item) {
             $categories = get_the_terms($item->ID, 'menu_category');
             $category_ids = array();
             if (is_array($categories)) {
                 $category_ids = array_map(function($term) {
                     return $term->term_id;
                 }, $categories);
             }

             $formatted_items[] = array(
                 'id' => $item->ID,
                 'title' => array('rendered' => $item->post_title),
                 'content' => array('rendered' => apply_filters('the_content', $item->post_content)),
                 'featured_image_url' => get_the_post_thumbnail_url($item->ID, 'medium'),
                 'menu_categories' => $category_ids,
                 'dish_name_slovak' => get_post_meta($item->ID, 'dish_name_slovak', true),
                 'dish_name_english' => get_post_meta($item->ID, 'dish_name_english', true),
                 'measurement_type' => get_post_meta($item->ID, 'measurement_type', true),
                 'measurement_value' => get_post_meta($item->ID, 'measurement_value', true),
                 'price' => get_post_meta($item->ID, 'price', true),
                 'allergens' => get_post_meta($item->ID, 'allergens', true),
             );
         }

         return rest_ensure_response($formatted_items);
     }

     // Get all categories
     function menu_alacarte_get_all_categories($request) {
         $categories = get_terms(array(
             'taxonomy' => 'menu_category',
             'hide_empty' => false,
             'orderby' => 'name',
             'order' => 'ASC'
         ));

         if (is_wp_error($categories)) {
             return rest_ensure_response(array());
         }

         $formatted_categories = array();

         foreach ($categories as $category) {
             $formatted_categories[] = array(
                 'id' => $category->term_id,
                 'name' => $category->name,
                 'description' => $category->description,
                 'slug' => $category->slug,
                 'parent' => $category->parent,
                 'count' => $category->count
             );
         }

         return rest_ensure_response($formatted_categories);
     }

     // Add CORS headers for mobile app
     add_action('rest_api_init', 'menu_alacarte_minimal_cors');
     function menu_alacarte_minimal_cors() {
         add_filter('rest_pre_serve_request', function($value) {
             header('Access-Control-Allow-Origin: *');
             header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
             header('Access-Control-Allow-Headers: Content-Type, Authorization');
             return $value;
         });
     }

     ?>
