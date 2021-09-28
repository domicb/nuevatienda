<?php
/*
Plugin Name: MRW
Plugin URI: http://www.mrw.es
Description: Módulo para gestionar sus envíos con MRW
Version: 4.3.5
Author: Webubi
Author URI: http://www.mrw.es
Text Domain: woocommerce-mrw-carrier
*/

//Load Languages PO,ES,EN, CA.
load_plugin_textdomain('woocommerce-mrw-carrier', false, basename(dirname(__FILE__)) . '/languages');

//Enqueue scripts and styles
function include_mrw_scripts()
{
    wp_register_script('mrw-script', plugins_url() . '/woocommerce-mrw-carrier/js/woocommerce-mrw-carrier.js', array(), '1.0.0', true);
    wp_register_script('mrw-tablerates-script', plugins_url() . '/woocommerce-mrw-carrier/js/mrw-carrier-tablerates.js', array(), '1.0.0', true);
    wp_register_style('mrw-style', plugins_url() . '/woocommerce-mrw-carrier/css/woocommerce-mrw-carrier.css');
    
    wp_enqueue_style('mrw-style');
    wp_enqueue_script('mrw-script');
    wp_enqueue_script('mrw-tablerates-script');
    
    wp_localize_script('ajax-script', 'ajax_object',
    array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'include_mrw_scripts');

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Check if WooCommerce is active
*/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    
    function mrw_init()
    {
        /**
        * Check that the class WC_Mrw doesn´t already exists
        **/
        if (!class_exists('WC_Mrw')) {
            class WC_Mrw extends WC_Shipping_Method
            {
                /**
                * Constructor for your shipping class
                *
                * @access public
                * @return void
                */
                public function __construct()
                {
                    $this->id = 'mrw'; // Id for your shipping method. Should be uunique.
                    $this->method_title = __('MRW', 'woocommerce-mrw-carrier'); // Title shown in admin
                    $this->method_description = __('This module allows you to automate the generation of the shipping labels and packages shipping vía MRW carrier', 'woocommerce-mrw-carrier'); // Description shown in admin
                    
                    $this->enabled = "yes"; // This can be added as an setting but for this example its forced enabled
                    $this->title = "MRW Carrier"; // This can be added as an setting but for this example its forced.
                    
                    // Save settings in admin if you have any defined
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                    
                    //Quit for not calling calculate_shipping function and crush on settings save
                    //add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'calculate_shipping' ) );
                    
                    $this->init();
                }
                
                /**
                * Init your settings
                *
                * @access public
                * @return void
                */
                public function init()
                {
                    // Load the settings API
                    $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
                    
                    // Define user set variables
                    $this->enabled = $this->get_option('enabled');
                    $this->title = $this->get_option('mrwtitle');
                    $this->mrwtype = $this->get_option('mrwtype');
                    $this->mrwfranchise = $this->get_option('mrwfranchise');
                    $this->mrwsubscriber = $this->get_option('mrwsubscriber');
                    $this->mrwdepartment = $this->get_option('mrwdepartment');
                    $this->mrwuser = $this->get_option('mrwuser');
                    $this->mrwpass = $this->get_option('mrwpass');
                    $this->mrwpasstrack = $this->get_option('mrwpasstrack');
                    $this->mrwdefaultservice = $this->get_option('mrwdefaultservice');
                    $this->mrwdefaultserviceint = $this->get_option('mrwdefaultserviceint');
                    $this->mrwnotifications = $this->get_option('mrwnotifications');
                    $this->mrwcountries = $this->get_option('mrwcountries');
                    $this->mrwweightprice = $this->get_option('mrwweightprice');
                    $this->mrwerrorlog = $this->get_option('mrwerrorlog');
                    $this->mrwshowrate = $this->get_option('mrwshowrate');
                    $this->mrwapportionment = $this->get_option('mrwapportionment');
                    $this->mrwfree = $this->get_option('mrwfree');
                    $this->mrwmarketplaces = $this->get_option('mrwmarketplaces');
                }
                
                /**
                * init_form_fields function.
                *
                * @access public
                * @return void
                */
                public function init_form_fields()
                {
                    $this->form_fields = include 'includes/settings-mrw.php';
                }
                
                /**
                * calculate_shipping function.
                *
                * @access public
                * @param mixed $package
                * @return void
                */
                public function calculate_shipping($package = array())
                {
                    global $wpdb, $woocommerce;
                    $shipping_total = null;
                    $mrw_cart_weight = 0;
                    $mrw_destination = WC()->customer->get_shipping_state();
                    $mrw_destination_country = WC()->customer->get_shipping_country();
                    $freeshippingcoupon = 0;
                    
                    $mrw_ranges = $wpdb->prefix . 'mrw_ranges';
                    $mrw_cities = $wpdb->prefix . 'mrw_cities';
                    $mrw_taxes = $wpdb->prefix . 'mrw_taxes';
                    
                    $range_id = null;
                    
                    $mrw_city = $wpdb->get_var($wpdb->prepare("SELECT city FROM $mrw_cities WHERE city_id = %s AND country_id = %s", $mrw_destination, $mrw_destination_country));
                    
                    $available = $wpdb->get_var($wpdb->prepare("SELECT available FROM $mrw_cities WHERE city_id = %s AND country_id = %s", $mrw_destination, $mrw_destination_country));
                    
                    $availableCountry = $wpdb->get_var($wpdb->prepare("SELECT available FROM $mrw_cities WHERE country_id = %s AND city_id = %s", $mrw_destination_country, $mrw_destination_country));
                    
                    //Coupons free shipping applied?
                    $all_applied_coupons = $woocommerce->cart->get_applied_coupons();
                    if ( $all_applied_coupons ) {
                        foreach ( $all_applied_coupons as $coupon_code ) {
                            $this_coupon = new WC_Coupon( $coupon_code );
                            if ( $this_coupon->get_free_shipping() ) {
                                $freeshippingcoupon ++;
                            }
                        }
                    }
                    
                    //National.
                    if ($mrw_destination_country == 'ES' || $mrw_destination_country == 'PT') {
                        if ($available) {
                            if ($freeshippingcoupon > 0) {
                                $shipping_total = 0;
                                $rate = array(
                                    'id' => $this->id,
                                    'label' => $this->title,
                                    'cost' => $shipping_total,
                                    'calc_tax' => 'per_order',
                                );
                                
                                $this->add_rate($rate);
                            }
                            else{
                                //Free shipping
                                if ($this->mrwfree > 0 && $package['contents_cost'] >= $this->mrwfree) {
                                    $shipping_total = 0;
                                    
                                    $rate = array(
                                        'id' => $this->id,
                                        'label' => $this->title,
                                        'cost' => $shipping_total,
                                        'calc_tax' => 'per_order',
                                    );
                                    
                                    $this->add_rate($rate);
                                } 
                                else {
                                    switch ($this->mrwweightprice) {
                                        case 'weight':
                                        
                                        $shipping_total = 0;
                                        
                                        //Caculate weight
                                        foreach ($package['contents'] as $item_id => $values) {
                                            // skip products that dont need shipping
                                            if ($values['data']->needs_shipping()) {
                                                // make sure a weight is set
                                                if ($values['data']->get_weight()) {
                                                    $item_weight = $values['data']->get_weight();
                                                    $mrw_cart_weight += $item_weight * $values['quantity'];
                                                }
                                            }
                                        }
                                        
                                        $weight_unit = get_option('woocommerce_weight_unit');
                                        
                                        $normalized_weight = wc_get_weight($mrw_cart_weight, 'kg', $weight_unit);
                                        
                                        //Calculate range
                                        $range_id = $wpdb->get_var($wpdb->prepare("SELECT range_id FROM $mrw_ranges WHERE (%s >= min  AND %s < max)", $normalized_weight, $normalized_weight));
                                        
                                        if (isset($range_id)) {
                                            //Calculate shipping price
                                            $shipping_total = $wpdb->get_var($wpdb->prepare("SELECT price FROM $mrw_taxes WHERE city_id = %s AND country_id = %s AND range_id = %s", $mrw_destination, $mrw_destination_country, $range_id));
                                            
                                            $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => $shipping_total,
                                                'calc_tax' => 'per_order',
                                            );
                                            
                                            $this->add_rate($rate);
                                            
                                        } else if ($this->mrwshowrate == 'rateyes') {
                                            $shipping_total = $wpdb->get_var("SELECT MAX(price) FROM $mrw_taxes");
                                            
                                            //$shipping_total = 100;
                                            
                                            $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => $shipping_total,
                                                'calc_tax' => 'per_order',
                                            );
                                            
                                            $this->add_rate($rate);
                                        }
                                        
                                        break;
                                        
                                        case 'price':
                                        
                                        $shipping_total = 0;
                                        $mrw_cart_price = $package['contents_cost'];
                                        
                                        //Calculate range
                                        $range_id = $wpdb->get_var($wpdb->prepare("SELECT range_id FROM $mrw_ranges WHERE (%s >= min  AND %s < max)", $mrw_cart_price, $mrw_cart_price));
                                        
                                        if (isset($range_id)) {
                                            //Calculate shipping price
                                            $shipping_total = $wpdb->get_var($wpdb->prepare("SELECT price FROM $mrw_taxes WHERE city_id = %s AND country_id = %s AND range_id = %s", $mrw_destination, $mrw_destination_country, $range_id));
                                            
                                            $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => $shipping_total,
                                                'calc_tax' => 'per_order',
                                            );
                                            
                                            $this->add_rate($rate);
                                        } else if ($this->mrwshowrate == 'rateyes') {
                                            $shipping_total = $wpdb->get_var("SELECT MAX(price) FROM $mrw_taxes");
                                            
                                            //$shipping_total = 100;
                                            
                                            $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => $shipping_total,
                                                'calc_tax' => 'per_order',
                                            );
                                            
                                            $this->add_rate($rate);
                                        }
                                        
                                        break;
                                    }
                                }
                                
                            }
                        }
                    } 
                    else {
                        if ($availableCountry) {
                            if ($freeshippingcoupon > 0) {
                                $shipping_total = 0;
                                $rate = array(
                                    'id' => $this->id,
                                    'label' => $this->title,
                                    'cost' => $shipping_total,
                                    'calc_tax' => 'per_order',
                                );
                                
                                $this->add_rate($rate);
                            }
                            else {
                            switch ($this->mrwweightprice) {
                                case 'weight':
                                
                                $shipping_total = 0;
                                
                                //Caculate weight
                                foreach ($package['contents'] as $item_id => $values) {
                                    // skip products that dont need shipping
                                    if ($values['data']->needs_shipping()) {
                                        // make sure a weight is set
                                        if ($values['data']->get_weight()) {
                                            
                                            $item_weight = $values['data']->get_weight();
                                            
                                            $mrw_cart_weight += $item_weight * $values['quantity'];
                                        }
                                    }
                                }
                                
                                $weight_unit = get_option('woocommerce_weight_unit');
                                
                                $normalized_weight = wc_get_weight($mrw_cart_weight, 'kg', $weight_unit);
                                
                                //Calculate range
                                $range_id = $wpdb->get_var($wpdb->prepare("SELECT range_id FROM $mrw_ranges WHERE (%s >= min  AND %s < max)", $normalized_weight, $normalized_weight));
                                
                                if (isset($range_id)) {
                                    //Calculate shipping price
                                    $shipping_total = $wpdb->get_var($wpdb->prepare("SELECT price FROM $mrw_taxes WHERE (country_id = %s AND city_id = %s AND range_id = %s)", $mrw_destination_country, $mrw_destination_country, $range_id));
                                    
                                    $rate = array(
                                        'id' => $this->id,
                                        'label' => $this->title,
                                        'cost' => $shipping_total,
                                        'calc_tax' => 'per_order',
                                    );
                                    
                                    $this->add_rate($rate);
                                    
                                } else if ($this->mrwshowrate == 'rateyes') {
                                    $shipping_total = $wpdb->get_var("SELECT MAX(price) FROM $mrw_taxes");
                                    
                                    $rate = array(
                                        'id' => $this->id,
                                        'label' => $this->title,
                                        'cost' => $shipping_total,
                                        'calc_tax' => 'per_order',
                                    );
                                    
                                    $this->add_rate($rate);
                                }
                                
                                break;
                                
                                case 'price':
                                
                                $shipping_total = 0;
                                $mrw_cart_price = $package['contents_cost'];
                                
                                //Calculate range
                                $range_id = $wpdb->get_var($wpdb->prepare("SELECT range_id FROM $mrw_ranges WHERE (%s >= min  AND %s < max)", $mrw_cart_price, $mrw_cart_price));
                                
                                if (isset($range_id)) {
                                    //Calculate shipping price
                                    $shipping_total = $wpdb->get_var($wpdb->prepare("SELECT price FROM $mrw_taxes WHERE (country_id = %s AND city_id = %s AND range_id = %s)", $mrw_destination_country, $mrw_destination_country, $range_id));
                                    
                                    $rate = array(
                                        'id' => $this->id,
                                        'label' => $this->title,
                                        'cost' => $shipping_total,
                                        'calc_tax' => 'per_order',
                                    );
                                    
                                    $this->add_rate($rate);
                                } else if ($this->mrwshowrate == 'rateyes') {
                                    $shipping_total = $wpdb->get_var("SELECT MAX(price) FROM $mrw_taxes");
                                    
                                    //$shipping_total = 100;
                                    
                                    $rate = array(
                                        'id' => $this->id,
                                        'label' => $this->title,
                                        'cost' => $shipping_total,
                                        'calc_tax' => 'per_order',
                                    );
                                    
                                    $this->add_rate($rate);
                                }
                                
                                break;
                            }
                        }
                        }
                    }
                }
                
                /**
                * generate_additional_costs_html function.
                *
                * @access public
                * @return string
                */
                public function generate_additional_costs_table_html()
                {
                    ob_start();
                    include 'includes/html-extra-costs.php';
                    return ob_get_clean();
                }
            }
        }
    }
    
    add_action('woocommerce_shipping_init', 'mrw_init');
    
    /**
    * Show plugin tab in shipping methods.
    * @param mixed $methods
    * @return $methods
    */
    add_filter('woocommerce_shipping_methods', 'add_mrw');
    function add_mrw($methods)
    {
        $methods[] = 'WC_Mrw';
        return $methods;
    }
    
    //Option 1 bulk
    //Add MRW orders view
    define('ROOTDIR', plugin_dir_path(__FILE__));
    require_once ROOTDIR . 'mrw-list.php';
    
    //Option 2 bulk
    //Add bulk actions to orders view
    add_filter('bulk_actions-edit-shop_order', 'mrw_bulk_actions');
    
    function mrw_bulk_actions($bulk_actions)
    {
        $bulk_actions['mrw_generate_bulk_action'] = __('Generar etiquetas MRW', 'mrw_generate_bulk_action');
        $bulk_actions['mrw_print_bulk_action'] = __('Imprimir etiquetas MRW', 'mrw_print_bulk_action');
        
        return $bulk_actions;
    }
    
    //Control mrw bulk actions form submission
    add_filter('handle_bulk_actions-edit-shop_order', 'mrw_bulk_action_handler', 10, 3);
    
    function mrw_bulk_action_handler($redirect_to, $action, $post_ids)
    {
        if ($action === 'mrw_generate_bulk_action') {
            foreach ($post_ids as $post_id) {
                //Comprobar si ya está generada, si lo está pasar a la siguiente, si no lo está se genera.
                mrw_log("MRW generate action", $post_id);
                if (!is_mrw_generated($post_id)) {
                    //mrw_log("Etiqueta no generada, generar: ", $post_id);
                    bulk_generate_mrw($post_id);
                } else {
                    //mrw_log("Etiqueta generada, no generar: ", $post_id);
                }
            }
            
            $redirect_to = add_query_arg('orders_generated', count($post_ids), $redirect_to);
        } elseif ($action === 'mrw_print_bulk_action') {
            
            $tracking_numbers = array();
            
            foreach ($post_ids as $post_id) {
                mrw_log("MRW print action", $post_id);
                if (!is_mrw_generated($post_id)) {
                    mrw_log("Etiqueta no generada, generar: ", $post_id);
                    bulk_generate_mrw($post_id);
                } else {
                    mrw_log("Etiqueta generada, no generar: ", $post_id);
                }
            }
            
            foreach ($post_ids as $post_id) {
                if (is_mrw_generated($post_id)) {
                    $tracking_numbers[] = get_mrw_tracking_number_bulk($post_id);
                }
            }
            
            $labelPath = bulk_print_mrw($tracking_numbers);

            $urlUpload = wp_upload_dir();
            $MRWFolder = $urlUpload['baseurl'] . '/MRW/';
            
            $download = $MRWFolder . 'download.php?f=' . $labelPath;
            
            $downloadWindow = '<script type="text/javascript" language="javascript">window.open(" ' . $download . '");</script>';
            
            echo $downloadWindow;
            
            $redirect_to = add_query_arg('mrw_printed', count($post_ids), $download);
        }
        
        return $redirect_to;
    }
    
    //Bulk actions notices
    add_action('admin_notices', 'mrw_bulk_action_admin_notice');
    
    function mrw_bulk_action_admin_notice()
    {
        if (!empty($_REQUEST['orders_generated'])) {
            $orders_count = intval($_REQUEST['orders_generated']);
            printf('<div id="message" class="updated fade">' .
            _n('Orders generated %s.',
            'Orders generated %s.',
            $orders_count,
            'mrw_generate_bulk_action'
            ) . '</div>', $orders_count);
        } elseif (!empty($_REQUEST['orders_printed'])) {
            $orders_count = intval($_REQUEST['orders_printed']);
            printf('<div id="message" class="updated fade">' .
            _n('Orders printed %s.',
            'Orders printed %s.',
            $orders_count,
            'mrw_print_bulk_action'
            ) . '</div>', $orders_count);
        }
    }
    //---------------------------------------------------------------------------------------------
    
    /**
    * See plugin settings in plugin page.
    *
    * @access public
    * @param mixed $links $file
    * @return $links
    */
    add_filter("plugin_action_links", 'wcmrw_plugin_action_links', 10, 4);
    function wcmrw_plugin_action_links($links, $file)
    {
        $plugin_file = 'woocommerce-mrw-carrier/woocommerce-mrw-carrier.php';
        //make sure it is our plugin we are modifying
        if ($file == $plugin_file) {
            $settings_link = '<a href="' .
            admin_url('admin.php?page=wc-settings&tab=shipping&section=wc_mrw') . '">' .
            __('Settings', 'woocommerce-mrw-carrier') . '</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }
    
    /*Create the database (if not exists) to controlls orders and tracking numbers. order_id as PRIMARY KEY for not duplicating and be allowed to edit the table in phpMyAdmin*/
    
    global $jal_db_version;
    $jal_db_version = '1.0';
    
    register_activation_hook(__FILE__, 'jal_install');
    
    function jal_install()
    {
        global $wpdb;
        global $jal_db_version;
        
        $mrw_orders = $wpdb->prefix . 'mrw_orders';
        $mrw_taxes = $wpdb->prefix . 'mrw_taxes';
        $mrw_ranges = $wpdb->prefix . 'mrw_ranges';
        $mrw_cities = $wpdb->prefix . 'mrw_cities';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_orders = "CREATE TABLE IF NOT EXISTS $mrw_orders (
            order_id bigint(20) NOT NULL PRIMARY KEY,
            tracking_number varchar(12) NOT NULL,
            URL text NOT NULL,
            options text NOT NULL,
            date date NOT NULL
            ) $charset_collate;";
            
            $sql_taxes = "CREATE TABLE IF NOT EXISTS $mrw_taxes (
                country_id varchar(22) NOT NULL,
                city_id varchar(22) NOT NULL,
                range_id int NOT NULL,
                PRIMARY KEY (country_id, city_id, range_id),
                price float NULL
                ) $charset_collate;";
                
                $wpdb->query("DROP TABLE IF EXISTS " . $mrw_taxes);
                $wpdb->query("DROP TABLE IF EXISTS " . $mrw_cities);
                
                $sql_cities = "CREATE TABLE IF NOT EXISTS $mrw_cities (
                    id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    country_id varchar(3) NOT NULL,
                    city_id varchar(3) NOT NULL,
                    city varchar(22) NOT NULL,
                    available boolean NOT NULL
                    ) $charset_collate;";
                    
                    $sql_ranges = "CREATE TABLE IF NOT EXISTS $mrw_ranges (
                        range_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
                        min bigint(20) NOT NULL,
                        max bigint(20) NOT NULL
                        ) $charset_collate;";
                        
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                        
                        dbDelta($sql_orders);
                        dbDelta($sql_taxes);
                        dbDelta($sql_ranges);
                        dbDelta($sql_cities);
                        
                        add_option('jal_db_version', $jal_db_version);
                    }
                    
                    register_activation_hook(__FILE__, 'jal_install_data');
                    
                    add_filter('woocommerce_states', 'woocommerce_mrw_portugal_states');
                    function woocommerce_mrw_portugal_states($states)
                    {
                        $states["PT"] = array(
                            'AC' => __('Azores', 'woocommerce-mrw-carrier'),
                            'AV' => __('Aveiro', 'woocommerce-mrw-carrier'),
                            'BJ' => __('Beja', 'woocommerce-mrw-carrier'),
                            'BR' => __('Braga', 'woocommerce-mrw-carrier'),
                            'BG' => __('Braganza', 'woocommerce-mrw-carrier'),
                            'CB' => __('Castelo Branco', 'woocommerce-mrw-carrier'),
                            'CM' => __('Coimbra', 'woocommerce-mrw-carrier'),
                            'EV' => __('Évora', 'woocommerce-mrw-carrier'),
                            'FR' => __('Faro', 'woocommerce-mrw-carrier'),
                            'GD' => __('Guarda', 'woocommerce-mrw-carrier'),
                            'LR' => __('Leiria', 'woocommerce-mrw-carrier'),
                            'LS' => __('Lisboa', 'woocommerce-mrw-carrier'),
                            'MD' => __('Madeira', 'woocommerce-mrw-carrier'),
                            'PR' => __('Portalegre', 'woocommerce-mrw-carrier'),
                            'PT' => __('Oporto', 'woocommerce-mrw-carrier'),
                            'ST' => __('Santarém', 'woocommerce-mrw-carrier'),
                            'SB' => __('Setúbal', 'woocommerce-mrw-carrier'),
                            'VC' => __('Viana do Castelo', 'woocommerce-mrw-carrier'),
                            'VR' => __('Vila Real', 'woocommerce-mrw-carrier'),
                            'VS' => __('Viseu', 'woocommerce-mrw-carrier'),
                        );
                        return $states;
                    }
                    
                    function jal_install_data()
                    {
                        global $wpdb;
                        
                        // $states = new WC_Countries;
                        // $states = $states->get_states('ES');
                        $var = null;
                        
                        //$table_taxes = $wpdb->prefix . 'mrw_taxes';
                        $table_ranges = $wpdb->prefix . 'mrw_ranges';
                        //$table_cities = $wpdb->prefix . 'mrw_cities';
                        
                        //Create first range is there are no ranges
                        $var = $wpdb->get_var("SELECT COUNT(*) FROM $table_ranges");
                        
                        if ($var == 0) {
                            $wpdb->replace(
                                $table_ranges,
                                array(
                                    'min' => '0',
                                    'max' => '2',
                                    )
                                );
                                
                                $wpdb->replace(
                                    $table_ranges,
                                    array(
                                        'min' => '2',
                                        'max' => '5',
                                        )
                                    );
                                }
                                
                                //Get states table
                                set_states('ES');
                                set_states('PT');
                                set_states_int();
                            }
                            
                            function set_states($country)
                            {
                                global $wpdb;
                                
                                $states = new WC_Countries;
                                $states = $states->get_states($country);
                                $var = null;
                                
                                $table_taxes = $wpdb->prefix . 'mrw_taxes';
                                $table_cities = $wpdb->prefix . 'mrw_cities';
                                
                                foreach ($states as $code => $state) {
                                    
                                    $wpdb->replace(
                                        $table_cities,
                                        array(
                                            'city' => $state,
                                            'city_id' => $code,
                                            'country_id' => $country,
                                            'available' => true,
                                            )
                                        );
                                    }
                                    
                                    //Create table with cities
                                    foreach ($states as $code => $state) {
                                        if ( $country == 'ES' ||  $country == 'PT') {
                                            $wpdb->replace(
                                                $table_taxes,
                                                array(
                                                    'country_id' => $country,
                                                    'city_id' => $code,
                                                    'range_id' => '1',
                                                    'price' => '4.95',
                                                    )
                                                );
                                            }
                                        }
                                    }
                                    
                                    function set_states_int()
                                    {
                                        global $wpdb;
                                        
                                        $states = new WC_Countries;
                                        $countries = $states->get_countries();
                                        $var = null;
                                        
                                        $table_taxes = $wpdb->prefix . 'mrw_taxes';
                                        $table_cities = $wpdb->prefix . 'mrw_cities';
                                        
                                        foreach ($countries as $code => $state) {
                                            if ($code != 'ES' && $code != 'PT') {
                                                $wpdb->replace(
                                                    $table_cities,
                                                    array(
                                                        'city' => $state,
                                                        'country_id' => $code,
                                                        'city_id' => $code,
                                                        'available' => false,
                                                        )
                                                    );
                                                }
                                            }
                                            
                                            //Create table with cities
                                            foreach ($countries as $code => $state) {
                                                if ($code != 'ES' && $code != 'PT') {
                                                    $wpdb->replace(
                                                        $table_taxes,
                                                        array(
                                                            'country_id' => $code,
                                                            'city_id' => $code,
                                                            'range_id' => '1',
                                                            'price' => '10',
                                                            )
                                                        );
                                                    }
                                                }
                                            }
                                            
                                            //Display Metabox (if the order has mrw as shipping carrier) to generate and download MRW labels.
                                            add_action('add_meta_boxes_shop_order', 'mrw_add_meta_box');
                                            function mrw_add_meta_box()
                                            {
                                                global $woocommerce, $post;
                                                
                                                if (!empty($post)) {
                                                    $order_id = $post->ID;
                                                }
                                                
                                                if (isset($order_id)) {
                                                    $order = new WC_Order($order_id);
                                                }
                                                
                                                $country = $order->get_shipping_country();
                                                
                                                //Comprobar si es nacional o internacional
                                                if (isset($order) && ($order->has_shipping_method('mrw') || check_free_shipping($order->get_shipping_methods()))) {
                                                    if ($country == 'ES' || $country == 'PT' || $country == 'GB' || $country == 'AD') {
                                                        add_meta_box(
                                                            'woocommerce-order-mrw',
                                                            '<a><img border="0" alt="logo mrw" src="../wp-content/plugins/woocommerce-mrw-carrier/img/LogoMRW.png" width="10%"></a>',
                                                            'order_mrw_nac',
                                                            'shop_order',
                                                            'advanced',
                                                            'high'
                                                        );
                                                    } else {
                                                        add_meta_box(
                                                            'woocommerce-order-mrw',
                                                            '<a><img border="0" alt="logo mrw" src="../wp-content/plugins/woocommerce-mrw-carrier/img/LogoMRW.png" width="10%"></a>',
                                                            'order_mrw_int',
                                                            'shop_order',
                                                            'advanced',
                                                            'high'
                                                        );
                                                    }
                                                }
                                                
                                            }
                                            
                                            //Create folder wp_content/uploads/MRW
                                            add_action('add_meta_boxes', 'mrw_folder');
                                            function mrw_folder()
                                            {
                                                $urlUpload = wp_upload_dir();
                                                $MRWFolder = $urlUpload['basedir'] . '/MRW/';
                                                $MRWDFile = $urlUpload['basedir'] . '/MRW/download.php';
                                                
                                                if (!file_exists($MRWFolder)) {
                                                    mkdir($MRWFolder, 0775, true);
                                                }
                                                
                                                //Move download.php file to uploads/MRW folder
                                                if (!file_exists($MRWDFile)) {
                                                    $origin = plugin_dir_path(__FILE__) . 'download.php';
                                                    $destiny = $urlUpload['basedir'] . '/MRW/download.php';
                                                    copy($origin, $destiny);
                                                }
                                            }
                                            
                                            function mrw_activate()
                                            {
                                                mrw_folder();
                                            }
                                            register_activation_hook(__FILE__, 'mrw_activate');
                                            
                                            //Controls the buttons for generating and visualizate MRW labels.
                                            function order_mrw_nac()
                                            {
                                                if (defined('WC_VERSION') && version_compare(WC_VERSION, '3.0', '>=')) {
                                                    
                                                    global $post, $woocommerce;
                                                    $order_id = $post->ID;
                                                    if (isset($order_id)) {
                                                        $order = new WC_Order($order_id);
                                                    }
                                                    
                                                    $mrwdefaultservice = get_mrw_default_service();
                                                    $mrwavailableservices = get_mrw_available_services();
                                                    
                                                    //Check if the label is already generated
                                                    $tracking_number = get_mrw_tracking_number3();
                                                    
                                                    $service = null;
                                                    $npack = null;
                                                    $frandel = null;
                                                    $satdel = null;
                                                    $ret = null;
                                                    $comm = null;
                                                    $reference = null;
                                                    $time_slot = null;
                                                    
                                                    //Change address
                                                    $new_name = null;
                                                    $new_street = null;
                                                    $new_number = null;
                                                    $new_pc = null;
                                                    $new_city = null;
                                                    $new_phone = null;
                                                    
                                                    //Get the weight of the order from get_mrw_weight()
                                                    $mrwproducts = $order->get_items();
                                                    $mrwweight = get_mrw_weight3($mrwproducts);
                                                    
                                                    if ($tracking_number == null) {
                                                        
                                                        ?>
                                                        <div id="mrw_container">
                                                        <?php 
                                                        if (!soap_active()) {
                                                            ?>
                                                            <p class="mrw_soap"><?php echo __('Class SOAP must be active in order to generate labels', 'woocommerce-mrw-carrier'); ?></p>
                                                            <?php        
                                                        }
                                                        ?>
                                                        <form action="" method="POST">
                                                        <div id="mrw_tracking_info_no" class="mrw_column">
                                                        <p class="mrw_data_title" style="bold"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                        <span class="mrw_service"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo get_service_name($service); ?></a></span>
                                                        <br/>
                                                        <span class="mrw_package_number"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_npackages"><?php echo $npack; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_franchisedel"><?php echo __('Delivery in franchise', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_franchisedel"><?php echo $frandel; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_saturdaydel"><?php echo __('Deliver on Saturday', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_saturdaydel"><?php echo $satdel; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_return"><?php echo __('With return', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_return"><?php echo $ret; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_timeslot"><?php echo __('Time slot', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_timeslot"><?php echo $time_slot; ?></a></span>
                                                        <br/>
                                                        <span class="tracking_num shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                        <br/>
                                                        <span class="tracking_info tracking_message" value=""><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"></a></span>
                                                        <br/>
                                                        <!--  <span class="mrw_reference"><?php echo __('Reference', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_reference"><?php echo $reference; ?></a></span>
                                                        <br/> -->
                                                        <span class="mrw_comment"><?php echo __('Comments', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_comments"><?php echo $comm; ?></a></span>
                                                        <br/>
                                                        </div>
                                                        
                                                        <!-- Terceras -->
                                                        <div id="mrw_address_info_no" class="mrw_column">
                                                        <p class="mrw_data_title" style="bold"><?php echo __('Pick up address', 'woocommerce-mrw-carrier'); ?></p>
                                                        <br/>
                                                        <span class="mrw_address_name"><?php echo __('Name', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_name"><?php echo $new_name; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_street"><?php echo __('Street', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_street"><?php echo $new_street; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_number"><?php echo __('Number', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_number"><?php echo $new_number; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_postalcode"><?php echo __('Postal code', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_pc"><?php echo $new_pc; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_city"><?php echo __('City', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_city"><?php echo $new_city; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_phone"><?php echo __('Name', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_phone"><?php echo $new_phone; ?></a></span>
                                                        <br/>
                                                        </div>
                                                        <!-- Terceras -->
                                                        
                                                        <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                        <input type="hidden" id="shipping_company" value="<?php echo $order->get_shipping_company(); ?>" />
                                                        <input type="hidden" id="shipping_first_name" value="<?php echo $order->get_shipping_first_name(); ?>" />
                                                        <input type="hidden" id="shipping_last_name" value="<?php echo $order->get_shipping_last_name(); ?>" />
                                                        <input type="hidden" id="shipping_address_1" value="<?php echo $order->get_shipping_address_1(); ?>" />
                                                        <input type="hidden" id="shipping_address_2" value="<?php echo $order->get_shipping_address_2(); ?>" />
                                                        <input type="hidden" id="shipping_postcode" value="<?php echo $order->get_shipping_postcode(); ?>" />
                                                        <input type="hidden" id="shipping_city" value="<?php echo $order->get_shipping_city(); ?>" />
                                                        <input type="hidden" id="billing_email" value="<?php echo $order->get_billing_email(); ?>" />
                                                        <input type="hidden" id="billing_phone" value="<?php echo $order->get_billing_phone(); ?>" />
                                                        
                                                        <div id="shipment_data" class="mrw_column">
                                                        <p class="mrw_info"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:
                                                        <select id="mrw_select_service" value="<?php echo $mrwdefaultservice; ?>" name="mrw_select_service">
                                                        <?php
                                                        foreach ($mrwavailableservices as $mrw_service => $mrw_service_name) {
                                                            if ($mrw_service == $mrwdefaultservice) {
                                                                echo '<option value="' . $mrw_service . '" selected>' . $mrw_service_name . '</option>';
                                                            } else {
                                                                echo '<option value="' . $mrw_service . '">' . $mrw_service_name . '</option>';
                                                            }

                                                        }?>
                                                        </select>
                                                        </p>
                                                        <p class="mrw_info" style="display:none;" id="show_select_timeSlot"><?php echo __('Time slot (just for Ecommerce service)', 'woocommerce-mrw-carrier'); ?>:</br><form id="mrw_select_timeSlot">
                                                        <input type="radio" name="tramo" value="0" checked="checked"><?php echo __('Don\'t use time slot', 'woocommerce-mrw-carrier'); ?><br>
                                                        <input type="radio" name="tramo" value="1"><?php echo __('entre las 08:00 y las 14:00 horas', 'woocommerce-mrw-carrier'); ?><br>
                                                        <input type="radio" name="tramo" value="2"><?php echo __('entre las 16:00 y las 19:00 horas', 'woocommerce-mrw-carrier'); ?><br>
                                                        <input type="radio" name="tramo" value="3"><?php echo __('entre las 20:00 y las 22:00 horas', 'woocommerce-mrw-carrier'); ?><br>
                                                        </form></p>
                                                        <p class="mrw_info"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<input type="number" id="mrw_select_npackages" maxlength="2" value = "1" min="1" max="99"/></p>
                                                        <p class="mrw_info"><?php echo __('Total weight (Kg)', 'woocommerce-mrw-carrier'); ?>:<input type="number" id="mrw_select_weight" maxlength="3" value = "<?php echo $mrwweight?>" min="1" max="999"/></p>
                                                        <p class="mrw_info"><?php echo __('Delivery in franchise', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_select_franchised" value/></p>
                                                        <p class="mrw_info"><?php echo __('Deliver on Saturday', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_select_saturdayd" value/></p>
                                                        <p class="mrw_info"><?php echo __('With return', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_select_return" value/></p>
                                                        <p class="mrw_info"><?php echo __('Reference', 'woocommerce-mrw-carrier'); ?>:<input type="text" id="mrw_select_reference" maxlength="25" value placeholder="<?php echo __("Write a reference (Optional)", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        
                                                        <?php
                                                        $mrw_check_comments = get_mrw_check_comments();

                                                        if ($mrw_check_comments == 'yes') {
                                                            $clientComments = $order->get_customer_note();
                                                        ?>
                                                            <p class="mrw_info"><?php echo __("Comments", "woocommerce-mrw-carrier"); ?>:<input type="text" id="mrw_select_comments" maxlength="150" value="<?php echo $clientComments?>" placeholder="<?php echo __("Write a comment (Optional)", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        <?php
                                                        }
                                                        else {
                                                        ?>
                                                            <p class="mrw_info"><?php echo __("Comments", "woocommerce-mrw-carrier"); ?>:<input type="text" id="mrw_select_comments" maxlength="150" value placeholder="<?php echo __("Write a comment (Optional)", "woocommerce-mrw-carrier"); ?>"/></p>
                                                        <?php
                                                        }
                                                        ?>
                                                        
                                                        </div>
                                                        
                                                        <!-- Terceras -->
                                                        <div id="shipment_address_data" class="mrw_column">
                                                        <p class="mrw_info" id="mrw_check_address"><?php echo __('Change pick up address', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_change_address" onchange="javascript:showChangeAddress()" value/></p>
                                                        <div id="address_shipment_data" style="display: none;">
                                                        <input type="text" id="mrw_select_name" maxlength="50" value placeholder="<?php echo __("Name", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        <input type="text" id="mrw_select_street" maxlength="30" value placeholder="<?php echo __("Street", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        <input type="text" id="mrw_select_number" maxlength="4" value placeholder="<?php echo __("Number", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        <input type="text" id="mrw_select_pc" maxlength="5" value placeholder="<?php echo __("Postal code", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        <input type="text" id="mrw_select_city" maxlength="30" value placeholder="<?php echo __("City", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        <input type="text" id="mrw_select_phone" maxlength="9" value placeholder="<?php echo __("Phone", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                        </div>
                                                        </div>
                                                        <!-- Fin Terceras -->
                                                        </div>
                                                        <div id="generate_form">
                                                        <input type="hidden" id="shipping_weight" value="<?php echo $mrwweight; ?>" />
                                                        <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number3(); ?>" />
                                                        <input id="btn_generate_nac" type="button" method="POST" name="generate_submit" class="button-primary" value="<?php echo __('Generate label', 'woocommerce-mrw-carrier'); ?>"/>
                                                        <span id="msg_generate" style="display:none"><?php echo __('Generating label', 'woocommerce-mrw-carrier'); ?></span>
                                                        </div>
                                                        </form>
                                                        
                                                        <div id="generate_form">
                                                        <form action="">
                                                        <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                        <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number3(); ?>" />
                                                        </form>
                                                        </div>
                                                        <div class="clear"></div>
                                                        <?php
                                                    } else {
                                                        $order_data = get_mrw_order_data3();
                                                        $service = get_service_name($order_data['Service']);
                                                        $npackages = $order_data['NPack'];
                                                        $frandel = mrw_get_sn($order_data['FranDel']);
                                                        $satdel = mrw_get_sn($order_data['SatDev']);
                                                        $return = mrw_get_sn($order_data['Ret']);
                                                        $time_slot = mrw_get_ts($order_data['time_slot']);
                                                        $reference = $order_data['Reference'];
                                                        $comments = $order_data['Comm'];
                                                        $check_ad = $order_data['Third'];
                                                        $mrw_mp_flag = get_mrw_marketplaces_flag();
                                                        
                                                        if ($check_ad == 'true') {
                                                            
                                                            $new_name = $order_data['address_name'];
                                                            $new_street = $order_data['address_street'];
                                                            $new_number = $order_data['address_number'];
                                                            $new_pc = $order_data['address_pc'];
                                                            $new_city = $order_data['address_city'];
                                                            $new_phone = $order_data['address_phone'];
                                                        }
                                                        
                                                        ?>
                                                        <div id="mrw_container">
                                                        <div id="mrw_tracking_info_si" class="mrw_column">
                                                        <p class="mrw_data_title"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                        <span class="mrw_service_c"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo $service; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_package_number"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_npackages"><?php echo $npackages; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_franchisedel"><?php echo __('Delivery in franchise', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_franchisedel"><?php echo __($frandel, 'woocommerce-mrw-carrier'); ?></a></span>
                                                        <br/>
                                                        <span class="mrw_saturdaydel"><?php echo __('Deliver on Saturday', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_saturdaydel"><?php echo __($satdel, 'woocommerce-mrw-carrier'); ?></a></span>
                                                        <br/>
                                                        <span class="mrw_return"><?php echo __('With return', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_return"><?php echo __($return, 'woocommerce-mrw-carrier'); ?></a></span>
                                                        <br/>
                                                        <?php if ($order_data['Service'] == '0800') {?>
                                                            <span class="mrw_timeslot"><?php echo __('Time slot', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_timeslot"><?php echo $time_slot; ?></a></span>
                                                            <br/>
                                                            <?php
                                                        }
                                                        ?>
                                                        <span class="shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                        <br/>
                                                        <span class="tracking_message"><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"><?php echo do_action('add_mrw_tracking_info', $tracking_number); ?></a></span>
                                                        <br/>
                                                        <!-- <span class="mrw_reference"><?php echo __('Reference', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_reference"><?php echo $reference; ?></a></span>
                                                        <br/> -->
                                                        <span class="mrw_comment"><?php echo __('Comments', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_comments"><?php echo $comments; ?></a></span>
                                                        <br/>
                                                        </div>
                                                        
                                                        <div id="mrw_address_info_si" <?php if ($check_ad == 'true') {echo ' style="display: block;"';}?> class="mrw_column">
                                                        <a></a>
                                                        <p class="mrw_data_title"><?php echo __('Pick up address', 'woocommerce-mrw-carrier'); ?></p>
                                                        <span class="mrw_address_name"><?php echo __('Name', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_name"><?php echo $new_name; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_street"><?php echo __('Street', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_street"><?php echo $new_street; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_number"><?php echo __('Number', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_number"><?php echo $new_number; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_postalcode"><?php echo __('Postal code', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_pc"><?php echo $new_pc; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_city"><?php echo __('City', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_city"><?php echo $new_city; ?></a></span>
                                                        <br/>
                                                        <span class="mrw_address_phone"><?php echo __('Phone', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_phone"><?php echo $new_phone; ?></a></span>
                                                        <br/>
                                                        </div>
                                                        </div>
                                                        
                                                        <?php if (($mrw_mp_flag == 'yes' && $check_ad == 'true') || $check_ad == 'false') {?>
                                                            <div id="download_form">
                                                            <form action="">
                                                            <a href="<?php echo download_mrw_label($tracking_number); ?>" download=""><input id="btn_download" type="button" name="download_submit" class="button-primary" value="<?php echo __('Download label', 'woocommerce-mrw-carrier'); ?>"/></a>
                                                            </form>
                                                            </div>
                                                            <?php }?>
                                                            <div class="clear"></div>
                                                            <?php
                                                        }
                                                    } else {
                                                        global $post, $woocommerce;
                                                        $order_id = $post->ID;
                                                        if (isset($order_id)) {
                                                            $order = new WC_Order($order_id);
                                                        }
                                                        
                                                        $mrwdefaultservice = get_mrw_default_service();
                                                        $mrwavailableservices = get_mrw_available_services();
                                                        
                                                        //Check if the label is already generated
                                                        $tracking_number = get_mrw_tracking_number();
                                                        
                                                        $service = null;
                                                        $npack = null;
                                                        $frandel = null;
                                                        $satdel = null;
                                                        $ret = null;
                                                        $comm = null;
                                                        $reference = null;
                                                        $time_slot = null;
                                                        
                                                        //Change address
                                                        $new_name = null;
                                                        $new_street = null;
                                                        $new_number = null;
                                                        $new_pc = null;
                                                        $new_city = null;
                                                        $new_phone = null;
                                                        
                                                        //Get the weight of the order from get_mrw_weight()
                                                        $mrwproducts = $order->get_items();
                                                        $mrwweight = get_mrw_weight($mrwproducts);
                                                        
                                                        //var_dump($mrwweight);
                                                        
                                                        if ($tracking_number == null) {
                                                            
                                                            //var_dump($order);
                                                            ?>
                                                            <div id="mrw_container">
                                                            <form action="" method="POST">
                                                            <div id="mrw_tracking_info_no" class="mrw_column">
                                                            <p class="mrw_data_title" style="bold"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                            <span class="mrw_service"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo get_service_name($service); ?></a></span>
                                                            <br/>
                                                            <span class="mrw_package_number"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_npackages"><?php echo $npack; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_franchisedel"><?php echo __('Delivery in franchise', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_franchisedel"><?php echo $frandel; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_saturdaydel"><?php echo __('Deliver on Saturday', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_saturdaydel"><?php echo $satdel; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_return"><?php echo __('With return', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_return"><?php echo $ret; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_timeslot"><?php echo __('Time slot', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_timeslot"><?php echo $time_slot; ?></a></span>
                                                            <br/>
                                                            <span class="tracking_num shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                            <br/>
                                                            <span class="tracking_info tracking_message" value=""><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"></a></span>
                                                            <br/>
                                                            <span class="mrw_reference"><?php echo __('Reference', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_reference"><?php echo $reference; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_comment"><?php echo __('Comments', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_comments"><?php echo $comm; ?></a></span>
                                                            <br/>
                                                            </div>
                                                            
                                                            <!-- Terceras -->
                                                            <div id="mrw_address_info_no" class="mrw_column">
                                                            <p class="mrw_data_title" style="bold"><?php echo __('Pick up address', 'woocommerce-mrw-carrier'); ?></p>
                                                            <br/>
                                                            <span class="mrw_address_name"><?php echo __('Name', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_name"><?php echo $new_name; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_street"><?php echo __('Street', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_street"><?php echo $new_street; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_number"><?php echo __('Number', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_number"><?php echo $new_number; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_postalcode"><?php echo __('Postal code', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_pc"><?php echo $new_pc; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_city"><?php echo __('City', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_city"><?php echo $new_city; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_phone"><?php echo __('Name', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_phone"><?php echo $new_phone; ?></a></span>
                                                            <br/>
                                                            </div>
                                                            <!-- Terceras -->
                                                            
                                                            <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                            <input type="hidden" id="shipping_company" value="<?php echo $order->shipping_company; ?>" />
                                                            <input type="hidden" id="shipping_first_name" value="<?php echo $order->shipping_first_name; ?>" />
                                                            <input type="hidden" id="shipping_last_name" value="<?php echo $order->shipping_last_name; ?>" />
                                                            <input type="hidden" id="shipping_address_1" value="<?php echo $order->shipping_address_1; ?>" />
                                                            <input type="hidden" id="shipping_address_2" value="<?php echo $order->shipping_address_2; ?>" />
                                                            <input type="hidden" id="shipping_postcode" value="<?php echo $order->shipping_postcode; ?>" />
                                                            <input type="hidden" id="shipping_city" value="<?php echo $order->shipping_city; ?>" />
                                                            <input type="hidden" id="billing_email" value="<?php echo $order->billing_email; ?>" />
                                                            <input type="hidden" id="billing_phone" value="<?php echo $order->billing_phone; ?>" />
                                                            
                                                            <div id="shipment_data" class="mrw_column">
                                                            <p class="mrw_info"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:
                                                            <select id="mrw_select_service" value="<?php echo $mrwdefaultservice; ?>">
                                                            <?php
                                                            foreach ($mrwavailableservices as $mrw_service => $mrw_service_name) {
                                                                if ($mrw_service == $mrwdefaultservice) {
                                                                    echo '<option value="' . $mrw_service . '" selected>' . $mrw_service_name . '</option>';
                                                                } else {
                                                                    echo '<option value="' . $mrw_service . '">' . $mrw_service_name . '</option>';
                                                                }
                                                            }?>
                                                            </select>
                                                            </p>
                                                            <p class="mrw_info" style="display:none;" id="show_select_timeSlot"><?php echo __('Time slot (just for Ecommerce service)', 'woocommerce-mrw-carrier'); ?>:</br><form id="mrw_select_timeSlot">
                                                            <input type="radio" name="tramo" value="0" checked="checked"><?php echo __('Don\'t use time slot', 'woocommerce-mrw-carrier'); ?><br>
                                                            <input type="radio" name="tramo" value="1"><?php echo __('entre las 08:00 y las 14:00 horas', 'woocommerce-mrw-carrier'); ?><br>
                                                            <input type="radio" name="tramo" value="2"><?php echo __('entre las 16:00 y las 19:00 horas', 'woocommerce-mrw-carrier'); ?><br>
                                                            <input type="radio" name="tramo" value="3"><?php echo __('entre las 20:00 y las 22:00 horas', 'woocommerce-mrw-carrier'); ?><br>
                                                            </form></p>
                                                            <p class="mrw_info"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<input type="number" id="mrw_select_npackages" maxlength="2" value = "1" min="1" max="99"/></p>
                                                            <p class="mrw_info"><?php echo __('Delivery in franchise', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_select_franchised" value/></p>
                                                            <p class="mrw_info"><?php echo __('Deliver on Saturday', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_select_saturdayd" value/></p>
                                                            <p class="mrw_info"><?php echo __('With return', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_select_return" value/></p>
                                                            <p class="mrw_info"><?php echo __('Reference', 'woocommerce-mrw-carrier'); ?>:<input type="text" id="mrw_select_reference" maxlength="25" value placeholder="<?php echo __("Write a reference (Optional)", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            
                                                            <?php
                                                            $mrw_check_comments = get_mrw_check_comments();

                                                            if ($mrw_check_comments == 'yes') {
                                                                $clientComments = $order->get_customer_note();
                                                            ?>
                                                                <p class="mrw_info"><?php echo __("Comments", "woocommerce-mrw-carrier"); ?>:<input type="text" id="mrw_select_comments" maxlength="150" value="<?php echo $clientComments?>" placeholder="<?php echo __("Write a comment (Optional)", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            <?php
                                                            }
                                                            else {
                                                            ?>
                                                                <p class="mrw_info"><?php echo __("Comments", "woocommerce-mrw-carrier"); ?>:<input type="text" id="mrw_select_comments" maxlength="150" value placeholder="<?php echo __("Write a comment (Optional)", "woocommerce-mrw-carrier"); ?>"/></p>
                                                            <?php
                                                            }
                                                            ?>
                                                            
                                                            </div>
                                                            
                                                            <!-- Terceras -->
                                                            <div id="shipment_address_data" class="mrw_column">
                                                            <p class="mrw_info" id="mrw_check_address"><?php echo __('Change pick up address', 'woocommerce-mrw-carrier'); ?>:<input type="checkbox" id="mrw_change_address" onchange="javascript:showChangeAddress()" value/></p>
                                                            <div id="address_shipment_data" style="display: none;">
                                                            <input type="text" id="mrw_select_name" maxlength="50" value placeholder="<?php echo __("Name", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            <input type="text" id="mrw_select_street" maxlength="30" value placeholder="<?php echo __("Street", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            <input type="text" id="mrw_select_number" maxlength="4" value placeholder="<?php echo __("Number", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            <input type="text" id="mrw_select_pc" maxlength="5" value placeholder="<?php echo __("Postal code", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            <input type="text" id="mrw_select_city" maxlength="30" value placeholder="<?php echo __("City", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            <input type="text" id="mrw_select_phone" maxlength="9" value placeholder="<?php echo __("Phone", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                            </div>
                                                            </div>
                                                            <!-- Fin Terceras -->
                                                            </div>
                                                            <div id="generate_form">
                                                            <input type="hidden" id="shipping_weight" value="<?php echo $mrwweight; ?>" />
                                                            <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number(); ?>" />
                                                            <input id="btn_generate_nac" type="button" method="POST" name="generate_submit" class="button-primary" value="<?php echo __('Generate label', 'woocommerce-mrw-carrier'); ?>"/>
                                                            <span id="msg_generate" style="display:none"><?php echo __('Generating label', 'woocommerce-mrw-carrier'); ?></span>
                                                            </div>
                                                            </form>
                                                            
                                                            <div id="generate_form">
                                                            <form action="">
                                                            <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                            <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number(); ?>" />
                                                            </form>
                                                            </div>
                                                            <div class="clear"></div>
                                                            <?php
                                                        } else {
                                                            $order_data = get_mrw_order_data();
                                                            $service = get_service_name($order_data['Service']);
                                                            $npackages = $order_data['NPack'];
                                                            $frandel = mrw_get_sn($order_data['FranDel']);
                                                            $satdel = mrw_get_sn($order_data['SatDev']);
                                                            $return = mrw_get_sn($order_data['Ret']);
                                                            $time_slot = mrw_get_ts($order_data['time_slot']);
                                                            $reference = $order_data['Reference'];
                                                            $comments = $order_data['Comm'];
                                                            $check_ad = $order_data['Third'];
                                                            $mrw_mp_flag = get_mrw_marketplaces_flag();
                                                            
                                                            if ($check_ad == 'true') {
                                                                
                                                                $new_name = $order_data['address_name'];
                                                                $new_street = $order_data['address_street'];
                                                                $new_number = $order_data['address_number'];
                                                                $new_pc = $order_data['address_pc'];
                                                                $new_city = $order_data['address_city'];
                                                                $new_phone = $order_data['address_phone'];
                                                            }
                                                            
                                                            ?>
                                                            <div id="mrw_container">
                                                            <div id="mrw_tracking_info_si" class="mrw_column">
                                                            <p class="mrw_data_title"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                            <span class="mrw_service_c"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo $service; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_package_number"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_npackages"><?php echo $npackages; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_franchisedel"><?php echo __('Delivery in franchise', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_franchisedel"><?php echo __($frandel, 'woocommerce-mrw-carrier'); ?></a></span>
                                                            <br/>
                                                            <span class="mrw_saturdaydel"><?php echo __('Deliver on Saturday', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_saturdaydel"><?php echo __($satdel, 'woocommerce-mrw-carrier'); ?></a></span>
                                                            <br/>
                                                            <span class="mrw_return"><?php echo __('With return', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_return"><?php echo __($return, 'woocommerce-mrw-carrier'); ?></a></span>
                                                            <br/>
                                                            <?php if ($order_data['Service'] == '0800') {?>
                                                                
                                                                <span class="mrw_timeslot"><?php echo __('Time slot', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_timeslot"><?php echo $time_slot; ?></a></span>
                                                                <br/>
                                                                <?php
                                                            }
                                                            ?>
                                                            <span class="shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                            <br/>
                                                            <span class="tracking_message"><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"><?php echo do_action('add_mrw_tracking_info', $tracking_number); ?></a></span>
                                                            <br/>
                                                            <span class="mrw_reference"><?php echo __('Reference', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_reference"><?php echo $reference; ?></a></span>
                                                            <span class="mrw_comment"><?php echo __('Comments', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_comments"><?php echo $comments; ?></a></span>
                                                            <br/>
                                                            </div>
                                                            
                                                            <div id="mrw_address_info_si" <?php if ($check_ad == 'true') {echo ' style="display: block;"';}?> class="mrw_column">
                                                            <a></a>
                                                            <p class="mrw_data_title"><?php echo __('Pick up address', 'woocommerce-mrw-carrier'); ?></p>
                                                            <span class="mrw_address_name"><?php echo __('Name', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_name"><?php echo $new_name; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_street"><?php echo __('Street', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_street"><?php echo $new_street; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_number"><?php echo __('Number', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_number"><?php echo $new_number; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_postalcode"><?php echo __('Postal code', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_pc"><?php echo $new_pc; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_city"><?php echo __('City', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_city"><?php echo $new_city; ?></a></span>
                                                            <br/>
                                                            <span class="mrw_address_phone"><?php echo __('Phone', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_addr_phone"><?php echo $new_phone; ?></a></span>
                                                            <br/>
                                                            </div>
                                                            </div>
                                                            <?php if (($mrw_mp_flag == 'yes' && $check_ad == 'true') || $check_ad == 'false') {?>
                                                                <div id="download_form">
                                                                <form action="">
                                                                <a href="<?php echo download_mrw_label($tracking_number); ?>" download=""><input id="btn_download" type="button" name="download_submit" class="button-primary" value="<?php echo __('Download label', 'woocommerce-mrw-carrier'); ?>"/></a>
                                                                </form>
                                                                </div>
                                                                <?php }?>
                                                                <div class="clear"></div>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    
                                                    //Controls the buttons for generating and visualizate MRW labels international.
                                                    function order_mrw_int()
                                                    {
                                                        if (defined('WC_VERSION') && version_compare(WC_VERSION, '3.0', '>=')) {
                                                            
                                                            global $post, $woocommerce;
                                                            $order_id = $post->ID;
                                                            if (isset($order_id)) {
                                                                $order = new WC_Order($order_id);
                                                            }
                                                            
                                                            $mrwdefaultservice = get_mrw_default_service_int();
                                                            $mrwavailableservices = get_mrw_available_services_int();
                                                            
                                                            //Check if the label is already generated
                                                            $tracking_number = get_mrw_tracking_number3();
                                                            
                                                            $service = null;
                                                            $npack = null;
                                                            
                                                            //Get the weight of the order from get_mrw_weight()
                                                            $mrwproducts = $order->get_items();
                                                            $mrwweight = get_mrw_weight3($mrwproducts);
                                                            
                                                            if ($tracking_number == null) {
                                                                
                                                                ?>
                                                                <div id="mrw_container">
                                                                <?php 
                                                                if (!soap_active()) {
                                                                    ?>
                                                                    <p class="mrw_soap"><?php echo __('Class SOAP must be active in order to generate labels', 'woocommerce-mrw-carrier'); ?></p>
                                                                    <?php        
                                                                }
                                                                ?>
                                                                <form action="" method="POST">
                                                                <div id="mrw_tracking_info_no" class="mrw_column">
                                                                <p class="mrw_data_title" style="bold"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                                <span class="mrw_service"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo get_service_name($service); ?></a></span>
                                                                <br/>
                                                                <span class="mrw_package_number"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_npackages"><?php echo $npack; ?></a></span>
                                                                <br/>
                                                                <span class="tracking_num shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                                <br/>
                                                                <span class="tracking_info tracking_message" value=""><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"></a></span>
                                                                <br/>
                                                                </div>
                                                                
                                                                <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                                <input type="hidden" id="shipping_company" value="<?php echo $order->get_shipping_company(); ?>" />
                                                                <input type="hidden" id="shipping_first_name" value="<?php echo $order->get_shipping_first_name(); ?>" />
                                                                <input type="hidden" id="shipping_last_name" value="<?php echo $order->get_shipping_last_name(); ?>" />
                                                                <input type="hidden" id="shipping_address_1" value="<?php echo $order->get_shipping_address_1(); ?>" />
                                                                <input type="hidden" id="shipping_address_2" value="<?php echo $order->get_shipping_address_2(); ?>" />
                                                                <input type="hidden" id="shipping_postcode" value="<?php echo $order->get_shipping_postcode(); ?>" />
                                                                <input type="hidden" id="shipping_city" value="<?php echo $order->get_shipping_city(); ?>" />
                                                                <input type="hidden" id="shipping_state" value="<?php echo $order->get_shipping_state(); ?>" />
                                                                <input type="hidden" id="shipping_country" value="<?php echo $order->get_shipping_country(); ?>" />
                                                                <input type="hidden" id="billing_email" value="<?php echo $order->get_billing_email(); ?>" />
                                                                <input type="hidden" id="billing_phone" value="<?php echo $order->get_billing_phone(); ?>" />
                                                                
                                                                <div id="shipment_data" class="mrw_column">
                                                                <p class="mrw_info"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:
                                                                <select id="mrw_select_service" value="<?php echo $mrwdefaultservice; ?>" name="mrw_select_service">
                                                                <?php
                                                                foreach ($mrwavailableservices as $mrw_service => $mrw_service_name) {
                                                                    if ($mrw_service == $mrwdefaultservice) {
                                                                        echo '<option value="' . $mrw_service . '" selected>' . $mrw_service_name . '</option>';
                                                                    } else {
                                                                        echo '<option value="' . $mrw_service . '">' . $mrw_service_name . '</option>';
                                                                    }
                                                                }?>
                                                                </select>
                                                                </p>
                                                                </form></p>
                                                                <p class="mrw_info"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<input type="number" id="mrw_select_npackages" maxlength="2" value = "1" min="1" max="99"/></p>
                                                                <p class="mrw_info"><?php echo __('Total weight (Kg)', 'woocommerce-mrw-carrier'); ?>:<input type="number" id="mrw_select_weight" maxlength="3" value = "<?php echo $mrwweight?>" min="1" max="999"/></p>
                                                                <p class="mrw_info"><?php echo __('Reference', 'woocommerce-mrw-carrier'); ?>:<input type="text" id="mrw_select_reference" maxlength="25" value placeholder="<?php echo __("Write a reference (Optional)", 'woocommerce-mrw-carrier'); ?>"/></p>
                                                                </div>
                                                                </div>
                                                                <div id="generate_form">
                                                                <input type="hidden" id="shipping_weight" value="<?php echo $mrwweight; ?>" />
                                                                <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number3(); ?>" />
                                                                <input id="btn_generate_int" type="button" method="POST" name="generate_submit" class="button-primary" value="<?php echo __('Generate label', 'woocommerce-mrw-carrier'); ?>"/>
                                                                <span id="msg_generate" style="display:none"><?php echo __('Generating label', 'woocommerce-mrw-carrier'); ?></span>
                                                                </div>
                                                                </form>
                                                                
                                                                <div id="generate_form">
                                                                <form action="">
                                                                <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                                <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number3(); ?>" />
                                                                </form>
                                                                </div>
                                                                <div class="clear"></div>
                                                                <?php
                                                            } else {
                                                                
                                                                $order_data = get_mrw_order_data3();
                                                                $service = get_service_name($order_data['Service']);
                                                                $reference = $order_data['Reference'];
                                                                $npackages = $order_data['NPack'];
                                                                
                                                                ?>
                                                                <div id="mrw_container">
                                                                <div id="mrw_tracking_info_si" class="mrw_column">
                                                                <p class="mrw_data_title"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                                <span class="mrw_service_c"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo $service; ?></a></span>
                                                                <br/>
                                                                <span class="mrw_package_number"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_npackages"><?php echo $npackages; ?></a></span>
                                                                <br/>
                                                                <span class="shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                                <br/>
                                                                <span class="tracking_message"><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"><?php echo do_action('add_mrw_tracking_info_int', $tracking_number); ?></a></span>
                                                                <br/>
                                                                </div>
                                                                </div>
                                                                
                                                                <div id="download_form">
                                                                <form action="">
                                                                <a href="<?php echo download_mrw_label($tracking_number); ?>" download=""><input id="btn_download" type="button" name="download_submit" class="button-primary" value="<?php echo __('Download label', 'woocommerce-mrw-carrier'); ?>"/></a>
                                                                </form>
                                                                </div>
                                                                <div class="clear"></div>
                                                                <?php
                                                            }
                                                        } else {
                                                            global $post, $woocommerce;
                                                            $order_id = $post->ID;
                                                            if (isset($order_id)) {
                                                                $order = new WC_Order($order_id);
                                                            }
                                                            
                                                            $mrwdefaultservice = get_mrw_default_service_int();
                                                            $mrwavailableservices = get_mrw_available_services_int();
                                                            
                                                            //Check if the label is already generated
                                                            $tracking_number = get_mrw_tracking_number();
                                                            
                                                            $service = null;
                                                            $npack = null;
                                                            
                                                            //Get the weight of the order from get_mrw_weight()
                                                            $mrwproducts = $order->get_items();
                                                            $mrwweight = get_mrw_weight($mrwproducts);
                                                            
                                                            if ($tracking_number == null) {
                                                                
                                                                ?>
                                                                <div id="mrw_container">
                                                                <form action="" method="POST">
                                                                <div id="mrw_tracking_info_no" class="mrw_column">
                                                                <p class="mrw_data_title" style="bold"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                                <span class="mrw_service"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo get_service_name($service); ?></a></span>
                                                                <br/>
                                                                <span class="tracking_num shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                                <br/>
                                                                <span class="tracking_info tracking_message" value=""><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"></a></span>
                                                                <br/>
                                                                </div>
                                                                
                                                                <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                                <input type="hidden" id="shipping_company" value="<?php echo $order->shipping_company; ?>" />
                                                                <input type="hidden" id="shipping_first_name" value="<?php echo $order->shipping_first_name; ?>" />
                                                                <input type="hidden" id="shipping_last_name" value="<?php echo $order->shipping_last_name; ?>" />
                                                                <input type="hidden" id="shipping_address_1" value="<?php echo $order->shipping_address_1; ?>" />
                                                                <input type="hidden" id="shipping_address_2" value="<?php echo $order->shipping_address_2; ?>" />
                                                                <input type="hidden" id="shipping_postcode" value="<?php echo $order->shipping_postcode; ?>" />
                                                                <input type="hidden" id="shipping_city" value="<?php echo $order->shipping_city; ?>" />
                                                                <input type="hidden" id="shipping_state" value="<?php echo $order->shipping_state; ?>" />
                                                                <input type="hidden" id="shipping_country" value="<?php echo $order->get_shipping_country(); ?>" />
                                                                <input type="hidden" id="billing_email" value="<?php echo $order->billing_email; ?>" />
                                                                <input type="hidden" id="billing_phone" value="<?php echo $order->billing_phone; ?>" />
                                                                
                                                                <div id="shipment_data" class="mrw_column">
                                                                <p class="mrw_info"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:
                                                                <select id="mrw_select_service" value="<?php echo $mrwdefaultservice; ?>">
                                                                <?php
                                                                foreach ($mrwavailableservices as $mrw_service => $mrw_service_name) {
                                                                    if ($mrw_service == $mrwdefaultservice) {
                                                                        echo '<option value="' . $mrw_service . '" selected>' . $mrw_service_name . '</option>';
                                                                    } else {
                                                                        echo '<option value="' . $mrw_service . '">' . $mrw_service_name . '</option>';
                                                                    }
                                                                }?>
                                                                </select>
                                                                </p>
                                                                </form></p>
                                                                <p class="mrw_info"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<input type="number" id="mrw_select_npackages" maxlength="2" value = "1" min="1" max="99"/></p>
                                                                </div>
                                                                </div>
                                                                <div id="generate_form">
                                                                <input type="hidden" id="shipping_weight" value="<?php echo $mrwweight; ?>" />
                                                                <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number(); ?>" />
                                                                <input id="btn_generate_int" type="button" method="POST" name="generate_submit" class="button-primary" value="<?php echo __('Generate label', 'woocommerce-mrw-carrier'); ?>"/>
                                                                <span id="msg_generate" style="display:none"><?php echo __('Generating label', 'woocommerce-mrw-carrier'); ?></span>
                                                                </div>
                                                                </form>
                                                                
                                                                <div id="generate_form">
                                                                <form action="">
                                                                <input type="hidden" id="order_id" value="<?php echo $order_id; ?>" />
                                                                <input type="hidden" id="mrw_tracking_number" value="<?php echo get_mrw_tracking_number(); ?>" />
                                                                </form>
                                                                </div>
                                                                <div class="clear"></div>
                                                                <?php
                                                            } else {
                                                                
                                                                $order_data = get_mrw_order_data();
                                                                $service = get_service_name($order_data['Service']);
                                                                $reference = $order_data['Reference'];
                                                                $npackages = $order_data['NPack'];
                                                                
                                                                ?>
                                                                <div id="mrw_container">
                                                                <div id="mrw_tracking_info_si" class="mrw_column">
                                                                <p class="mrw_data_title"><?php echo __('Shipment information', 'woocommerce-mrw-carrier'); ?></p>
                                                                <span class="mrw_service_c"><?php echo __('Service', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_service"><?php echo $service; ?></a></span>
                                                                <br/>
                                                                <span class="mrw_package_number"><?php echo __('Number of packages', 'woocommerce-mrw-carrier'); ?>:<a id="mrw_npackages"><?php echo $npackages; ?></a></span>
                                                                <br/>
                                                                <span class="shipping_message"><?php echo __('Shipping number:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_num"><?php echo $tracking_number; ?></a></span>
                                                                <br/>
                                                                <span class="tracking_message"><?php echo __('Shipping information:', 'woocommerce-mrw-carrier'); ?><a id="mrw_tracking_info"><?php echo do_action('add_mrw_tracking_info_int', $tracking_number); ?></a></span>
                                                                <br/>
                                                                </div>
                                                                </div>
                                                                <div id="download_form">
                                                                <form action="">
                                                                <a href="<?php echo download_mrw_label($tracking_number); ?>" download=""><input id="btn_download" type="button" name="download_submit" class="button-primary" value="<?php echo __('Download label', 'woocommerce-mrw-carrier'); ?>"/></a>
                                                                </form>
                                                                </div>
                                                                <div class="clear"></div>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    
                                                    /*Function to generate the order label for national shipping*/
                                                    add_action('wp_ajax_generate_mrw_label_nac', 'generate_mrw_label_nac');
                                                    function generate_mrw_label_nac()
                                                    {
                                                        global $wpdb, $woocommerce, $post;
                                                        $wsdl_url = '';
                                                        $wsdl_url2 = '';
                                                        
                                                        if (is_ssl()) {
                                                            //action to take for page using SSL
                                                            $wsdl_pro = 'https://sagec.mrw.es/';
                                                            $wsdl_test = 'https://sagec-test.mrw.es/';
                                                        } else {
                                                            $wsdl_pro = 'http://sagec.mrw.es/';
                                                            $wsdl_test = 'http://sagec-test.mrw.es/';
                                                        }
                                                        
                                                        $url = '';
                                                        $mrw_tracking_number = '';
                                                        $mrwaddress = '';
                                                        $notifications_mrw = array();
                                                        $track_info = array();
                                                        $options = array();
                                                        $mrw_pickup_address = array();
                                                        $label_name = null;
                                                        $label_to = null;
                                                        
                                                        //Get order settings
                                                        $billing_phone = $_POST['billing_phone'];
                                                        $billing_email = $_POST['billing_email'];
                                                        $shipping_address = $_POST['shipping_address'];
                                                        $shipping_postcode = $_POST['shipping_postcode'];
                                                        $shipping_first_name = $_POST['shipping_first_name'];
                                                        $shipping_last_name = $_POST['shipping_last_name'];
                                                        //$shipping_weight = $_POST['shipping_weight'];
                                                        $shipping_weight = $_POST['select_total_weight'];
                                                        $shipping_city = $_POST['shipping_city'];
                                                        $order_id = $_POST['order_id'];
                                                        $mrw_saturday_delivery = $_POST['select_saturdayd'];
                                                        $mrw_franchise_delivery = $_POST['select_franchised'];
                                                        $mrw_return = $_POST['select_return'];
                                                        $mrw_packages = $_POST['select_npackages'];
                                                        $mrw_reference = $_POST['select_reference'];
                                                        $mrw_comments = $_POST['select_comments'];
                                                        $mrw_service = $_POST['select_service'];
                                                        $mrw_new_name = $_POST['select_name'];
                                                        $mrw_new_street = $_POST['select_street'];
                                                        $mrw_new_number = $_POST['select_number'];
                                                        $mrw_new_pc = $_POST['select_pc'];
                                                        $mrw_new_city = $_POST['select_city'];
                                                        $mrw_new_phone = $_POST['select_phone'];
                                                        $mrw_check_address = $_POST['check_address'];
                                                        $mrw_company_name = $_POST['company_name'];
                                                        $mrw_time_slot = $_POST['time_slot'];
                                                        
                                                        //If company name is filled put in the label name, else, put customer name
                                                        if (empty($mrw_company_name) || $mrw_company_name == "-") {
                                                            
                                                            $label_name = $shipping_first_name . ' ' . $shipping_last_name;
                                                            
                                                        } else {
                                                            
                                                            $label_name = $mrw_company_name;
                                                            $label_to = $shipping_first_name . ' ' . $shipping_last_name;
                                                        }
                                                        
                                                        //Options of the order Franchise delivery, Saturday delivery, Return, number of packages, comments, service, picking up address.
                                                        $options = array(
                                                            'Service' => $mrw_service,
                                                            'NPack' => $mrw_packages,
                                                            'SatDev' => $mrw_saturday_delivery,
                                                            'FranDel' => $mrw_franchise_delivery,
                                                            'Ret' => $mrw_return,
                                                            'Reference' => $mrw_reference,
                                                            'Comm' => $mrw_comments,
                                                            'Third' => $mrw_check_address,
                                                            'address_name' => $mrw_new_name,
                                                            'address_street' => $mrw_new_street,
                                                            'address_number' => $mrw_new_number,
                                                            'address_pc' => $mrw_new_pc,
                                                            'address_city' => $mrw_new_city,
                                                            'address_phone' => $mrw_new_phone,
                                                            'time_slot' => $mrw_time_slot,
                                                        );
                                                        
                                                        //COD Checking
                                                        $order = new WC_Order($order_id);
                                                        
                                                        $codOrigin = '';
                                                        $codAmount = null;
                                                        
                                                        //Check payment method to check if is COD method. By default cod
                                                        if (get_post_meta($order->get_id(), '_payment_method', true) == "cod") {
                                                            $codOrigin = 'O';
                                                            
                                                            //Wordpress takes . as decimal character, change to ,
                                                            $codAmount = number_format($order->get_total(), 2, ',', ' ');
                                                            
                                                        }
                                                        $options_table = $wpdb->prefix . "options";
                                                        //Get woocommerce_mrw_carrier settings.
                                                        $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                        $mrwsettings = get_object_vars($mrwsettings[0]);
                                                        $mrwsettings = unserialize($mrwsettings['option_value']);
                                                        
                                                        //If the service is 0000, 0100, 0110, 0120 or 200 do package apportionment.
                                                        if ($mrw_service == '0000' || $mrw_service == '0100' || $mrw_service == '0110' || $mrw_service == '0120' || $mrw_service == '0200') {
                                                            $mrwsettings['mrwapportionment'] = 'yes';
                                                        }
                                                        
                                                        //If the service is Ecommerce, set time_slot variable
                                                        if ($mrw_service == '0800') {
                                                            $time_slot_value = $mrw_time_slot;
                                                        } else { $time_slot_value = 0;}
                                                        
                                                        //Check if we are in real or development mode.
                                                        if ($mrwsettings['mrwtype'] == 'development') {
                                                            $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
                                                        } else {
                                                            $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';
                                                        }
                                                        
                                                        //Fill in the notificacions array.
                                                        if ($mrw_franchise_delivery == 'E') {
                                                            if ($mrwsettings['mrwnotifications'] == 'sms' && !empty($billing_phone)) {
                                                                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '3', 'MailSMS' => $billing_phone);
                                                            } else if ($mrwsettings['mrwnotifications'] == 'email' && !empty($billing_email)) {
                                                                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '3', 'MailSMS' => $billing_email);
                                                            } else if ($mrwsettings['mrwnotifications'] == 'sms+email') {
                                                                if (!empty($order->billing_phone) && !empty($billing_email)) {
                                                                    $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '3', 'MailSMS' => $billing_phone);
                                                                    $notifications_mrw['NotificacionRequest'][1] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '3', 'MailSMS' => $billing_email);
                                                                } else if (!empty($order->billing_phone) && empty($order->billing_email)) {
                                                                    $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '3', 'MailSMS' => $billing_phone);
                                                                } else if (empty($order->billing_phone) && !empty($order->billing_email)) {
                                                                    $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '3', 'MailSMS' => $order->billing_email);
                                                                }
                                                            }
                                                        } else {
                                                            if ($mrwsettings['mrwnotifications'] == 'sms' && !empty($billing_phone)) {
                                                                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '4', 'MailSMS' => $billing_phone);
                                                            } else if ($mrwsettings['mrwnotifications'] == 'email' && !empty($billing_email)) {
                                                                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '4', 'MailSMS' => $billing_email);
                                                            } else if ($mrwsettings['mrwnotifications'] == 'sms+email') {
                                                                if (!empty($order->billing_phone) && !empty($billing_email)) {
                                                                    $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '4', 'MailSMS' => $billing_phone);
                                                                    $notifications_mrw['NotificacionRequest'][1] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '4', 'MailSMS' => $billing_email);
                                                                } else if (!empty($order->billing_phone) && empty($order->billing_email)) {
                                                                    $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '4', 'MailSMS' => $billing_phone);
                                                                } else if (empty($order->billing_phone) && !empty($order->billing_email)) {
                                                                    $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '4', 'MailSMS' => $order->billing_email);
                                                                }
                                                            }
                                                        }
                                                        
                                                        //Change . , because the web service gets the weight with ,
                                                        $shipping_weight = str_replace(".", ",", round($shipping_weight, 2, PHP_ROUND_HALF_UP));
                                                        
                                                        //Package apportionment.
                                                        if ($mrwsettings['mrwapportionment'] == 'yes') {
                                                            if ($mrwsettings['mrwerrorlog'] == 'yes') {
                                                                mrw_log("Realizamos desglose de bultos (Nº de bultos)", $mrw_packages);
                                                            }
                                                            if ($mrw_packages > 1) {
                                                                for ($i = 0; $i < $mrw_packages; $i++) {
                                                                    $mrw_apportion['BultoRequest'][$i] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Bulto ' . $i . ' de ' . $mrw_packages, 'Peso' => str_replace(".", ",", round($shipping_weight / $mrw_packages, 2, PHP_ROUND_HALF_UP)));
                                                                }
                                                            } else {
                                                                $mrw_apportion['BultoRequest'] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Ref 1 ', 'Peso' => str_replace(".", ",", round($shipping_weight, 2, PHP_ROUND_HALF_UP)));
                                                            }
                                                        } else {
                                                            $mrw_apportion = '';
                                                        }
                                                        
                                                        //Change pick up address
                                                        if ($mrw_check_address === 'true') {
                                                            
                                                            $mrw_pickup_address = array(
                                                                'Direccion' => array(
                                                                    'Via' => $mrw_new_street,
                                                                    'Numero' => $mrw_new_number,
                                                                    'CodigoPostal' => $mrw_new_pc,
                                                                    'Poblacion' => $mrw_new_city,
                                                                ),
                                                                'Nombre' => $mrw_new_name,
                                                                'Telefono' => $mrw_new_phone,
                                                            );
                                                            
                                                            if ($mrwsettings['mrwerrorlog'] == 'yes') {
                                                                mrw_log("Se cambia la dirección de recogida por defecto", json_encode($mrw_pickup_address));
                                                            }
                                                        } else {
                                                            
                                                            $mrw_pickup_address = array(
                                                                'Direccion' => array(
                                                                    'Via' => '',
                                                                    'Numero' => '',
                                                                    'CodigoPostal' => '',
                                                                    'Poblacion' => '',
                                                                ),
                                                                'Nombre' => '',
                                                                'Telefono' => '',
                                                            );
                                                        }
                                                        
                                                        // Create the SoapClient instance.
                                                        $clientMRW = new SoapClient($wsdl_url, array('trace' => true));
                                                        
                                                        $cabeceras = array(
                                                            'CodigoFranquicia' => $mrwsettings['mrwfranchise'],
                                                            'CodigoAbonado' => $mrwsettings['mrwsubscriber'],
                                                            'CodigoDepartamento' => $mrwsettings['mrwdepartment'], //Optional
                                                            'UserName' => $mrwsettings['mrwuser'],
                                                            'Password' => $mrwsettings['mrwpass'],
                                                        );
                                                        
                                                        // Create the header.
                                                        $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $cabeceras); // Headers over the SOAP client object
                                                        $clientMRW->__setSoapHeaders($header);
                                                        
                                                        $today = date("d/m/Y", time());
                                                        $parametros = array(
                                                            'request' => array(
                                                                'DatosRecogida' => $mrw_pickup_address,
                                                                'DatosEntrega' => array(
                                                                    ## DATOS DESTINATARIO ##
                                                                    'Direccion' => array(
                                                                        'CodigoDireccion' => '' //Optional
                                                                        , 'CodigoTipoVia' => '' //Optional
                                                                        , 'Via' => $shipping_address
                                                                        , 'Numero' => ''
                                                                        , 'Resto' => '' //Optional
                                                                        , 'CodigoPostal' => $shipping_postcode
                                                                        , 'Poblacion' => $shipping_city //Obligatorio
                                                                        , 'CodigoPais' => '', //Optional
                                                                        )
                                                                        , 'Nif' => ''
                                                                        , 'Nombre' => $label_name
                                                                        , 'Telefono' => empty($billing_phone) ? ' ' : $billing_phone //Optional
                                                                        , 'Contacto' => $label_to //Optional
                                                                        , 'ALaAtencionDe' => $label_to
                                                                        , 'Observaciones' => $mrw_comments, //Optional
                                                                        
                                                                        )
                                                                        ## DATOS DEL SERVICIO ##
                                                                        , 'DatosServicio' => array(
                                                                            'Fecha' => $today //Today or after
                                                                            , 'Referencia' =>  empty($mrw_reference) ? $order_id : $mrw_reference
                                                                            , 'EnFranquicia' => $mrw_franchise_delivery
                                                                            //   N = Entrega en domicilio (por defecto si se omite)
                                                                            //   E = Entrega en franquicia. El destinatario recogera en delegacion mas proxima
                                                                            , 'CodigoServicio' => $mrw_service
                                                                            , 'DescripcionServicio' => '' //Optional
                                                                            , 'Bultos' => $mrw_apportion //Optional
                                                                            , 'NumeroBultos' => $mrw_packages
                                                                            , 'Peso' => $shipping_weight
                                                                            , 'EntregaSabado' => $mrw_saturday_delivery //'N'
                                                                            , 'Retorno' => $mrw_return //Optional
                                                                            , 'Reembolso' => $codOrigin //Optional
                                                                            , 'ImporteReembolso' => $codAmount //If COD is selected is mandatory to inform the cost. Indicate decimals with , (coma)
                                                                            , 'Notificaciones' => $notifications_mrw
                                                                            , 'TramoHorario' => empty($time_slot_value) ? '0' : $time_slot_value, //Optional (additional charge)
                                                                            // 0 = Sin tramo (8:30h a 19h). Por defecto si se omite.
                                                                            // 1 = Mañana (8:30h a 14h)
                                                                            // 2 = Tarde (14h a 19h)
                                                                            //,'PortesDebidos' => 'N' //Opcional - Se debe omitir si el abonado no lo tiene habilitado en el sistema
                                                                        ),
                                                                    ),
                                                                );
                                                                
                                                                $responseCode = $clientMRW->TransmEnvio($parametros);
                                                                
                                                                //Save generate request
                                                                if ($mrwsettings['mrwerrorlog'] == 'yes') {
                                                                    mrw_log("MRW Generate Label Request", $clientMRW->__getLastRequest());
                                                                    
                                                                    if (!empty($responseCode->TransmEnvioResult->Mensaje)) {
                                                                        mrw_log("MRW Generate Message Request", $responseCode->TransmEnvioResult->Mensaje);
                                                                    }
                                                                    
                                                                }
                                                                
                                                                //Save in database
                                                                $table_name = $wpdb->prefix . 'mrw_orders';
                                                                
                                                                if ($mrwsettings['mrwtype'] == 'live') {
                                                                    $wsdl_url2 = $wsdl_pro;
                                                                } else if ($mrwsettings['mrwtype'] == 'development') {
                                                                    $wsdl_url2 = $wsdl_test;
                                                                }
                                                                
                                                                if ($responseCode->TransmEnvioResult->Estado == '1' && $responseCode->TransmEnvioResult->NumeroEnvio) {
                                                                    $mrw_tracking_number = $responseCode->TransmEnvioResult->NumeroEnvio;
                                                                    $num_sol = $responseCode->TransmEnvioResult->NumeroSolicitud;
                                                                    $url = $wsdl_url2 . "Panel.aspx?Franq=" . $mrwsettings['mrwfranchise'] . "&Ab=" . $mrwsettings['mrwsubscriber'] . "&Dep=" . $mrwsettings['mrwdepartment'] . "&Usr=" . $mrwsettings['mrwuser'] . "&Pwd=" . $mrwsettings['mrwpass'] . "&NumSol=" . $num_sol . "&NumEnv=" . $mrw_tracking_number;
                                                                    
                                                                    $wpdb->insert(
                                                                        $table_name,
                                                                        array(
                                                                            'order_id' => $order_id,
                                                                            'tracking_number' => $mrw_tracking_number,
                                                                            'URL' => $url,
                                                                            'options' => serialize($options),
                                                                            'date' => date("Y-m-d H:i:s"),
                                                                            )
                                                                        );
                                                                        
                                                                        //Download mrw label
                                                                        if ($mrwsettings['mrwtype'] == 'live') {
                                                                            $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';
                                                                        } else if ($mrwsettings['mrwtype'] == 'development') {
                                                                            $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
                                                                        }
                                                                        
                                                                        //SOAP Request
                                                                        $clientMRW = new SoapClient($wsdl_url, array('trace' => true));
                                                                        
                                                                        $headers = array(
                                                                            'CodigoFranquicia' => $mrwsettings['mrwfranchise'],
                                                                            'CodigoAbonado' => $mrwsettings['mrwsubscriber'],
                                                                            'CodigoDepartamento' => $mrwsettings['mrwdepartment'],
                                                                            'UserName' => $mrwsettings['mrwuser'],
                                                                            'Password' => $mrwsettings['mrwpass']);
                                                                            
                                                                            $params = array(
                                                                                'request' => array(
                                                                                    'NumeroEnvio' => $mrw_tracking_number,
                                                                                    'SeparadorNumerosEnvio' => ';',
                                                                                    'FechaInicioEnvio' => '',
                                                                                    'FechaFinEnvio' => '',
                                                                                    'TipoEtiquetaEnvio' => '0',
                                                                                    'ReportTopMargin' => '1100',
                                                                                    'ReportLeftMargin' => '650',
                                                                                ),
                                                                            );
                                                                            
                                                                            $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $headers);
                                                                            
                                                                            $clientMRW->__setSoapHeaders($header);
                                                                            
                                                                            $responseCode = $clientMRW->EtiquetaEnvio($params);
                                                                            
                                                                            if ($mrwsettings['mrwerrorlog'] == 'yes') {
                                                                                mrw_log("MRW Download Label Request", $clientMRW->__getLastRequest());
                                                                                
                                                                                if (!empty($responseCode->GetEtiquetaEnvioResult->Mensaje)) {
                                                                                    mrw_log("MRW Download Message Request", $responseCode->GetEtiquetaEnvioResult->Mensaje);
                                                                                }
                                                                                
                                                                            }
                                                                            
                                                                            //Get label code
                                                                            $pdf_code = $responseCode->GetEtiquetaEnvioResult->EtiquetaFile;
                                                                            
                                                                            //Path for downloading
                                                                            $urlUpload = wp_upload_dir();
                                                                            $MRWFolder = $urlUpload['basedir'] . '/MRW/';
                                                                            $urlLabel = $MRWFolder . $mrw_tracking_number . '.pdf';
                                                                            
                                                                            $pdf = fopen($urlLabel, 'w');
                                                                            fputs($pdf, $pdf_code);
                                                                            fclose($pdf);
                                                                            
                                                                            if ($mrw_check_address == true) {
                                                                                $json_arr = array(
                                                                                    "mrw_tracking_number" => $mrw_tracking_number,
                                                                                    "url_label" => $urlLabel,
                                                                                    "message" => __('There is no tracking information yet', 'woocommerce-mrw-carrier'),
                                                                                    "success" => __('The shipping label was generated successfully!', 'woocommerce-mrw-carrier'),
                                                                                    "service" => get_service_name($mrw_service),
                                                                                    "npack" => $mrw_packages,
                                                                                    "frandel" => __(mrw_get_sn($mrw_franchise_delivery), 'woocommerce-mrw-carrier'),
                                                                                    "satdel" => __(mrw_get_sn($mrw_saturday_delivery), 'woocommerce-mrw-carrier'),
                                                                                    "ret" => __(mrw_get_sn($mrw_return), 'woocommerce-mrw-carrier'),
                                                                                    "time_slot" => __(mrw_get_ts($time_slot_value), 'woocommerce-mrw-carrier'),
                                                                                    "reference" => $mrw_reference,
                                                                                    "comm" => $mrw_comments, 
                                                                                    "state" => 1,
                                                                                    "ad_check" => $mrw_check_address,
                                                                                    "ad_name" => $mrw_new_name,
                                                                                    "ad_street" => $mrw_new_street,
                                                                                    "ad_number" => $mrw_new_number,
                                                                                    "ad_pc" => $mrw_new_pc,
                                                                                    "ad_city" => $mrw_new_city,
                                                                                    "ad_phone" => $mrw_new_phone,
                                                                                );
                                                                            } else {
                                                                                $json_arr = array(
                                                                                    "mrw_tracking_number" => $mrw_tracking_number,
                                                                                    "url_label" => $urlLabel,
                                                                                    "message" => __('There is no tracking information yet', 'woocommerce-mrw-carrier'),
                                                                                    "success" => __('The shipping label was generated successfully!', 'woocommerce-mrw-carrier'),
                                                                                    "service" => get_service_name($mrw_service),
                                                                                    "npack" => $mrw_packages,
                                                                                    "frandel" => __(mrw_get_sn($mrw_franchise_delivery), 'woocommerce-mrw-carrier'),
                                                                                    "satdel" => __(mrw_get_sn($mrw_saturday_delivery), 'woocommerce-mrw-carrier'),
                                                                                    "ret" => __(mrw_get_sn($mrw_return), 'woocommerce-mrw-carrier'),
                                                                                    "time_slot" => __(mrw_get_ts($time_slot), 'woocommerce-mrw-carrier'),
                                                                                    "reference" => $mrw_reference,
                                                                                    "comm" => $mrw_comments, 
                                                                                    "state" => 1);
                                                                                }
                                                                                
                                                                                $order->update_status($mrwsettings['mrwstatus'], 'order_note');
                                                                                
                                                                                //Return JSON
                                                                                echo json_encode($json_arr);
                                                                            }
                                                                            //If the generation fails, show the error.
                                                                            else {
                                                                                $json_arr = array("nosuccess" => __($responseCode->TransmEnvioResult->Mensaje, 'woocommerce-mrw-carrier'), "state" => 0);
                                                                                echo json_encode($json_arr);
                                                                            }
                                                                            
                                                                            wp_die();
                                                                        }
                                                                        
                                                                        /*Function to generate the order label for national shipping*/
                                                                        add_action('wp_ajax_generate_mrw_label_int', 'generate_mrw_label_int');
                                                                        function generate_mrw_label_int()
                                                                        {
                                                                            global $wpdb, $woocommerce, $post;
                                                                            $wsdl_url = '';
                                                                            $wsdl_url2 = '';
                                                                            
                                                                            if (is_ssl()) {
                                                                                //action to take for page using SSL
                                                                                $wsdl_pro = 'https://sagec.mrw.es/';
                                                                                $wsdl_test = 'https://sagec-test.mrw.es/';
                                                                            } else {
                                                                                $wsdl_pro = 'http://sagec.mrw.es/';
                                                                                $wsdl_test = 'http://sagec-test.mrw.es/';
                                                                            }
                                                                            
                                                                            $url = '';
                                                                            $mrw_tracking_number = '';
                                                                            $notifications_mrw = array();
                                                                            $track_info = array();
                                                                            $options = array();
                                                                            $label_name = null;
                                                                            $label_to = null;
                                                                            
                                                                            //Get order settings
                                                                            $billing_phone = $_POST['billing_phone'];
                                                                            $billing_email = $_POST['billing_email'];
                                                                            $shipping_address = $_POST['shipping_address'];
                                                                            $shipping_postcode = $_POST['shipping_postcode'];
                                                                            $shipping_first_name = $_POST['shipping_first_name'];
                                                                            $shipping_last_name = $_POST['shipping_last_name'];
                                                                            //$shipping_weight = $_POST['shipping_weight'];
                                                                            $shipping_weight = $_POST['select_total_weight'];
                                                                            $shipping_city = $_POST['shipping_city'];
                                                                            $shipping_state = $_POST['shipping_state'];
                                                                            $shipping_country = $_POST['shipping_country'];
                                                                            $order_id = $_POST['order_id'];
                                                                            $mrw_packages = $_POST['select_npackages'];
                                                                            $mrw_service = $_POST['select_service'];
                                                                            $mrw_company_name = $_POST['company_name'];
                                                                            $mrw_reference = $_POST['reference'];
                                                                            
                                                                            //If company name is filled put in the label name, else, put customer name
                                                                            if (empty($mrw_company_name) || $mrw_company_name == "-") {
                                                                                $label_name = $shipping_first_name . ' ' . $shipping_last_name;
                                                                            } else {
                                                                                $label_name = $mrw_company_name;
                                                                                $label_to = $shipping_first_name . ' ' . $shipping_last_name;
                                                                            }
                                                                            
                                                                            //Options of the order service, parcels.
                                                                            $options = array(
                                                                                'Service' => $mrw_service,
                                                                                'NPack' => $mrw_packages,
                                                                                'Reference' => $mrw_reference
                                                                            );
                                                                            
                                                                            //COD Checking
                                                                            $order = new WC_Order($order_id);
                                                                            
                                                                            $codOrigin = '';
                                                                            $codAmount = null;
                                                                            
                                                                            $options_table = $wpdb->prefix . "options";
                                                                            //Get woocommerce_mrw_carrier settings.
                                                                            $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                            $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                            $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                            
                                                                            //Check if we are in real or development mode.
                                                                            if ($mrwsettings['mrwtype'] == 'development') {
                                                                                $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
                                                                            } else {
                                                                                $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';
                                                                            }
                                                                            
                                                                            //Change . , because the web service gets the weight with ,
                                                                            $shipping_weight = str_replace(".", ",", round($shipping_weight, 2, PHP_ROUND_HALF_UP));
                                                                            
                                                                            //Package apportionment.
                                                                            if ($mrwsettings['mrwapportionment'] == 'yes') {
                                                                                if ($mrwsettings['mrwerrorlog'] == 'yes') {
                                                                                    mrw_log("Realizamos desglose de bultos (Nº de bultos)", $mrw_packages);
                                                                                }
                                                                                if ($mrw_packages > 1) {
                                                                                    for ($i = 0; $i < $mrw_packages; $i++) {
                                                                                        $mrw_apportion['BultoRequest'][$i] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Bulto ' . $i . ' de ' . $mrw_packages, 'Peso' => str_replace(".", ",", round($shipping_weight / $mrw_packages, 2, PHP_ROUND_HALF_UP)));
                                                                                    }
                                                                                } else {
                                                                                    $mrw_apportion['BultoRequest'] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Ref 1 ', 'Peso' => str_replace(".", ",", round($shipping_weight, 2, PHP_ROUND_HALF_UP)));
                                                                                }
                                                                            } else {
                                                                                $mrw_apportion = '';
                                                                            }
                                                                            
                                                                            //Notifications
                                                                            if ($mrwsettings['mrwnotifications'] == 'sms' && !empty($billing_phone)) {
                                                                                $notifications_mrw = array('MailSMS' => $billing_phone);
                                                                            }
                                                                            
                                                                            // Create the SoapClient instance.
                                                                            $clientMRW = new SoapClient($wsdl_url, array('trace' => true));
                                                                            
                                                                            $cabeceras = array(
                                                                                'CodigoFranquicia' => $mrwsettings['mrwfranchise'],
                                                                                'CodigoAbonado' => $mrwsettings['mrwsubscriber'],
                                                                                'CodigoDepartamento' => $mrwsettings['mrwdepartment'], //Optional
                                                                                'UserName' => $mrwsettings['mrwuser'],
                                                                                'Password' => $mrwsettings['mrwpass'],
                                                                            );
                                                                            
                                                                            // Create the header.
                                                                            $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $cabeceras); // Headers over the SOAP client object
                                                                            $clientMRW->__setSoapHeaders($header);
                                                                            
                                                                            $shipping_country = $order->get_shipping_country();
                                                                            
                                                                            $today = date("d/m/Y", time());
                                                                            $parametros = array(
                                                                                'request' => array(
                                                                                    'DatosEntrega' => array(
                                                                                        ## DATOS DESTINATARIO ##
                                                                                        'Direccion' => array(
                                                                                            'Direccion' => $shipping_address
                                                                                            , 'CodigoPostal' => $shipping_postcode
                                                                                            , 'Poblacion' => $shipping_city //Obligatorio
                                                                                            , 'CodigoPais' => $shipping_country //Optional
                                                                                            , 'Estado'     => $shipping_state
                                                                                            )
                                                                                            , 'Nif' => ''
                                                                                            , 'Nombre' => $label_name
                                                                                            , 'Telefono' => empty($billing_phone) ? ' ' : $billing_phone //Optional
                                                                                            , 'ALaAtencionDe' => $label_to,
                                                                                            )
                                                                                            ## DATOS DEL SERVICIO ##
                                                                                            , 'DatosServicio' => array(
                                                                                                'Fecha' => $today //Today or after
                                                                                                , 'Referencia' => empty($mrw_reference) ? $order_id : $mrw_reference
                                                                                                , 'CodigoServicio' => $mrw_service
                                                                                                , 'DescripcionServicio' => '' //Optional
                                                                                                , 'Bultos' => $mrw_apportion //Optional
                                                                                                , 'NumeroBultos' => $mrw_packages
                                                                                                , 'Peso' => $shipping_weight
                                                                                                , 'Notificaciones' => $notifications_mrw,
                                                                                            ),
                                                                                        ),
                                                                                    );
                                                                                    
                                                                                    $responseCode = $clientMRW->TransmEnvioInternacional($parametros);
                                                                                    
                                                                                    //Save generate request
                                                                                    if ($mrwsettings['mrwerrorlog'] == 'yes') {
                                                                                        mrw_log("MRW Generate International Label Request", $clientMRW->__getLastRequest());
                                                                                        
                                                                                        if (!empty($responseCode->TransmEnvioInternacionalResult->Mensaje)) {
                                                                                            mrw_log("MRW Generate International Message Request", $responseCode->TransmEnvioInternacionalResult->Mensaje);
                                                                                        }
                                                                                    }
                                                                                    
                                                                                    //Save in database
                                                                                    $table_name = $wpdb->prefix . 'mrw_orders';
                                                                                    
                                                                                    if ($mrwsettings['mrwtype'] == 'live') {
                                                                                        $wsdl_url2 = $wsdl_pro;
                                                                                    } else if ($mrwsettings['mrwtype'] == 'development') {
                                                                                        $wsdl_url2 = $wsdl_test;
                                                                                    }
                                                                                    
                                                                                    if ($responseCode->TransmEnvioInternacionalResult->Estado == '1' && $responseCode->TransmEnvioInternacionalResult->NumeroEnvio) {
                                                                                        $mrw_tracking_number = $responseCode->TransmEnvioInternacionalResult->NumeroEnvio;
                                                                                        $num_sol = $responseCode->TransmEnvioInternacionalResult->NumeroSolicitud;
                                                                                        $url = $wsdl_url2 . "Panel.aspx?Franq=" . $mrwsettings['mrwfranchise'] . "&Ab=" . $mrwsettings['mrwsubscriber'] . "&Dep=" . $mrwsettings['mrwdepartment'] . "&Usr=" . $mrwsettings['mrwuser'] . "&Pwd=" . $mrwsettings['mrwpass'] . "&NumSol=" . $num_sol . "&NumEnv=" . $mrw_tracking_number;
                                                                                        
                                                                                        $wpdb->insert(
                                                                                            $table_name,
                                                                                            array(
                                                                                                'order_id' => $order_id,
                                                                                                'tracking_number' => $mrw_tracking_number,
                                                                                                'URL' => $url,
                                                                                                'options' => serialize($options),
                                                                                                'date' => date("Y-m-d H:i:s"),
                                                                                                )
                                                                                            );
                                                                                            
                                                                                            //Download mrw label
                                                                                            if ($mrwsettings['mrwtype'] == 'live') {
                                                                                                $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';
                                                                                            } else if ($mrwsettings['mrwtype'] == 'development') {
                                                                                                $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
                                                                                            }
                                                                                            
                                                                                            //SOAP Request
                                                                                            $clientMRW = new SoapClient($wsdl_url, array('trace' => true));
                                                                                            
                                                                                            $headers = array(
                                                                                                'CodigoFranquicia' => $mrwsettings['mrwfranchise'],
                                                                                                'CodigoAbonado' => $mrwsettings['mrwsubscriber'],
                                                                                                'CodigoDepartamento' => $mrwsettings['mrwdepartment'],
                                                                                                'UserName' => $mrwsettings['mrwuser'],
                                                                                                'Password' => $mrwsettings['mrwpass']);
                                                                                                
                                                                                                $params = array(
                                                                                                    'request' => array(
                                                                                                        'NumeroEnvio' => $mrw_tracking_number,
                                                                                                        'SeparadorNumerosEnvio' => ';',
                                                                                                        'FechaInicioEnvio' => '',
                                                                                                        'FechaFinEnvio' => '',
                                                                                                        'TipoEtiquetaEnvio' => '0',
                                                                                                        'ReportTopMargin' => '20',
                                                                                                        'ReportLeftMargin' => '20',
                                                                                                    ),
                                                                                                );
                                                                                                
                                                                                                $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $headers);
                                                                                                
                                                                                                $clientMRW->__setSoapHeaders($header);
                                                                                                
                                                                                                $responseCode = $clientMRW->EtiquetaEnvioInternacional($params);
                                                                                                
                                                                                                if ($mrwsettings['mrwerrorlog'] == 'yes') {
                                                                                                    mrw_log("MRW Download International Label Request", $clientMRW->__getLastRequest());
                                                                                                    
                                                                                                    if (!empty($responseCode->GetEtiquetaEnvioInternacionalResult->Mensaje)) {
                                                                                                        mrw_log("MRW Download International Message Request", $responseCode->GetEtiquetaEnvioInternacionalResult->Mensaje);
                                                                                                    }
                                                                                                    
                                                                                                }
                                                                                                
                                                                                                //Get label code
                                                                                                $pdf_code = $responseCode->GetEtiquetaEnvioInternacionalResult->EtiquetaFile;
                                                                                                
                                                                                                //Path for downloading
                                                                                                $urlUpload = wp_upload_dir();
                                                                                                $MRWFolder = $urlUpload['basedir'] . '/MRW/';
                                                                                                $urlLabel = $MRWFolder . $mrw_tracking_number . '.pdf';
                                                                                                
                                                                                                $pdf = fopen($urlLabel, 'w');
                                                                                                fputs($pdf, $pdf_code);
                                                                                                fclose($pdf);
                                                                                                
                                                                                                $json_arr = array(
                                                                                                    "mrw_tracking_number" => $mrw_tracking_number,
                                                                                                    "url_label" => $urlLabel,
                                                                                                    "message" => __('There is no tracking information yet', 'woocommerce-mrw-carrier'),
                                                                                                    "success" => __('The shipping label was generated successfully!', 'woocommerce-mrw-carrier'),
                                                                                                    "service" => get_service_name($mrw_service),
                                                                                                    "npack" => $mrw_packages);
                                                                                                    
                                                                                                    $order->update_status($mrwsettings['mrwstatus'], 'order_note');
                                                                                                    
                                                                                                    //Return JSON
                                                                                                    echo json_encode($json_arr);
                                                                                                }
                                                                                                //If the generation fails, show the error.
                                                                                                else {
                                                                                                    $json_arr = array("nosuccess" => __($responseCode->TransmEnvioInternacionalResult->Mensaje, 'woocommerce-mrw-carrier'), "state" => 0);
                                                                                                    echo json_encode($json_arr);
                                                                                                }
                                                                                                
                                                                                                wp_die();
                                                                                            }
                                                                                            
                                                                                            //Function to print the order label
                                                                                            //add_action( 'wp_ajax_download_mrw_label', 'download_mrw_label');
                                                                                            function download_mrw_label($mrw_tracking_number)
                                                                                            {
                                                                                                $urlLabel = '';
                                                                                                
                                                                                                $urlUpload = wp_upload_dir();
                                                                                                $MRWFolder = $urlUpload['baseurl'] . '/MRW/';
                                                                                                $urlLabel = $MRWFolder . $mrw_tracking_number . '.pdf';
                                                                                                echo $urlLabel;
                                                                                            }
                                                                                            
                                                                                            /*Function to show the order tracking for national shippings*/
                                                                                            add_filter('add_mrw_tracking_info', 'get_mrw_tracking_info');
                                                                                            function get_mrw_tracking_info($tracking_number)
                                                                                            {
                                                                                                $order_status = '';
                                                                                                $wsdl_url = 'http://seguimiento.mrw.es/swc/wssgmntnvs.asmx?WSDL';
                                                                                                $tracking_msg = '';
                                                                                                
                                                                                                $clientMRW = new SoapClient($wsdl_url, array('trace' => true));
                                                                                                global $wpdb;
                                                                                                $options_table = $wpdb->prefix . "options";
                                                                                                $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                                                $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                                                $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                                                
                                                                                                $params = array(
                                                                                                    'Franquicia' => $mrwsettings['mrwfranchise'],
                                                                                                    'Cliente' => $mrwsettings['mrwsubscriber'],
                                                                                                    'Password' => $mrwsettings['mrwpasstrack'],
                                                                                                    'NumeroMRW' => $tracking_number,
                                                                                                    'Referencia' => '',
                                                                                                    'Agrupado' => '',
                                                                                                );
                                                                                                
                                                                                                $responseCode = $clientMRW->SeguimientoNumeroEnvioMRWNacional($params);
                                                                                                
                                                                                                //Controlls if there is any information about the tracking and shows it, in case not shows: there is no information about the tracking
                                                                                                if ($responseCode->SeguimientoNumeroEnvioMRWNacionalResult->Estado == 'true') {
                                                                                                    
                                                                                                    $order_status = $responseCode->SeguimientoNumeroEnvioMRWNacionalResult->Envio->EstadoDescripcion;
                                                                                                    $tracking_msg = $order_status;
                                                                                                } else {
                                                                                                    $tracking_msg = __('There is no tracking information yet', 'woocommerce-mrw-carrier');
                                                                                                }
                                                                                                
                                                                                                echo $tracking_msg;
                                                                                            }
                                                                                            
                                                                                            /*Function to show the order tracking for national shippings*/
                                                                                            add_filter('add_mrw_tracking_info_int', 'get_mrw_tracking_info_int');
                                                                                            function get_mrw_tracking_info_int($tracking_number)
                                                                                            {
                                                                                                $order_status = '';
                                                                                                $wsdl_url = 'http://seguimiento.mrw.es/swc/wssgmntnvs.asmx?WSDL';
                                                                                                $tracking_msg = '';
                                                                                                
                                                                                                $clientMRW = new SoapClient($wsdl_url, array('trace' => true));
                                                                                                global $wpdb;
                                                                                                $options_table = $wpdb->prefix . "options";
                                                                                                $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                                                $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                                                $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                                                
                                                                                                $params = array(
                                                                                                    'Franquicia' => $mrwsettings['mrwfranchise'],
                                                                                                    'Cliente' => $mrwsettings['mrwsubscriber'],
                                                                                                    'Password' => $mrwsettings['mrwpasstrack'],
                                                                                                    'NumeroMRW' => $tracking_number,
                                                                                                    'Referencia' => '',
                                                                                                    'Agrupado' => '',
                                                                                                );
                                                                                                
                                                                                                $responseCode = $clientMRW->SeguimientoNumeroEnvioMRWInternacional($params);
                                                                                                
                                                                                                //Controlls if there is any information about the tracking and shows it, in case not shows: there is no information about the tracking
                                                                                                if ($responseCode->SeguimientoNumeroEnvioMRWInternacionalResult->Estado == 'true') {
                                                                                                    
                                                                                                    $order_status = $responseCode->SeguimientoNumeroEnvioMRWInternacionalResult->Envio->EstadoDescripcion;
                                                                                                    $tracking_msg = $order_status;
                                                                                                } else {
                                                                                                    $tracking_msg = __('There is no tracking information yet', 'woocommerce-mrw-carrier');
                                                                                                }
                                                                                                
                                                                                                echo $tracking_msg;
                                                                                            }
                                                                                            
                                                                                            /*Function to get the tracking number from the database*/
                                                                                            function get_mrw_tracking_number()
                                                                                            {
                                                                                                global $wpdb, $woocommerce, $post;
                                                                                                $order_id = $post->ID;
                                                                                                if (isset($order_id)) {
                                                                                                    $order = new WC_Order($order_id);
                                                                                                }
                                                                                                
                                                                                                $order_array = null;
                                                                                                $tracking_number = null;
                                                                                                
                                                                                                //Variables to get id and tracking from multidimensional array
                                                                                                $oid = 'order_id';
                                                                                                $tn = 'tracking_number';
                                                                                                $turl = 'URL';
                                                                                                $orders_table = $wpdb->prefix . "mrw_orders";
                                                                                                $query = $wpdb->prepare("SELECT * FROM $orders_table WHERE order_id = %s", $order->get_id());
                                                                                                
                                                                                                $order_array = $wpdb->get_results($query, ARRAY_A);
                                                                                                
                                                                                                if (!empty($order_array)) {
                                                                                                    $tracking_number = $order_array[0][$tn];
                                                                                                } else {
                                                                                                    $tracking_number = null;
                                                                                                }
                                                                                                
                                                                                                return $tracking_number;
                                                                                            }
                                                                                            
                                                                                            function get_mrw_tracking_number3()
                                                                                            {
                                                                                                global $wpdb, $woocommerce, $post;
                                                                                                
                                                                                                $order_id = $post->ID;
                                                                                                if (isset($order_id)) {
                                                                                                    $order = new WC_Order($order_id);
                                                                                                }
                                                                                                
                                                                                                $order_array = null;
                                                                                                $tracking_number = null;
                                                                                                
                                                                                                //Variables to get id and tracking from multidimensional array
                                                                                                $oid = 'order_id';
                                                                                                $tn = 'tracking_number';
                                                                                                $turl = 'URL';
                                                                                                $orders_table = $wpdb->prefix . "mrw_orders";
                                                                                                $query = $wpdb->prepare("SELECT * FROM $orders_table WHERE order_id = %s", $order->get_id());
                                                                                                
                                                                                                $order_array = $wpdb->get_results($query, ARRAY_A);
                                                                                                
                                                                                                if (!empty($order_array)) {
                                                                                                    $tracking_number = $order_array[0][$tn];
                                                                                                } else {
                                                                                                    $tracking_number = null;
                                                                                                }
                                                                                                
                                                                                                return $tracking_number;
                                                                                            }
                                                                                            
                                                                                            //Get order weight
                                                                                            function get_mrw_weight($products)
                                                                                            {
                                                                                                $weight = null;
                                                                                                
                                                                                                foreach ($products as $product) {
                                                                                                    
                                                                                                    $product_mrw = new WC_Product($product['product_id']);
                                                                                                    
                                                                                                    if ($product['variation_id'] != 0) {
                                                                                                        
                                                                                                        $variation = new WC_Product($product['variation_id']);
                                                                                                        //echo $variation->price;
                                                                                                        $weight += $product['qty'] * $variation->weight;
                                                                                                    } else {
                                                                                                        $weight += $product['qty'] * $product_mrw->weight;
                                                                                                    }
                                                                                                    
                                                                                                }
                                                                                                
                                                                                                $weight_unit = get_option('woocommerce_weight_unit');
                                                                                                
                                                                                                $normalized_weight = wc_get_weight($weight, 'kg', $weight_unit);
                                                                                                
                                                                                                return $normalized_weight;
                                                                                            }
                                                                                            
                                                                                            function get_mrw_weight3($products)
                                                                                            {
                                                                                                $weight = null;
                                                                                                
                                                                                                foreach ($products as $product) {
                                                                                                    
                                                                                                    if ($product['variation_id'] != 0) {
                                                                                                        
                                                                                                        $variation = new WC_Product_Variation($product['variation_id']);
                                                                                                        
                                                                                                        $weight += $product['qty'] * $variation->get_weight();
                                                                                                    } else {
                                                                                                        
                                                                                                        $product_mrw = new WC_Product($product['product_id']);
                                                                                                        
                                                                                                        $weight += $product['qty'] * $product_mrw->get_weight();
                                                                                                    }
                                                                                                }
                                                                                                
                                                                                                $weight_unit = get_option('woocommerce_weight_unit');
                                                                                                
                                                                                                $normalized_weight = wc_get_weight($weight, 'kg', $weight_unit);
                                                                                                
                                                                                                return $normalized_weight;
                                                                                            }
                                                                                            
                                                                                            //Save taxes
                                                                                            add_action('wp_ajax_save_mrw_taxes', 'save_mrw_taxes');
                                                                                            function save_mrw_taxes()
                                                                                            {
                                                                                                global $wpdb;
                                                                                                
                                                                                                $table_cities = $wpdb->prefix . 'mrw_cities';
                                                                                                $table_taxes = $wpdb->prefix . 'mrw_taxes';
                                                                                                
                                                                                                $cities_available = $_POST['cities_available'];
                                                                                                $mrw_taxes = $_POST['taxes'];
                                                                                                
                                                                                                $mrw_taxes = urldecode(stripslashes($_POST['taxes']));
                                                                                                $mrw_taxes = json_decode($mrw_taxes);
                                                                                                
                                                                                                foreach ($cities_available as $city) {
                                                                                                    
                                                                                                    $available = $city['available'];
                                                                                                    $city_id = $city['city_id'];
                                                                                                    $country_id = $city['country_id'];
                                                                                                    
                                                                                                    $result = $wpdb->query($wpdb->prepare("UPDATE $table_cities SET `available`= %d WHERE `city_id` = %s AND `country_id` = %s", $available, $city_id, $country_id));
                                                                                                    
                                                                                                }
                                                                                                
                                                                                                foreach ($mrw_taxes as $tax) {
                                                                                                    
                                                                                                    $wpdb->replace(
                                                                                                        $table_taxes,
                                                                                                        array(
                                                                                                            'country_id' => $tax->country_id,
                                                                                                            'city_id' => $tax->city_id,
                                                                                                            'range_id' => $tax->range_id,
                                                                                                            'price' => $tax->price,
                                                                                                            )
                                                                                                        );
                                                                                                    }
                                                                                                    
                                                                                                    $messages = array("success" => __('Taxes saved', 'woocommerce-mrw-carrier'), "fail" => __('There has been a problem saving taxes', 'woocommerce-mrw-carrier'));
                                                                                                    
                                                                                                    //Return JSON
                                                                                                    echo json_encode($messages);
                                                                                                    
                                                                                                    wp_die();
                                                                                                }
                                                                                                
                                                                                                //Save ranges
                                                                                                add_action('wp_ajax_save_ranges', 'save_ranges');
                                                                                                function save_ranges()
                                                                                                {
                                                                                                    global $wpdb;
                                                                                                    
                                                                                                    $table_ranges = $wpdb->prefix . 'mrw_ranges';
                                                                                                    
                                                                                                    $ranges_min = $_POST['array_ranges_inf'];
                                                                                                    $ranges_max = $_POST['array_ranges_sup'];
                                                                                                    
                                                                                                    //Create ranges array with id max and min
                                                                                                    foreach ($ranges_min as $range) {
                                                                                                        foreach ($ranges_max as $range2) {
                                                                                                            if ($range2['range_id'] == $range['range_id']) {
                                                                                                                $ranges[] = array(
                                                                                                                    'range_id' => $range['range_id'],
                                                                                                                    'min' => $range['min'],
                                                                                                                    'max' => $range2['max'],
                                                                                                                );
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    //Check ranges conditions
                                                                                                    if (check_ranges($ranges) == true) {
                                                                                                        foreach ($ranges as $range) {
                                                                                                            
                                                                                                            $wpdb->replace(
                                                                                                                $table_ranges,
                                                                                                                array(
                                                                                                                    'range_id' => $range['range_id'],
                                                                                                                    'min' => $range['min'],
                                                                                                                    'max' => $range['max'],
                                                                                                                    )
                                                                                                                );
                                                                                                            }
                                                                                                            
                                                                                                            $messages = array("message" => __('Ranges saved', 'woocommerce-mrw-carrier'), "check" => true);
                                                                                                        } else {
                                                                                                            $messages = array("message" => __('There is an error in ranges configuration', 'woocommerce-mrw-carrier'), "check" => false);
                                                                                                        }
                                                                                                        
                                                                                                        //Return JSON
                                                                                                        echo json_encode($messages);
                                                                                                        
                                                                                                        wp_die();
                                                                                                    }
                                                                                                    
                                                                                                    //Delete range
                                                                                                    add_action('wp_ajax_delete_ranges', 'delete_ranges');
                                                                                                    function delete_ranges()
                                                                                                    {
                                                                                                        global $wpdb;
                                                                                                        
                                                                                                        $range_id = $_POST['del_range'];
                                                                                                        $table_ranges = $wpdb->prefix . 'mrw_ranges';
                                                                                                        $table_taxes = $wpdb->prefix . 'mrw_taxes';
                                                                                                        $messages = null;
                                                                                                        
                                                                                                        $check_r = $wpdb->query($wpdb->prepare("DELETE FROM $table_ranges WHERE range_id = %d", $range_id));
                                                                                                        
                                                                                                        $check_t = $wpdb->query($wpdb->prepare("DELETE FROM $table_taxes WHERE range_id = %d", $range_id));
                                                                                                        
                                                                                                        if ($check_r and $check_t) {
                                                                                                            $messages = array("message" => __('Range deleted', 'woocommerce-mrw-carrier'), "check" => false, "new_range" => $range_id);
                                                                                                        } else {
                                                                                                            $messages = array("message" => __('There is an error deleting ranges', 'woocommerce-mrw-carrier'), "check" => false);
                                                                                                        }
                                                                                                        
                                                                                                        //Return JSON
                                                                                                        echo json_encode($messages);
                                                                                                        
                                                                                                        wp_die();
                                                                                                    }
                                                                                                    
                                                                                                    //Check if the ranges overlaps to each other
                                                                                                    add_action('wp_ajax_check_ranges', 'check_ranges');
                                                                                                    function check_ranges($ranges)
                                                                                                    {
                                                                                                        $check = true;
                                                                                                        
                                                                                                        foreach ($ranges as $range) {
                                                                                                            foreach ($ranges as $range2) {
                                                                                                                
                                                                                                                //If are the same range
                                                                                                                if ($range['range_id'] == $range2['range_id']) {
                                                                                                                    if (($range['min'] > $range['max']) or ($range2['min'] > $range2['max'])) {
                                                                                                                        
                                                                                                                        $check = false;
                                                                                                                    }
                                                                                                                }
                                                                                                                
                                                                                                                //If different ranges
                                                                                                                if ($range['range_id'] != $range2['range_id']) {
                                                                                                                    
                                                                                                                    //If sup range > inf range
                                                                                                                    if ($range['min'] > $range['max']) {
                                                                                                                        
                                                                                                                        $check = false;
                                                                                                                    }
                                                                                                                    
                                                                                                                    //If don't overlaps each other
                                                                                                                    if (($range['min'] <= $range2['min']) and ($range['max'] > $range2['min'])) {
                                                                                                                        $check = false;
                                                                                                                    }
                                                                                                                    
                                                                                                                    if (($range2['min'] <= $range['min']) and ($range2['max'] > $range['min'])) {
                                                                                                                        $check = false;
                                                                                                                    }
                                                                                                                    
                                                                                                                    //Don't allow duplicated ranges
                                                                                                                    if (($range['min'] == $range2['min']) and ($range['max'] == $range2['max'])) {
                                                                                                                        $check = false;
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        
                                                                                                        return $check;
                                                                                                        
                                                                                                        wp_die();
                                                                                                    }
                                                                                                    
                                                                                                    function get_service_name($service_code)
                                                                                                    {
                                                                                                        switch ($service_code) {
                                                                                                            
                                                                                                            case '0000':
                                                                                                            return __('Urgente 10', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0005':
                                                                                                            return __('Urgente Hoy', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0010':
                                                                                                            return __('Promociones', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0100':
                                                                                                            return __('Urgente 12', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0110':
                                                                                                            return __('Urgente 14', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0120':
                                                                                                            return __('Urgente 22', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0200':
                                                                                                            return __('Urgente 19', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0205':
                                                                                                            return __('Urgente 19 Expedición', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0115':
                                                                                                            return __('Urgente 14 Expedición', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0105':
                                                                                                            return __('Urgente 12 Expedición', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0015':
                                                                                                            return __('Urgente 10 Expedición', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0210':
                                                                                                            return __('Urgente 19 Mas 40 Kilos', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0220':
                                                                                                            return __('Urgente 19 Portugal', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0230':
                                                                                                            return __('Bag 19', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0235':
                                                                                                            return __('Bag 14', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0300':
                                                                                                            return __('Económico', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0310':
                                                                                                            return __('Económico Mas 40 Kilos', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0350':
                                                                                                            return __('Económico Interinsular', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0370':
                                                                                                            return __('Marítimo Baleares', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0385':
                                                                                                            return __('Marítimo Canarias', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0390':
                                                                                                            return __('Marítimo Interinsular', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0400':
                                                                                                            return __('Express Documentos', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0450':
                                                                                                            return __('Express 2 Kilos', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0480':
                                                                                                            return __('Caja Express 3 Kilos', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0490':
                                                                                                            return __('Documentos 14', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0800':
                                                                                                            return __('Ecommerce', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case '0810':
                                                                                                            return __('Ecommerce Canje', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case 'BOX25':
                                                                                                            return __('Ecobox25', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case 'DOC':
                                                                                                            return __('Documentos', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case 'ECOMM':
                                                                                                            return __('Documentos', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case 'ECOP':
                                                                                                            return __('Economy', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case 'EURO2':
                                                                                                            return __('Euro 2kg', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case 'PAC':
                                                                                                            return __('Paquetes', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                            case 'SC':
                                                                                                            return __('Supercity', 'woocommerce-mrw-carrier');
                                                                                                            break;
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    //Get order shipment data to show it in order page
                                                                                                    function get_mrw_order_data()
                                                                                                    {
                                                                                                        global $wpdb, $woocommerce, $post;
                                                                                                        $order_id = $post->ID;
                                                                                                        if (isset($order_id)) {
                                                                                                            $order = new WC_Order($order_id);
                                                                                                        }
                                                                                                        
                                                                                                        $options_array = null;
                                                                                                        $orders_table = $wpdb->prefix . "mrw_orders";
                                                                                                        $query = $wpdb->prepare("SELECT options FROM $orders_table WHERE order_id = %s", $order->get_id());
                                                                                                        $options_array = $wpdb->get_results($query, ARRAY_A);
                                                                                                        
                                                                                                        $options_array = unserialize($options_array[0]['options']);
                                                                                                        
                                                                                                        return $options_array;
                                                                                                        
                                                                                                    }
                                                                                                    
                                                                                                    function get_mrw_order_data3()
                                                                                                    {
                                                                                                        global $wpdb, $woocommerce, $post;
                                                                                                        $order_id = $post->ID;
                                                                                                        if (isset($order_id)) {
                                                                                                            $order = new WC_Order($order_id);
                                                                                                        }
                                                                                                        
                                                                                                        $options_array = null;
                                                                                                        $orders_table = $wpdb->prefix . "mrw_orders";
                                                                                                        $query = $wpdb->prepare("SELECT options FROM $orders_table WHERE order_id = %s", $order->get_id());
                                                                                                        $options_array = $wpdb->get_results($query, ARRAY_A);
                                                                                                        
                                                                                                        $options_array = unserialize($options_array[0]['options']);
                                                                                                        
                                                                                                        return $options_array;
                                                                                                        
                                                                                                    }
                                                                                                    
                                                                                                    function mrw_get_sn($option)
                                                                                                    {
                                                                                                        if ($option == 'S' || $option == 'E') {
                                                                                                            return 'Yes';
                                                                                                        } else {
                                                                                                            return 'No';
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    function mrw_get_ts($option)
                                                                                                    {
                                                                                                        switch ($option) {
                                                                                                            case '1':
                                                                                                            return 'Entrega (08:00-14:00)';
                                                                                                            break;
                                                                                                            
                                                                                                            case '2':
                                                                                                            return 'Entrega (16:00-19:00)';
                                                                                                            break;
                                                                                                            
                                                                                                            case '3':
                                                                                                            return 'Entrega (20:00-22:00)';
                                                                                                            break;
                                                                                                            
                                                                                                            default:
                                                                                                            return 'No aplicable';
                                                                                                            break;
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    function get_mrw_default_service()
                                                                                                    {
                                                                                                        global $wpdb;
                                                                                                        $options_table = $wpdb->prefix . "options";
                                                                                                        $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                                                        $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                                                        $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                                                        
                                                                                                        $mrw_default_service = $mrwsettings['mrwdefaultservice'];
                                                                                                        
                                                                                                        return $mrw_default_service;
                                                                                                    }
                                                                                                    
                                                                                                    function get_mrw_default_service_int()
                                                                                                    {
                                                                                                        global $wpdb;
                                                                                                        $options_table = $wpdb->prefix . "options";
                                                                                                        $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                                                        $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                                                        $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                                                        
                                                                                                        $mrw_default_service_int = $mrwsettings['mrwdefaultserviceint'];
                                                                                                        
                                                                                                        return $mrw_default_service_int;
                                                                                                    }
                                                                                                    
                                                                                                    function get_mrw_marketplaces_flag()
                                                                                                    {
                                                                                                        global $wpdb;
                                                                                                        $options_table = $wpdb->prefix . "options";
                                                                                                        $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                                                        $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                                                        $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                                                        
                                                                                                        $mrw_marketplaces_flag = $mrwsettings['mrwmpflag'];
                                                                                                        
                                                                                                        return $mrw_marketplaces_flag;
                                                                                                    }

                                                                                                    function get_mrw_check_comments()
                                                                                                    {
                                                                                                        global $wpdb;
                                                                                                        $options_table = $wpdb->prefix . "options";
                                                                                                        $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                                                        $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                                                        $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                                                        
                                                                                                        $mrw_comments_flag = $mrwsettings['mrwcheckcomments'];
                                                                                                        
                                                                                                        return $mrw_comments_flag;
                                                                                                    }
                                                                                                    
                                                                                                    function get_mrw_available_services()
                                                                                                    {
                                                                                                        $mrw_services = array(
                                                                                                            '0000' => __('Urgente 10', 'woocommerce-mrw-carrier'),
                                                                                                            '0005' => __('Urgente Hoy', 'woocommerce-mrw-carrier'),
                                                                                                            '0010' => __('Promociones', 'woocommerce-mrw-carrier'),
                                                                                                            '0100' => __('Urgente 12', 'woocommerce-mrw-carrier'),
                                                                                                            '0110' => __('Urgente 14', 'woocommerce-mrw-carrier'),
                                                                                                            '0120' => __('Urgente 22', 'woocommerce-mrw-carrier'),
                                                                                                            '0200' => __('Urgente 19', 'woocommerce-mrw-carrier'),
                                                                                                            '0205' => __('Urgente 19 Expedición', 'woocommerce-mrw-carrier'),
                                                                                                            '0115' => __('Urgente 14 Expedición', 'woocommerce-mrw-carrier'),
                                                                                                            '0105' => __('Urgente 12 Expedición', 'woocommerce-mrw-carrier'),
                                                                                                            '0015' => __('Urgente 10 Expedición', 'woocommerce-mrw-carrier'),
                                                                                                            '0210' => __('Urgente 19 Mas 40 Kilos', 'woocommerce-mrw-carrier'),
                                                                                                            '0220' => __('Urgente 19 Portugal', 'woocommerce-mrw-carrier'),
                                                                                                            '0230' => __('Bag 19', 'woocommerce-mrw-carrier'),
                                                                                                            '0235' => __('Bag 14', 'woocommerce-mrw-carrier'),
                                                                                                            '0300' => __('Económico', 'woocommerce-mrw-carrier'),
                                                                                                            '0310' => __('Económico Mas 40 Kilos', 'woocommerce-mrw-carrier'),
                                                                                                            '0350' => __('Económico Interinsular', 'woocommerce-mrw-carrier'),
                                                                                                            '0370' => __('Marítimo Baleares', 'woocommerce-mrw-carrier'),
                                                                                                            '0385' => __('Marítimo Canarias', 'woocommerce-mrw-carrier'),
                                                                                                            '0390' => __('Marítimo Interinsular', 'woocommerce-mrw-carrier'),
                                                                                                            '0400' => __('Express Documentos', 'woocommerce-mrw-carrier'),
                                                                                                            '0450' => __('Express 2 Kilos', 'woocommerce-mrw-carrier'),
                                                                                                            '0480' => __('Caja Express 3 Kilos', 'woocommerce-mrw-carrier'),
                                                                                                            '0490' => __('Documentos 14', 'woocommerce-mrw-carrier'),
                                                                                                            '0800' => __('Ecommerce', 'woocommerce-mrw-carrier'),
                                                                                                            '0810' => __('Ecommerce Canje', 'woocommerce-mrw-carrier'),
                                                                                                        );
                                                                                                        
                                                                                                        return $mrw_services;
                                                                                                    }
                                                                                                    
                                                                                                    function get_mrw_available_services_int()
                                                                                                    {
                                                                                                        $mrw_services_int = array(
                                                                                                            'BOX25' => __('Ecobox25', 'woocommerce-mrw-carrier'),
                                                                                                            'DOC' => __('Documentos', 'woocommerce-mrw-carrier'),
                                                                                                            'ECOMM' => __('Ecommerce', 'woocommerce-mrw-carrier'),
                                                                                                            'ECOP' => __('Economy', 'woocommerce-mrw-carrier'),
                                                                                                            'EURO2' => __('Euro 2kg', 'woocommerce-mrw-carrier'),
                                                                                                            'PAC' => __('Paquetes', 'woocommerce-mrw-carrier'),
                                                                                                            'SC' => __('Supercity', 'woocommerce-mrw-carrier'),
                                                                                                        );
                                                                                                        
                                                                                                        return $mrw_services_int;
                                                                                                    }
                                                                                                    
                                                                                                    function mrw_log($texto1, $texto2)
                                                                                                    {
                                                                                                        $mrw_log_path = plugin_dir_path(__FILE__) . 'mrw_log.txt';
                                                                                                        $mrw_log = fopen($mrw_log_path, 'a');
                                                                                                        fwrite($mrw_log, "[" . date('j/m/Y h:i:s') . "] $texto1:  $texto2 \r\n");
                                                                                                        fclose($mrw_log);
                                                                                                    }
                                                                                                    
                                                                                                    //Check free shipping
                                                                                                    function check_free_shipping($shipping_methods)
                                                                                                    {
                                                                                                        foreach ($shipping_methods as $shipping) {
                                                                                                            if (preg_match("/free_shipping/", $shipping['method_id'])) {
                                                                                                                return true;
                                                                                                            } else {
                                                                                                                return false;
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    //Check if the order label has been generated
                                                                                                    function is_mrw_generated($shop_order_post_id)
                                                                                                    {
                                                                                                        global $wpdb;
                                                                                                        
                                                                                                        $query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "mrw_orders WHERE order_id = %d", $shop_order_post_id);
                                                                                                        
                                                                                                        $results = $wpdb->get_var($query);
                                                                                                        
                                                                                                        $order = new WC_Order($shop_order_post_id);
                                                                                                        
                                                                                                        $options_table = $wpdb->prefix . "options";
                                                                                                        
                                                                                                        //Get woocommerce_mrw_carrier settings.
                                                                                                        $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
                                                                                                        $mrwsettings = get_object_vars($mrwsettings[0]);
                                                                                                        $mrwsettings = unserialize($mrwsettings['option_value']);
                                                                                                        
                                                                                                        mrw_log("Module path", plugin_dir_path(__FILE__));
                                                                                                        if ($results > 0) {
                                                                                                            return true;
                                                                                                        } else {
                                                                                                            return false;
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    function is_national_shipping($order_id)
                                                                                                    {
                                                                                                        if (isset($order_id)) {
                                                                                                            $order = new WC_Order($order_id);
                                                                                                        }
                                                                                                        
                                                                                                        $country = $order->get_shipping_country();
                                                                                                        
                                                                                                        if ('ES' == $country || 'AD' == $country || 'GB' == $country || 'PT' == $country) {
                                                                                                            return true;
                                                                                                        } else {
                                                                                                            return false;
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    function soap_active(){
                                                                                                        if (class_exists("SOAPClient")) {
                                                                                                            return true;
                                                                                                        } else return false;
                                                                                                    }
                                                                                                }