<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper: Recursively print a panel (menu-category-items) for $term and all its descendants.
 * The very first panel (root of the first desired top-level term) will be visible; all others hidden.
 */
function mlac_print_term_panel( $term, $atts ) {
    static $first_done = false;

    // Determine whether this panel should be shown or hidden on load
    if ( ! $first_done ) {
        $display = 'block';
        $first_done = true;
    } else {
        $display = 'none';
    }

    // Begin the panel for this term
    echo '<div class="menu-category-items" id="cat-' . esc_attr( $term->slug ) . '" style="display:' . $display . ';">';

    // Query all dishes (posts) assigned to this term:
    $args = array(
        'post_type'      => 'menu_alacarte',
        'posts_per_page' => intval( $atts['limit'] ),
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'menu_category',
                'field'    => 'slug',
                'terms'    => $term->slug,
            ),
        ),
    );
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div class="menu-alacarte-item">';

                echo '<div class="dish-container">';

                    // Illustration
                    if ( has_post_thumbnail() ) {
                        echo '<div class="dish-illustration">';
                        the_post_thumbnail( 'full' );
                        echo '</div>';
                    }

                    // Dish details
                    echo '<div class="dish-details">';

                        // Title
                        echo '<h3>' . get_the_title() . '</h3>';

                        // Slovak name
                        $dish_name_sk = get_post_meta( get_the_ID(), '_mlac_dish_name_sk', true );
                        if ( $dish_name_sk ) {
                            echo '<p class="dish-name-sk" style="font-size:0.95em; color:#ccc;">'
                                 . esc_html( $dish_name_sk ) . '</p>';
                        }

                        // English name
                        $dish_name_en = get_post_meta( get_the_ID(), '_mlac_dish_name_en', true );
                        if ( $dish_name_en ) {
                            echo '<p class="dish-name-en" style="font-size:0.9em;">'
                                 . esc_html( $dish_name_en ) . '</p>';
                        }

                        // Measurement (type + value)
                        $measurement_type  = get_post_meta( get_the_ID(), '_mlac_measurement', true );
                        $measurement_value = get_post_meta( get_the_ID(), '_mlac_measurement_value', true );
                        if ( $measurement_type && $measurement_value ) {
                            echo '<p class="menu-details"><strong>'
                                 . esc_html( $measurement_type ) . ':</strong> '
                                 . esc_html( $measurement_value ) . '</p>';
                        }

                        // Price
                        $price = get_post_meta( get_the_ID(), '_mlac_price', true );
                        if ( $price ) {
                            echo '<p class="menu-details"><strong>Cena:</strong> ' . esc_html( $price ) . '</p>';
                        }

                        // Dish‐specific allergens
                        $allergens = get_post_meta( get_the_ID(), '_mlac_allergens', true );
                        if ( $allergens ) {
                            echo '<p class="menu-details" style="font-size:0.8em;"><strong>Alergény:</strong> '
                                 . esc_html( $allergens ) . '</p>';
                        }

                        // Description / content
                        echo '<div class="menu-description">'
                             . apply_filters( 'the_content', get_the_content() ) . '</div>';

                    echo '</div>'; // .dish-details

                echo '</div>'; // .dish-container

            echo '</div>'; // .menu-alacarte-item
        }
        wp_reset_postdata();
    } else {
        echo '<p>V tejto kategórii nie sú žiadne položky.</p>';
    }

    // Allergens legend for this category
    echo '<button class="allergens-button" type="button">Alergény</button>';
    echo '<div class="allergens-list" style="display: none;">';
        echo '<p><strong>Alergény</strong></p>';
        echo '<p><strong>Allergens</strong></p>';
        echo '<p>1. Obilniny obsahujúce lepok / Cereals containing gluten</p>';
        echo '<p>2. Kôrovce / Crustaceans</p>';
        echo '<p>3. Vajcia / Eggs</p>';
        echo '<p>4. Ryby / Fish</p>';
        echo '<p>5. Arašidy / Peanuts</p>';
        echo '<p>6. Sójové zrná / Soya</p>';
        echo '<p>7. Mlieko / Milk</p>';
        echo '<p>8. Orechy / Nuts</p>';
        echo '<p>9. Zeler / Celery</p>';
        echo '<p>10. Horčica / Mustard</p>';
        echo '<p>11. Sezamové semená / Sesame seeds</p>';
        echo '<p>12. Oxid siričitý a siričitany / Sulphur dioxide</p>';
        echo '<p>13. Vlčí bôb / Lupin</p>';
        echo '<p>14. Mäkkýše / Molluscs</p>';
        echo '<p>Gramáže jedál sú uvedené v surovom stave!</p>';
    echo '</div>';

    echo '</div>'; // end .menu-category-items for this term

    // Now recurse into children (so grandchildren and deeper levels also get panels)
    $children = get_terms( array(
        'taxonomy'   => 'menu_category',
        'hide_empty' => false,
        'parent'     => $term->term_id,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );
    if ( ! empty( $children ) && ! is_wp_error( $children ) ) {
        foreach ( $children as $child ) {
            mlac_print_term_panel( $child, $atts );
        }
    }
}

function mlac_menu_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'limit' => -1,
        'width' => '90%',
    ), $atts, 'menu_alacarte' );
    
    // 1) Desired top‐level order (Slovak names)
    $desired_order = array(
        "Antipasti",
        "Niečo k vínu",
        "Zuppe",
        "Insalate",
        "Risotto",
        "Pasta",
        "Piatti di carne",
        "Piatti di pesce",
        "Contorno",
        "Dezerty",
        "Pinsa",
        "Detské menu",
        "Nealkoholické nápoje",
        "Miešané nápoje",
        "Vínna karta",
    );
    
    // 2) Fetch only top‐level terms (parent = 0)
    $terms = get_terms( array(
        'taxonomy'   => 'menu_category',
        'hide_empty' => false,
        'parent'     => 0,
    ) );
    
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        usort( $terms, function( $a, $b ) use ( $desired_order ) {
            $posA = array_search( $a->name, $desired_order );
            $posB = array_search( $b->name, $desired_order );
            if ( $posA === false ) { $posA = 999; }
            if ( $posB === false ) { $posB = 999; }
            return $posA - $posB;
        } );
    }
    
    ob_start();
    
    // Wrapper with inline width
    echo '<div class="menu-alacarte-container" style="width:' . esc_attr( $atts['width'] ) . ';">';
    
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        // 3) Category navigation + submenus
        echo '<ul class="menu-category-nav">';
        foreach ( $terms as $index => $term ) {
            $active_class = ( $index === 0 ) ? 'active' : '';
            echo '<li>';
            
            // Top‐level link
            echo '<a href="#cat-' . esc_attr( $term->slug ) . '" class="menu-category-tab ' . $active_class . '">' 
                 . esc_html( $term->name ) . '</a>';
            
            // 3a) If Nealkoholické nápoje, output its four children
            if ( $term->name === 'Nealkoholické nápoje' ) {
                $children = get_terms( array(
                    'taxonomy'   => 'menu_category',
                    'hide_empty' => false,
                    'parent'     => $term->term_id,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ) );
                if ( ! empty( $children ) ) {
                    echo '<ul class="sub-menu">';
                    foreach ( $children as $child ) {
                        echo '<li>';
                        echo '<a href="#cat-' . esc_attr( $child->slug ) . '" class="menu-category-tab">' 
                             . esc_html( $child->name ) . '</a>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
            }
            
            // 3b) If Vínna karta, output its children + grandchildren (and deeper if you wish)
            if ( $term->name === 'Vínna karta' ) {
                $wine_children = get_terms( array(
                    'taxonomy'   => 'menu_category',
                    'hide_empty' => false,
                    'parent'     => $term->term_id,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ) );
                if ( ! empty( $wine_children ) ) {
                    echo '<ul class="sub-menu">';
                    foreach ( $wine_children as $child ) {
                        echo '<li>';
                        echo '<a href="#cat-' . esc_attr( $child->slug ) . '" class="menu-category-tab">' 
                             . esc_html( $child->name ) . '</a>';
                        
                        // Grandchildren under Rozlievané vína
                        $grandkids = get_terms( array(
                            'taxonomy'   => 'menu_category',
                            'hide_empty' => false,
                            'parent'     => $child->term_id,
                            'orderby'    => 'name',
                            'order'      => 'ASC',
                        ) );
                        if ( ! empty( $grandkids ) ) {
                            echo '<ul class="sub-menu">';
                            foreach ( $grandkids as $gg ) {
                                echo '<li>';
                                echo '<a href="#cat-' . esc_attr( $gg->slug ) . '" class="menu-category-tab">' 
                                     . esc_html( $gg->name ) . '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                        
                        echo '</li>';
                    }
                    echo '</ul>';
                }
            }
            
            echo '</li>';
        }
        echo '</ul>';
        
        // 4) Generate panels for **all** levels
        // We iterate each top‐level term, then recursively print its children and deeper
        foreach ( $terms as $term ) {
            mlac_print_term_panel( $term, $atts );
        }
    } else {
        echo '<p>No menu categories found.</p>';
    }
    
    echo '</div>'; // .menu-alacarte-container
    return ob_get_clean();
}
add_shortcode( 'menu_alacarte', 'mlac_menu_shortcode' );