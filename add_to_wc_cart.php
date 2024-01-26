//add to child themes function PHP
add_action( 'gform_after_submission_274', 'add_product_to_cart_form', 10, 2 );
function add_product_to_cart_form( $entry, $form ) {
    $summary = explode(",",rgar( $entry, '3' )); 
    $i=0;
    foreach ($summary as $item) {
        if (preg_match( '/(\d+)\s*\[(\d+)\]/', $item, $matches[$i])) {
            $id[$i] = $matches[$i][1];
            $qty[$i] = $matches[$i][2];
            $entry2[$i] = GFAPI::get_entry(  $id[$i] );
            if(GFAPI::entry_exists( $entry2[$i] ) && !is_wp_error($entry2[$i])){
                $product_id = $entry2[$i]['34']; // Product ID from Field ID 1
                $quantity = $qty[$i]; // Quantity from Field ID 3
                $custom_price = intval(preg_replace('/[^\d.]/', '', $entry2[$i]['11'])); 
                $custom_description = $entry2[$i]['30'] ; // Description from Field ID 4
                $linked_image = $entry2[$i]['51'];;
                $uploaded_image = $entry2[$i]['41']; 
                $uploaded_image_url = (!empty($linked_image)) ? $linked_image : $uploaded_image;

                // Prepare the Gravity Forms history data.
                $gravity_forms_history = array(
                    '_gravity_form_cart_item_key' => md5( microtime() . rand() ),
                    '_gravity_form_linked_entry_id' => $entry['id'],
                    '_gravity_form_lead' => array(
                        'form_id' => $form['id'],
                        'source_url' => esc_url_raw( $_SERVER['HTTP_REFERER'] ),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                    ),
                    '_gravity_form_data' => array(
                        // Populate this array based on the specific form configuration.
                        'id' => $form['id'],
                        // Add other configuration details as needed.
                    ),
                );

                // Serialize the Gravity Forms history data.
                $serialized_gravity_forms_history = serialize( $gravity_forms_history );

                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    if ( $cart_item['product_id'] == $product_id && 
                         $cart_item['custom_price'] == $custom_price &&
                         $cart_item['custom_description'] == $custom_description ) {
                        WC()->cart->set_quantity( $cart_item_key, $cart_item['quantity'] + $quantity );
                        return;
                    }
                }

                // Add a new item to the cart.
                $cart_item_data = array(
                    '_gravity_forms_history' => $serialized_gravity_forms_history,
                    'custom_price' => $custom_price,
                    'custom_description' => $custom_description,
                    'uploaded_image_url' => $uploaded_image_url,
                    'gform_entry_id' => $entry['id'],
                    'option_id' => $id[$i],
                    'unique_key' => md5( microtime() . rand() ) // To ensure uniqueness
                );

                WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );

            }
        }
        $i++;
    }
}
    // Serialize the Gravity Forms history data.
    $serialized_gravity_forms_history = serialize( $gravity_forms_history );

    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( $cart_item['product_id'] == $product_id && 
             $cart_item['custom_price'] == $custom_price &&
             $cart_item['custom_description'] == $custom_description ) {
            WC()->cart->set_quantity( $cart_item_key, $cart_item['quantity'] + $quantity );
            return;
        }
    }

    // Add a new item to the cart.
    $cart_item_data = array(
        '_gravity_forms_history' => $serialized_gravity_forms_history,
        'custom_price' => $custom_price,
        'custom_description' => $custom_description,
        'uploaded_image_url' => $uploaded_image_url,
        'gform_entry_id' => $entry['id'],
        'unique_key' => md5( microtime() . rand() ) // To ensure uniqueness
    );

    WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );
}

// Modify cart item price based on custom price.
add_action( 'woocommerce_before_calculate_totals', 'apply_custom_price_to_cart_item', 10, 1 );
function apply_custom_price_to_cart_item( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( isset( $cart_item['custom_price'] ) ) {
            $cart_item['data']->set_price( $cart_item['custom_price'] );
        }
    }
}

// Display custom description in the cart.
add_filter( 'woocommerce_get_item_data', 'display_custom_description_in_cart', 10, 2 );
function display_custom_description_in_cart( $item_data, $cart_item ) {
    if ( isset( $cart_item['custom_description'] ) ) {
        $item_data[] = array(
            'name' => 'Option',
            'value' => $cart_item['custom_description']
        );
    }
    return $item_data;
}

// Replace product thumbnail in the cart if a custom image is uploaded.
add_filter( 'woocommerce_cart_item_thumbnail', 'replace_product_thumbnail_with_uploaded_image', 10, 3 );
function replace_product_thumbnail_with_uploaded_image( $thumbnail, $cart_item, $cart_item_key ) {
    if ( isset( $cart_item['uploaded_image_url'] ) && ! empty( $cart_item['uploaded_image_url'] ) ) {
        $custom_image_url = $cart_item['uploaded_image_url'];
        $thumbnail = '<img src="' . esc_url( $custom_image_url ) . '" alt="Custom Image" />';
    }
    return $thumbnail;
}

add_action( 'woocommerce_checkout_create_order_line_item', 'save_custom_description_order_item_meta', 10, 4 );
function save_custom_description_order_item_meta( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['custom_description'] ) ) {
        $item->add_meta_data( '_custom_description', $values['custom_description'] );
    }
}
add_action( 'woocommerce_admin_order_item_values', 'display_custom_description_admin_order', 10, 3 );
function display_custom_description_admin_order( $product, $item, $item_id ) {
    if ( $product && $item->get_meta( '_custom_description' ) ) {
        echo '<div><strong>Option:</strong> ' . esc_html( $item->get_meta( '_custom_description' ) ) . '</div>';
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'save_description_as_order_item_meta', 10, 4 );
function save_description_as_order_item_meta( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['_gf_option'] ) ) {
        $item->add_meta_data( '_custom_description', $values['_custom_description'], true );
    }
    if ( isset( $values['gform_entry_id'] ) ) {
        $item->add_meta_data( 'gform_entry_id', $values['gform_entry_id'], true );
    }
    if ( isset( $values['option_id'] ) ) {
        $item->add_meta_data( 'option_id', $values['option_id'], true );
    }
}

add_action( 'woocommerce_thankyou', 'update_gravity_forms_entry_with_order_number', 10, 1 );
function update_gravity_forms_entry_with_order_number( $order_id ) {
    $order = wc_get_order( $order_id );

    foreach ( $order->get_items() as $item_id => $item ) {
        $gform_entry_id = $item->get_meta( 'gform_entry_id' );

        if ( ! empty( $gform_entry_id ) ) {
            $entry = GFAPI::get_entry( $gform_entry_id );
            $form = GFAPI::get_form( $entry['form_id'] );

            foreach ( $form['fields'] as $field ) {
                if ( strpos( $field->cssClass, 'wc_gf_order_id' ) !== false ) {
                    // Update the field with the order number
                    GFAPI::update_entry_field( $gform_entry_id, $field->id, $order_id );
                    break; // Exit the loop once the field is found and updated
                }
            }
        }
    }
}
