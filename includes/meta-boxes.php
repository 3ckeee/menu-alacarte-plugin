<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add the meta box to the Menu À la Carte post type.
 */
function mlac_add_menu_meta_box() {
    add_meta_box(
        'mlac_menu_meta',
        __( 'Menu Item Details', 'mlac-textdomain' ),
        'mlac_render_menu_meta_box',
        'menu_alacarte',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'mlac_add_menu_meta_box' );

/**
 * Render the meta box form.
 */
function mlac_render_menu_meta_box( $post ) {
    wp_nonce_field( 'mlac_menu_meta_box', 'mlac_menu_meta_box_nonce' );
    
    // Retrieve existing meta data.
    $dish_name_sk       = get_post_meta( $post->ID, '_mlac_dish_name_sk', true );
    $dish_name_en       = get_post_meta( $post->ID, '_mlac_dish_name_en', true );
    $measurement        = get_post_meta( $post->ID, '_mlac_measurement', true );
    $measurement_value  = get_post_meta( $post->ID, '_mlac_measurement_value', true );
    $price              = get_post_meta( $post->ID, '_mlac_price', true );
    $allergens          = get_post_meta( $post->ID, '_mlac_allergens', true );
    ?>
   <p>
        <label for="mlac_dish_name_sk"><strong>Názov (SK):</strong></label><br />
        <input type="text" id="mlac_dish_name_sk" name="mlac_dish_name_sk" value="<?php echo esc_attr( $dish_name_sk ); ?>" placeholder="Enter Slovak dish name (optional)" style="width:100%;" />
    </p>
    <p>
        <label for="mlac_dish_name_en"><strong>Názov (EN):</strong></label><br />
        <input type="text" id="mlac_dish_name_en" name="mlac_dish_name_en" value="<?php echo esc_attr( $dish_name_en ); ?>" placeholder="Enter English dish name (optional)" style="width:100%;" />
    </p>
    <p>
        <label for="mlac_measurement"><strong>Meranie (Váha/Objem/Množstvo):</strong></label><br />
        <select id="mlac_measurement" name="mlac_measurement" style="width:100%;">
            <option value="Váha" <?php selected( $measurement, 'Váha' ); ?>>Váha</option>
            <option value="Objem" <?php selected( $measurement, 'Objem' ); ?>>Objem</option>
            <option value="Množstvo" <?php selected( $measurement, 'Množstvo' ); ?>>Množstvo</option>
        </select>
    </p>
    <p>
        <label for="mlac_measurement_value"><strong>Hodnota (napr. 350g / 250ml):</strong></label><br />
        <input type="text" id="mlac_measurement_value" name="mlac_measurement_value" value="<?php echo esc_attr( $measurement_value ); ?>" placeholder="350g" style="width:100%;" />
    </p>
    <p>
        <label for="mlac_price"><strong>Cena:</strong></label><br />
        <input type="text" id="mlac_price" name="mlac_price" value="<?php echo esc_attr( $price ); ?>" placeholder="e.g., 10,90€" style="width:100%;" />
    </p>
    <p>
        <label for="mlac_allergens" style="font-size:0.8em;"><strong>Alergény:</strong></label><br />
        <input type="text" id="mlac_allergens" name="mlac_allergens" value="<?php echo esc_attr( $allergens ); ?>" placeholder="e.g., 1,3,7" style="width:100%; font-size:0.8em;" />
    </p>
    <?php
}

function mlac_save_menu_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['mlac_menu_meta_box_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['mlac_menu_meta_box_nonce'], 'mlac_menu_meta_box' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $_POST['post_type'] ) && 'menu_alacarte' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }
    
    $fields = array(
        'mlac_dish_name_sk'       => '_mlac_dish_name_sk',
        'mlac_dish_name_en'       => '_mlac_dish_name_en',
        'mlac_measurement'        => '_mlac_measurement',
        'mlac_measurement_value'  => '_mlac_measurement_value',
        'mlac_price'              => '_mlac_price',
        'mlac_allergens'          => '_mlac_allergens'
    );
    
    foreach ( $fields as $field_name => $meta_key ) {
        if ( isset( $_POST[ $field_name ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field_name ] ) );
        }
    }
}
add_action( 'save_post', 'mlac_save_menu_meta_box_data' );