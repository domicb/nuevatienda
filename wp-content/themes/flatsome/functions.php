<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';

/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */


add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');
function custom_override_checkout_fields($fields)
 {
 unset($fields['billing']['billing_mobile_phone']);
 $fields['billing']['billing_company']['placeholder'] = 'DNI/CIF';
 $fields['billing']['billing_company']['label'] = 'DNI/CIF';
 $fields['billing']['billing_phone']['required'];
 $fields['shipping']['shipping_phone'] = array(
    'label'     => __('Teléfono', 'woocommerce'),
'placeholder'   => _x('Teléfono', 'placeholder', 'woocommerce'),
'required'  => false,
'class'     => array('form-row-wide'),
'clear'     => true
 );
 unset($fields['shipping']['shipping_company']);
 return $fields;
 }

 /**
 * Display field value on the order edit page
 */
 
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Teléfono envio').':</strong> ' . get_post_meta( $order->get_id(), '_shipping_phone', true ) . '</p>';
}


 add_action( 'wp_enqueue_scripts', 'ajax_test_enqueue_scripts' );

 function ajax_test_enqueue_scripts() {
  // wp_enqueue_script( 'test', plugins_url( '/ajax-test.js', __FILE__ ), array('jquery'), '1.0', true );
 }
 
 // Hook para usuarios no logueados
add_action('wp_ajax_nopriv_notify_button_click', 'notify_button_click');

// Hook para usuarios logueados
add_action('wp_ajax_notify_button_click', 'notify_button_click');

// Función que procesa la llamada AJAX
function notify_button_click(){
    $message  = isset( $_POST['message'] ) ? $_POST['message'] : false;
    if($message == 'add'){
        WC()->session->set( 'recargo', $message );
       // WC()->cart->add_fee( "Entrega en Sábado", $fee, true, '' );
    }else{
        WC()->session->set( 'recargo', $message );
       //WC()->cart->add_fee( "Entrega en Sábado", $fee, false, '' );
    }
    $valorSesion = WC()->session->get( 'recargo' );
    // Check parameters
    
    if( !$message ) wp_send_json( array('message' => __('Message not received :(', 'wpduf') ) );
    else wp_send_json( array('message' => __('Message received, greetings from server!'.$valorSesion, 'wpduf') ) );

    $fee = 4;

    //WC()->cart->add_to_cart( $pr0pr0 );
}

add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee' );

function woo_add_cart_fee(){
    $fee = 4;
    $free = 0;

    if(WC()->session->get( 'recargo' )== 'add'){
        WC()->cart->add_fee( "Entrega Sábado (Festivo)", $fee, false, '' );
    }else{
        WC()->cart->fees_api()->set_fees();
    }
    
}


function my_add_frontend_scripts() {
    // Deregister script about password strenght meter ~ 800kb
    wp_dequeue_script('wc-password-strength-meter');
    wp_deregister_script('wc-password-strength-meter');

    wp_register_script('custom-script', get_stylesheet_directory_uri().'/custom-script.js', array('jquery'), 1, false );
    wp_enqueue_script('custom-script');
    }

add_action('wp_enqueue_scripts', 'my_add_frontend_scripts');