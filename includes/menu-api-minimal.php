<?php

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
             'dish_name_slovak', 'dish_name_sk', 'name_slovak', 'name_sk', 'slovak_name',
             'dish_name_english', 'dish_name_en', 'name_english', 'name_en', 'english_name',
             'measurement_type', 'measurement', 'portion', 'weight_type',
             'measurement_value', 'weight', 'portion_size', 'weight_value', 'size',
             'price', 'cost', 'price_value',
             'allergens', 'allergen', 'allergies',
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

         // Debug endpoint to understand what's actually in the database
         register_rest_route('menu-alacarte/v1', '/debug', array(
             'methods' => 'GET',
             'callback' => 'menu_alacarte_debug_info',
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

             // Get ALL meta data for debugging
             $all_meta = get_post_meta($item->ID);

             // Try different possible field name variations
             $possible_names = array(
                 'dish_name_slovak', 'dish_name_sk', 'name_slovak', 'name_sk', 'slovak_name',
                 'dish_name_english', 'dish_name_en', 'name_english', 'name_en', 'english_name',
                 'measurement_type', 'measurement', 'portion', 'weight_type',
                 'measurement_value', 'weight', 'portion_size', 'weight_value', 'size',
                 'price', 'cost', 'price_value',
                 'allergens', 'allergen', 'allergies'
             );

             $found_fields = array();
             foreach ($possible_names as $possible_name) {
                 $value = get_post_meta($item->ID, $possible_name, true);
                 if (!empty($value)) {
                     $found_fields[$possible_name] = $value;
                 }
             }

             $formatted_items[] = array(
                 'id' => $item->ID,
                 'title' => array('rendered' => $item->post_title),
                 'content' => array('rendered' => apply_filters('the_content', $item->post_content)),
                 'featured_image_url' => get_the_post_thumbnail_url($item->ID, 'medium'),
                 'menu_categories' => $category_ids,

                 // Standard expected fields
                 'dish_name_slovak' => get_post_meta($item->ID, 'dish_name_slovak', true),
                 'dish_name_english' => get_post_meta($item->ID, 'dish_name_english', true),
                 'measurement_type' => get_post_meta($item->ID, 'measurement_type', true),
                 'measurement_value' => get_post_meta($item->ID, 'measurement_value', true),
                 'price' => get_post_meta($item->ID, 'price', true),
                 'allergens' => get_post_meta($item->ID, 'allergens', true),

                 // Debug information
                 'debug_all_meta_keys' => array_keys($all_meta),
                 'debug_found_fields' => $found_fields,
                 'debug_all_meta_sample' => array_slice($all_meta, 0, 10, true), // First 10 meta fields
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

     // Debug function to understand the database structure
     function menu_alacarte_debug_info($request) {
         global $wpdb;

         // Check if post type exists
         $post_types = get_post_types(array('public' => true), 'names');
         $menu_post_type_exists = post_type_exists('menu_alacarte');

         // Check if taxonomy exists
         $taxonomies = get_taxonomies(array('public' => true), 'names');
         $menu_taxonomy_exists = taxonomy_exists('menu_category');

         // Get sample posts
         $sample_posts = get_posts(array(
             'post_type' => 'menu_alacarte',
             'numberposts' => 3,
             'post_status' => 'any'
         ));

         $posts_info = array();
         foreach ($sample_posts as $post) {
             $all_meta = get_post_meta($post->ID);
             $posts_info[] = array(
                 'id' => $post->ID,
                 'title' => $post->post_title,
                 'status' => $post->post_status,
                 'meta_keys' => array_keys($all_meta),
                 'meta_values_sample' => array_map(function($meta_array) {
                     return is_array($meta_array) && count($meta_array) > 0 ? $meta_array[0] : $meta_array;
                 }, array_slice($all_meta, 0, 5, true))
             );
         }

         // Get sample categories
         $sample_categories = get_terms(array(
             'taxonomy' => 'menu_category',
             'number' => 5,
             'hide_empty' => false
         ));

         $debug_info = array(
             'post_type_exists' => $menu_post_type_exists,
             'taxonomy_exists' => $menu_taxonomy_exists,
             'all_post_types' => array_keys($post_types),
             'all_taxonomies' => array_keys($taxonomies),
             'total_menu_items' => wp_count_posts('menu_alacarte'),
             'sample_posts' => $posts_info,
             'sample_categories' => !is_wp_error($sample_categories) ? $sample_categories : 'Error: ' . $sample_categories->get_error_message(),
             'wordpress_info' => array(
                 'wp_version' => get_bloginfo('version'),
                 'site_url' => get_site_url(),
                 'rest_url' => get_rest_url()
             )
         );

         return rest_ensure_response($debug_info);
     }


     ?>