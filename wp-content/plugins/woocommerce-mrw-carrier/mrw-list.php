<?php

if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MRW_List_Table extends WP_List_Table {

    public $found_data = array();
 
   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
 
    function __construct() {
 
        parent::__construct( array(
            'singular'  => __( 'pedido', 'woocommerce-mrw-carrier' ),        //singular name of the listed records
            'plural'    => __( 'pedidos', 'woocommerce-mrw-carrier' ),       //plural name of the listed records
            'ajax'      => true 
        ));
    }        

    public function no_items() {
        _e( 'Actualmente no existen pedidos de MRW.', 'woocommerce-mr' );
    }

    protected function column_default( $item, $column_name ) {
        switch( $column_name ) { 
            case 'order_id':
            case 'tracking_number':
            case 'post_date':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }

    protected function get_bulk_actions() {
      $actions = array(
        'mrw_generate_bulk_action'    => __( 'Generar etiquetas MRW', 'woocommerce-mrw-carrier' ),
        'mrw_print_bulk_action'               => __( 'Descargar etiquetas MRW', 'woocommerce-mrw-carrier' ),
      );
      return $actions;
    }

    private function process_bulk_action() {

        // Detect when a bulk action is being triggered.
        
        if ( 'mrw_generate_bulk_action' === $this->current_action() ) {

            if (!isset($_POST['order'])){
                echo "Debes seleccionar al menos un pedido!";
            }

            else{
                foreach($_POST['order'] as $id) {
                            
                    if (!is_mrw_generated($id)){
                        if (is_national_shipping($id)) {
                            bulk_generate_mrw($id);
                        } else bulk_generate_mrw_int($id);
                    }
                }
                return;
            }

        }elseif ('mrw_print_bulk_action' === $this->current_action()){

            if (!isset($_POST['order'])){
                echo "Debes seleccionar al menos un pedido!";
            }

            else{

                $tracking_numbers = array();
                $tracking_numbers_int = array();

                foreach ( $_POST['order'] as $id ) {
                    if (!is_mrw_generated($id)){
                        if (is_national_shipping($id)) {
                            bulk_generate_mrw($id);
                        } else bulk_generate_mrw_int($id);
                    }
                }

                foreach ( $_POST['order'] as $id ) {
                    if (is_mrw_generated($id)){
                        if (is_national_shipping($id)) {
                            $tracking_numbers[] = get_mrw_tracking_number_bulk($id);
                        } else $tracking_numbers_int[] = get_mrw_tracking_number_bulk($id);
                       
                    }
                }

                $labelPath = bulk_print_mrw($tracking_numbers);

                $urlUpload = wp_upload_dir();
                $MRWFolder = $urlUpload['baseurl'] . '/MRW/';
                $download = $MRWFolder . 'download.php?f=' . $labelPath;    
                
                $labelPathInt = bulk_print_mrw_int($tracking_numbers_int);
                
                
                $urlUpload = wp_upload_dir();
                $MRWFolder = $urlUpload['baseurl'] . '/MRW/';
                $downloadInt = $MRWFolder . 'download.php?f=' . $labelPathInt;
                
                if (!empty($tracking_numbers)){
                    $downloadWindow = '<script type="text/javascript" language="javascript">window.open(" ' . $download . '");</script>';
                    echo str_replace("\\", "\\\\", $downloadWindow);
                } 
                if (!empty($tracking_numbers_int)) {
                    $downloadWindowInt = '<script type="text/javascript" language="javascript">window.open(" ' . $downloadInt . '");</script>';
                    echo str_replace("\\", "\\\\", $downloadWindowInt);
                }
            }
        }
        return;
    }

    protected function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="order[]" value="%s" />', $item['order_id']
        );    
    }

    protected function column_vp($item) {
        return sprintf(
            '<a href="post.php?post=%s&action=edit">Ver pedido</a>', $item['order_id']
        );    
    }

    protected function column_dl($item) {

        $urlUpload = wp_upload_dir();
        $MRWFolderDir = $urlUpload['basedir'] . '/MRW/';
        $MRWFolderUrl = $urlUpload['baseurl'] . '/MRW/';

        $file = $MRWFolderDir . $item['tracking_number'] . ".pdf";
        
        if (file_exists($file)){
            return '<a href="' . $MRWFolderUrl . 'download.php?f=' . $item['tracking_number'] . '.pdf">' . $item['tracking_number'] . ' </a>';
        }else return '<p>' . $item['tracking_number'] . '</p>';
    }

    protected function get_sortable_columns() {
      $sortable_columns = array(
        'order_id'  => array('order_id',true),
        'post_date' => array('post_date',true),
        'tracking_number'   => array('tracking_number',false)
      );
      return $sortable_columns;
    }

    public function get_columns(){
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'order_id'          => __( 'ID pedido', 'woocommerce-mrw-carrier' ),
            'post_date'         => __( 'Fecha', 'woocommerce-mrw-carrier' ),
            'dl'                => __( 'Número de envío', 'woocommerce-mrw-carrier' ),
            'vp'                => __( '', 'woocommerce-mrw-carrier' )
        );
        
        return $columns;
    }

    public function prepare_items() {
      $columns  = $this->get_columns();
      $hidden   = array();
      $sortable = $this->get_sortable_columns();
      $this->_column_headers = array( $columns, $hidden, $sortable );
      $this->process_bulk_action();
      $data = table_data();

      //usort( $data, array( &$this, 'usort_reorder' ) );
      usort( $data, 'usort_reorder' );
      
      $per_page = 30;
      $current_page = $this->get_pagenum();
      $total_items = count( $data );

      $this->found_data = array_slice( $data,( ( $current_page-1 )* $per_page ), $per_page );
      $this->set_pagination_args( array(
        'total_items' => $total_items,                  //WE have to calculate the total number of items
        'per_page'    => $per_page                     //WE have to determine how many items to show on a page
      ) );
      $this->items = $this->found_data;
    }
}//class

function usort_reorder($a,$b){
    $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'order_id'; //If no sort, default to title
    $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
    $result = strnatcmp($a[$orderby], $b[$orderby]); //Determine sort order
    return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
}

function add_mrw_menu(){
    $hook = add_submenu_page('woocommerce', 'MRW', 'MRW', 'manage_options', 'mrw_orders', 'mrw_order_list' );
    add_action( "load-$hook", 'add_options' );
}

function add_options() {
  global $mrwListTable;
  $option = 'per_page';
  $args = array(
         'label' => 'Pedidos',
         'default' => 10,
         'option' => 'orders_per_page'
         );
  add_screen_option( $option, $args );
  $mrwListTable = new MRW_List_Table();

}
add_action( 'admin_menu', 'add_mrw_menu' );

function mrw_order_list(){
    global $mrwListTable;

    echo '</pre><div class="wrap"><h2>Pedidos MRW</h2>'; 
    $mrwListTable->prepare_items(); 
    ?>
      <form method="post">
        <input type="hidden" name="page" value=<?php echo $_REQUEST['page'] ?>>
    <?php
    $mrwListTable->search_box( 'search', 'search_id' );
    $mrwListTable->display();
    echo '</form></div>'; 
}

function table_data() {      
  global $wpdb;

   $mrw_orders      = $wpdb->prefix . 'mrw_orders';
   $order_items     = $wpdb->prefix . 'woocommerce_order_items';
   $order_itemmeta  = $wpdb->prefix . 'woocommerce_order_itemmeta';
   $order_posts     = $wpdb->prefix . 'posts';

   $data=array();

    if(isset($_POST['s']))
      {
       
      $search=$_POST['s'];

      $search = trim($search);

      $wk_post = $wpdb->get_results("SELECT * FROM (SELECT a.order_id, b.tracking_number, d.post_date, b.date FROM " . $wpdb->prefix ."woocommerce_order_items a JOIN " . $wpdb->prefix . "woocommerce_order_itemmeta c ON a.order_item_id = c.order_item_id JOIN " . $wpdb->prefix ."posts d ON a.order_id = d.ID LEFT JOIN " . $wpdb->prefix . "mrw_orders as b ON a.order_id = b.order_id WHERE c.meta_key = 'method_id' AND c.meta_value = 'mrw') as f WHERE (f.date >= CURDATE() OR f.date IS NULL) AND f.tracking_number LIKE '%%$search%%' OR f.order_id LIKE '%%$search%%'");
      }

    else{

      $wk_post = $wpdb->get_results("SELECT * FROM (SELECT a.order_id, b.tracking_number, d.post_date, b.date FROM " . $wpdb->prefix ."woocommerce_order_items a JOIN " . $wpdb->prefix ."woocommerce_order_itemmeta c ON a.order_item_id = c.order_item_id JOIN " . $wpdb->prefix ."posts d ON a.order_id = d.ID LEFT JOIN " . $wpdb->prefix ."mrw_orders as b ON a.order_id = b.order_id WHERE c.meta_key = 'method_id' AND c.meta_value = 'mrw') as f WHERE f.date >= CURDATE() OR f.date IS NULL");
    }

    $order_id = array();
    $tracking_number = array();
    $date = array();

    $i=0;

    foreach ($wk_post as $wk_posts) {

        $order_id[]=$wk_posts->order_id;

        $tracking_number[]=$wk_posts->tracking_number;

        $date[]=$wk_posts->post_date;

        $data[] = array(
                'order_id'          => $order_id[$i],
                'tracking_number'   => $tracking_number[$i], 
                'post_date'              => $date[$i]
                );

        $i++;
    }

    return $data;
}

function bulk_generate_mrw($shop_order_post_id){

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
    $label_name = NULL;
    $label_to   = NULL;

    $order = new WC_Order($shop_order_post_id);

    $options_table = $wpdb->prefix . "options";

    //Get woocommerce_mrw_carrier settings.
    $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
    $mrwsettings = get_object_vars($mrwsettings[0]);
    $mrwsettings = unserialize($mrwsettings['option_value']);

    $mrwproducts = $order->get_items();
    $mrwweight = get_mrw_weight3($mrwproducts);
    $shipping_weight        =  $mrwweight;

    //In bulk printing there is no third places or options like delivery in franchise,...

    //Get order info
    $billing_phone          =  $order->get_billing_phone();
    $billing_email          =  $order->get_billing_email();
    $shipping_address       =  $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
    $shipping_postcode      =  $order->get_shipping_postcode();
    $shipping_first_name    =  $order->get_shipping_first_name();
    $shipping_last_name     =  $order->get_shipping_last_name();
    $shipping_weight        =  $mrwweight;
    $shipping_city          =  $order->get_shipping_city();
    $order_id               = $shop_order_post_id;
    $mrw_saturday_delivery  = 'N';
    $mrw_franchise_delivery = 'N';
    $mrw_return             = 'N';
    $mrw_packages           = '1';
    $mrw_comments           = '';
    $mrw_service            = $mrwsettings["mrwdefaultservice"];
    $mrw_company_name       = $order->get_shipping_company();
    $mrw_time_slot          = '0';

    //If company name is filled put in the label name, else, put customer name
    if(empty($mrw_company_name)){
        $label_name = $shipping_first_name . ' ' . $shipping_last_name;
    }
    else{
        $label_name = $mrw_company_name;
        $label_to = $shipping_first_name . ' ' . $shipping_last_name;
    }


    //Options of the order Franchise delivery, Saturday delivery, Return, number of packages, comments, service, picking up address.
    $options = array(
    'Service'       =>  $mrw_service,
    'NPack'         =>  $mrw_packages,
    'SatDev'        =>  $mrw_saturday_delivery,
    'FranDel'       =>  $mrw_franchise_delivery,
    'Ret'           =>  $mrw_return,
    'Comm'          =>  $mrw_comments,
    'Third'         =>  'false',
    'address_name'  =>  '',
    'address_street'=>  '',
    'address_number'=>  '',
    'address_pc'    =>  '',
    'address_city'  =>  '',
    'address_phone' =>  '',
    'time_slot'     =>  ''
    ); 

    $codOrigin = '';
    $codAmount = NULL;

    //Check payment method to check if is COD method. By default cod
    if ( get_post_meta( $order->get_id(), '_payment_method', true ) == "cod" )
    {
        $codOrigin = 'O';
        
        //Wordpress takes . as decimal character, change to ,
        $codAmount = number_format($order->get_total(), 2, ',', ' ');           
    }

    //If the service is 0000, 0100, 0110, 0120 or 200 do package apportionment.
    if( $mrw_service == '0000' || $mrw_service == '0100' || $mrw_service == '0110' || $mrw_service == '0120' || $mrw_service == '0200' ){
        $mrwsettings['mrwapportionment'] = 'yes';
    }

    //Check if we are in real or development mode.
    if ($mrwsettings['mrwtype'] == 'development'){
        $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
    }
    else $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';

    //Fill in the notificacions array.
    if($mrw_franchise_delivery == 'E'){
        if ($mrwsettings['mrwnotifications'] == 'sms' && !empty($billing_phone)) {
            $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '3', 'MailSMS' => $billing_phone);
        } 
        else if ($mrwsettings['mrwnotifications'] == 'email' && !empty($billing_email)) {
            $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '3', 'MailSMS' => $billing_email);
        } 
        else if ($mrwsettings['mrwnotifications'] == 'sms+email') {
            if (!empty($order->billing_phone) && !empty($billing_email)) {
                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '3', 'MailSMS' => $billing_phone);
                $notifications_mrw['NotificacionRequest'][1] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '3', 'MailSMS' => $billing_email);
            }
            else if (!empty($order->billing_phone) && empty($order->billing_email)){
                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '3', 'MailSMS' => $billing_phone);
            }
            else if (empty($order->billing_phone) && !empty($order->billing_email)){
                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '3', 'MailSMS' => $order->billing_email);
            }
        } 
    }
    else{
        if ($mrwsettings['mrwnotifications'] == 'sms' && !empty($billing_phone)) {
            $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '4', 'MailSMS' => $billing_phone);
        } 
        else if ($mrwsettings['mrwnotifications'] == 'email' && !empty($billing_email)) {
            $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '4', 'MailSMS' => $billing_email);
        } 
        else if ($mrwsettings['mrwnotifications'] == 'sms+email') {
            if (!empty($order->billing_phone) && !empty($billing_email)) {
                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '4', 'MailSMS' => $billing_phone);
                $notifications_mrw['NotificacionRequest'][1] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '4', 'MailSMS' => $billing_email);
            } 
            else if (!empty($order->billing_phone) && empty($order->billing_email)){
                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '2', 'TipoNotificacion' => '4', 'MailSMS' => $billing_phone);
            }
            else if (empty($order->billing_phone) && !empty($order->billing_email)){
                $notifications_mrw['NotificacionRequest'][0] = array('CanalNotificacion' => '1', 'TipoNotificacion' => '4', 'MailSMS' => $order->billing_email);
            }
        } 
    }

    //Change . , because the web service gets the weight with ,
    $shipping_weight = str_replace(".",",",round($shipping_weight,2,PHP_ROUND_HALF_UP));

    //Package apportionment.
    if ( $mrwsettings['mrwapportionment'] == 'yes'){
        if ($mrwsettings['mrwerrorlog'] == 'yes'){
            mrw_log("Realizamos desglose de bultos (Nº de bultos)", $mrw_packages);
        }
        if ($mrw_packages > 1) {
            for ( $i= 0 ; $i <  $mrw_packages; $i++){
                $mrw_apportion['BultoRequest'][$i] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Bulto ' . $i . ' de ' . $mrw_packages, 'Peso' => str_replace(".",",",round($shipping_weight / $mrw_packages,2,PHP_ROUND_HALF_UP)));
            }
        } else {
                $mrw_apportion['BultoRequest'] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Ref 1 ', 'Peso' => str_replace(".",",",round($shipping_weight,2,PHP_ROUND_HALF_UP)));
        }
    }
    else{
        $mrw_apportion = '';
    }

    // Create the SoapClient instance. 
    $clientMRW = new SoapClient($wsdl_url, array('trace' => true));

    $cabeceras = array(
        'CodigoFranquicia'      =>  $mrwsettings['mrwfranchise'], 
        'CodigoAbonado'         =>  $mrwsettings['mrwsubscriber'], 
        'CodigoDepartamento'    =>  $mrwsettings['mrwdepartment'], //Optional
        'UserName'              =>  $mrwsettings['mrwuser'], 
        'Password'              =>  $mrwsettings['mrwpass'] 
        );

    // Create the header.
    $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $cabeceras); // Headers over the SOAP client object
    $clientMRW->__setSoapHeaders($header);

    $today = date("d/m/Y", time());
    $parametros = array(
        'request' => array(
            'DatosEntrega' => array(
                ## DATOS DESTINATARIO ##
                'Direccion' => array(
                    'CodigoDireccion'   => ''//Optional
                    , 'CodigoTipoVia'   => ''//Optional
                    , 'Via'             => $shipping_address
                    , 'Numero'          => ''
                    , 'Resto'           => ''//Optional
                    , 'CodigoPostal'    => $shipping_postcode
                    , 'Poblacion'       => $shipping_city//Obligatorio
                    , 'CodigoPais'      => '', //Optional
                    )
                , 'Nif'             => ''
                , 'Nombre'          => $label_name
                , 'Telefono'        => empty($billing_phone) ? ' ' : $billing_phone //Optional
                , 'Contacto'        => $label_to //Optional
                , 'ALaAtencionDe'   => $label_to
                , 'Observaciones' => $mrw_comments  //Optional
                
                )
            ## DATOS DEL SERVICIO ##
            , 'DatosServicio' => array(
                'Fecha' => $today //Today or after
                , 'Referencia'          => 'Referencia: ' . $order_id
                , 'EnFranquicia'        => $mrw_franchise_delivery
                , 'CodigoServicio'          => $mrw_service
                , 'DescripcionServicio'     => ''//Optional
                , 'Bultos'                  => $mrw_apportion //Optional
                , 'NumeroBultos'            => $mrw_packages
                , 'Peso'                    => $shipping_weight
                , 'EntregaSabado'           => $mrw_saturday_delivery//'N'
                , 'Retorno'                 => $mrw_return //Optional
                , 'Reembolso'               => $codOrigin //Optional
                , 'ImporteReembolso'        => $codAmount//If COD is selected is mandatory to inform the cost. Indicate decimals with , (coma)
                , 'Notificaciones'          => $notifications_mrw
                , 'TramoHorario'            => '0' //Optional (additional charge)
                ),
        ),
    );

    $responseCode = $clientMRW->TransmEnvio($parametros);

    //Save generate request
    if ($mrwsettings['mrwerrorlog'] == 'yes'){
        mrw_log("MRW Generate Label Request", $clientMRW->__getLastRequest());
        
        if (!empty($responseCode->TransmEnvioResult->Mensaje))
            mrw_log("MRW Generate Message Request", $responseCode->TransmEnvioResult->Mensaje);
    }

    //Save in database
    $table_name = $wpdb->prefix . 'mrw_orders';

    if($mrwsettings['mrwtype']=='live')
    {
        $wsdl_url2 = $wsdl_pro;
    }
    else if($mrwsettings['mrwtype']=='development')
        $wsdl_url2 = $wsdl_test;

    if($responseCode->TransmEnvioResult->Estado == '1' && $responseCode->TransmEnvioResult->NumeroEnvio){
        $mrw_tracking_number = $responseCode->TransmEnvioResult->NumeroEnvio;
        $num_sol         = $responseCode->TransmEnvioResult->NumeroSolicitud;
        $url = $wsdl_url2 . "Panel.aspx?Franq=" . $mrwsettings['mrwfranchise'] . "&Ab=" . $mrwsettings['mrwsubscriber'] . "&Dep=" . $mrwsettings['mrwdepartment'] ."&Usr=" . $mrwsettings['mrwuser'] . "&Pwd=" . $mrwsettings['mrwpass'] . "&NumSol=" . $num_sol . "&NumEnv=" . $mrw_tracking_number;

        $wpdb->insert( 
            $table_name, 
            array( 
                'order_id'        => $order_id, 
                'tracking_number' => $mrw_tracking_number,
                'URL'             => $url,
                'options'         => serialize($options),
                'date'            => date("Y-m-d H:i:s")
                )
            );

        //Download mrw label 
        if ($mrwsettings['mrwtype'] == 'live'){
            $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';
        }
        else if ($mrwsettings['mrwtype'] == 'development'){
            $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
        }

        //SOAP Request
        $clientMRW = new SoapClient($wsdl_url, array('trace' => true));

        $headers = array(
            'CodigoFranquicia'      => $mrwsettings['mrwfranchise'],
            'CodigoAbonado'         => $mrwsettings['mrwsubscriber'],
            'CodigoDepartamento'    => $mrwsettings['mrwdepartment'], 
            'UserName'              => $mrwsettings['mrwuser'], 
            'Password'              => $mrwsettings['mrwpass']); 

        $params = array(
            'request' => array(
                'NumeroEnvio'           => $mrw_tracking_number,
                'SeparadorNumerosEnvio' => ';',
                'FechaInicioEnvio'      => '',
                'FechaFinEnvio'         => '',
                'TipoEtiquetaEnvio'     => '0',
                'ReportTopMargin'       => '1100',
                'ReportLeftMargin'      => '650',
                ),
            );

        $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $headers);

        $clientMRW->__setSoapHeaders($header);

        $responseCode = $clientMRW->EtiquetaEnvio($params);

        if ($mrwsettings['mrwerrorlog'] == 'yes'){
            mrw_log("MRW Download Label Request", $clientMRW->__getLastRequest());
            
            if (!empty($responseCode->GetEtiquetaEnvioResult->Mensaje))
            mrw_log("MRW Download Message Request", $responseCode->GetEtiquetaEnvioResult->Mensaje);
        }

        //Get label code
        $pdf_code = $responseCode->GetEtiquetaEnvioResult->EtiquetaFile;

        //Create pdf

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
            "message" => __( 'There is no tracking information yet', 'woocommerce-mrw-carrier' ), 
            "success" => __( 'The shipping label was generated successfully!', 'woocommerce-mrw-carrier' ), 
            "service"   => get_service_name($mrw_service), 
            "npack" => $mrw_packages, 
            "frandel" => __(mrw_get_sn($mrw_franchise_delivery),'woocommerce-mrw-carrier'),
            "satdel" => __(mrw_get_sn($mrw_saturday_delivery),'woocommerce-mrw-carrier'), 
            "ret" => __(mrw_get_sn($mrw_return),'woocommerce-mrw-carrier'), 
            "time_slot"     => '0',
            "comm"  => $mrw_comments, "state"   => 1);

        $order->update_status($mrwsettings['mrwstatus'], 'order_note');

    }
    //If the generation fails, show the error.
    else{
        $json_arr = array("nosuccess" => __( $responseCode->TransmEnvioResult->Mensaje, 'woocommerce-mrw-carrier' ), "state"    => 0);
    }
}

function bulk_print_mrw($tracking_numbers){

    global $wpdb;

    $options_table = $wpdb->prefix . "options";
    //Get woocommerce_mrw_carrier settings.
    $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
    $mrwsettings = get_object_vars($mrwsettings[0]);
    $mrwsettings = unserialize($mrwsettings['option_value']);

    //Check if we are in real or development mode.
    if ($mrwsettings['mrwtype'] == 'development'){
        $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
    }
    else $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';

    //SOAP Request
    $clientMRW = new SoapClient($wsdl_url, array('trace' => true));

    $headers = array(
        'CodigoFranquicia'      => $mrwsettings['mrwfranchise'],
        'CodigoAbonado'         => $mrwsettings['mrwsubscriber'],
        'CodigoDepartamento'    => $mrwsettings['mrwdepartment'], 
        'UserName'              => $mrwsettings['mrwuser'], 
        'Password'              => $mrwsettings['mrwpass']); 

    $params = array(
        'request' => array(
            'NumeroEnvio'           => implode(",", $tracking_numbers),
            'SeparadorNumerosEnvio' => ',',
            'FechaInicioEnvio'      => '',
            'FechaFinEnvio'         => '',
            'TipoEtiquetaEnvio'     => '0',
            'ReportTopMargin'       => '1100',
            'ReportLeftMargin'      => '650',
            ),
        );

    $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $headers);

    $clientMRW->__setSoapHeaders($header);

    $responseCode = $clientMRW->EtiquetaEnvio($params);

    if ($mrwsettings['mrwerrorlog'] == 'yes'){
        mrw_log("Bulk printing request", $clientMRW->__getLastRequest());
        
        if (!empty($responseCode->GetEtiquetaEnvioResult->Mensaje))
        mrw_log("Bulk printing Message Request", $responseCode->GetEtiquetaEnvioResult->Mensaje);
    }

    //Get label code
    $pdf_code = $responseCode->GetEtiquetaEnvioResult->EtiquetaFile;

    $filename = date("d-m-Y-His");

    //Path for downloading
    $urlUpload = wp_upload_dir();
    $MRWFolder = $urlUpload['basedir'] . '/MRW/';
    $urlLabel = $MRWFolder . $filename . '.pdf';

    $pdf = fopen($urlLabel, 'w');
    fputs($pdf, $pdf_code);
    fclose($pdf);

    return realpath($urlLabel);
}

function bulk_generate_mrw_int($shop_order_post_id){

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
    $label_name = NULL;
    $label_to   = NULL;

    $order = new WC_Order($shop_order_post_id);

    $options_table = $wpdb->prefix . "options";

    //Get woocommerce_mrw_carrier settings.
    $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
    $mrwsettings = get_object_vars($mrwsettings[0]);
    $mrwsettings = unserialize($mrwsettings['option_value']);

    $mrwproducts = $order->get_items();
    $mrwweight = get_mrw_weight3($mrwproducts);
    $shipping_weight        =  $mrwweight;

    //In bulk printing there is no third places or options like delivery in franchise,...

    //Get order info
    $billing_phone          =  $order->get_billing_phone();
    $billing_email          =  $order->get_billing_email();
    $shipping_address       =  $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
    $shipping_postcode      =  $order->get_shipping_postcode();
    $shipping_first_name    =  $order->get_shipping_first_name();
    $shipping_last_name     =  $order->get_shipping_last_name();
    $shipping_weight        =  $mrwweight;
    $shipping_city          =  $order->get_shipping_city();
    $shipping_state         =  $order->get_shipping_state();
    $order_id               = $shop_order_post_id;
    $mrw_packages           = '1';
    $mrwdefaultserviceint = get_mrw_default_service_int();
    $mrw_company_name       = $order->get_shipping_company();
    $country                =  $order->get_shipping_country();

    //If company name is filled put in the label name, else, put customer name
    if(empty($mrw_company_name)){
        $label_name = $shipping_first_name . ' ' . $shipping_last_name;
    }
    else{
        $label_name = $mrw_company_name;
        $label_to = $shipping_first_name . ' ' . $shipping_last_name;
    }


    //Options of the order Franchise delivery, Saturday delivery, Return, number of packages, comments, service, picking up address.
    $options = array(
    'Service'       =>  $mrwdefaultserviceint,
    'NPack'         =>  $mrw_packages,
    'Third'         =>  'false',
    'address_name'  =>  '',
    'address_street'=>  '',
    'address_number'=>  '',
    'address_pc'    =>  '',
    'address_city'  =>  '',
    'address_phone' =>  '',
    ); 

    $codOrigin = '';
    $codAmount = NULL;

    //Check payment method to check if is COD method. By default cod
    if ( get_post_meta( $order->get_id(), '_payment_method', true ) == "cod" )
    {
        $codOrigin = 'O';
        
        //Wordpress takes . as decimal character, change to ,
        $codAmount = number_format($order->get_total(), 2, ',', ' ');           
    }

    //Check if we are in real or development mode.
    if ($mrwsettings['mrwtype'] == 'development'){
        $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
    }
    else $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';

    //Change . , because the web service gets the weight with ,
    $shipping_weight = str_replace(".",",",round($shipping_weight,2,PHP_ROUND_HALF_UP));

    //Package apportionment.
    if ( $mrwsettings['mrwapportionment'] == 'yes'){
        if ($mrwsettings['mrwerrorlog'] == 'yes'){
            mrw_log("Realizamos desglose de bultos int (Nº de bultos)", $mrw_packages);
        }
        if ($mrw_packages > 1) {
            for ( $i= 0 ; $i <  $mrw_packages; $i++){
                $mrw_apportion['BultoRequest'][$i] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Bulto ' . $i . ' de ' . $mrw_packages, 'Peso' => str_replace(".",",",round($shipping_weight / $mrw_packages,2,PHP_ROUND_HALF_UP)));
            }
        } else {
                $mrw_apportion['BultoRequest'] = array('Alto' => '1', 'Largo' => '1', 'Ancho' => '1', 'Dimension' => '3', 'Referencia' => 'Ref 1 ', 'Peso' => str_replace(".",",",round($shipping_weight,2,PHP_ROUND_HALF_UP)));
        }
    }
    else{
        $mrw_apportion = '';
    }

    // Create the SoapClient instance. 
    $clientMRW = new SoapClient($wsdl_url, array('trace' => true));

    $cabeceras = array(
        'CodigoFranquicia'      =>  $mrwsettings['mrwfranchise'], 
        'CodigoAbonado'         =>  $mrwsettings['mrwsubscriber'], 
        'CodigoDepartamento'    =>  $mrwsettings['mrwdepartment'], //Optional
        'UserName'              =>  $mrwsettings['mrwuser'], 
        'Password'              =>  $mrwsettings['mrwpass'] 
        );

    // Create the header.
    $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $cabeceras); // Headers over the SOAP client object
    $clientMRW->__setSoapHeaders($header);

    $today = date("d/m/Y", time());
    $parametros = array(
        'request' => array(
            'DatosEntrega' => array(
                ## DATOS DESTINATARIO ##
                'Direccion' => array(
                    'CodigoDireccion'   => ''//Optional
                    , 'CodigoTipoVia'   => ''//Optional
                    , 'Direccion'             => $shipping_address
                    , 'Numero'          => ''
                    , 'Resto'           => ''//Optional
                    , 'CodigoPostal'    => $shipping_postcode
                    , 'Poblacion'       => $shipping_city//Obligatorio
                    , 'CodigoPais'      => $country
                    , 'Estado'          => $shipping_state

                    )
                , 'Nif'             => ''
                , 'Nombre'          => $label_name
                , 'Telefono'        => empty($billing_phone) ? ' ' : $billing_phone //Optional                
                )
            ## DATOS DEL SERVICIO ##
            , 'DatosServicio' => array(
                'Fecha' => $today //Today or after
                , 'Referencia'          => 'Referencia: ' . $order_id
                , 'CodigoServicio'          => $mrwdefaultserviceint
                , 'DescripcionServicio'     => ''//Optional
                , 'Bultos'                  => $mrw_apportion //Optional
                , 'NumeroBultos'            => $mrw_packages
                , 'Peso'                    => $shipping_weight
                , 'NotificacionSMS'          => empty($billing_phone) ? ' ' : $billing_phone //Optional
                ),
        ),
    );

    $responseCode = $clientMRW->TransmEnvioInternacional($parametros);

    //Save generate request
    if ($mrwsettings['mrwerrorlog'] == 'yes'){
        mrw_log("MRW Generate Label International Request", $clientMRW->__getLastRequest());
        
        if (!empty($responseCode->TransmEnvioInternacionalResult->Mensaje))
            mrw_log("MRW Generate Internacional Message Request", $responseCode->TransmEnvioInternacionalResult->Mensaje);
    }

    //Save in database
    $table_name = $wpdb->prefix . 'mrw_orders';

    if($mrwsettings['mrwtype']=='live')
    {
        $wsdl_url2 = $wsdl_pro;
    }
    else if($mrwsettings['mrwtype']=='development')
        $wsdl_url2 = $wsdl_test;

    if($responseCode->TransmEnvioInternacionalResult->Estado == '1' && $responseCode->TransmEnvioInternacionalResult->NumeroEnvio){
        $mrw_tracking_number = $responseCode->TransmEnvioInternacionalResult->NumeroEnvio;
        $num_sol         = $responseCode->TransmEnvioInternacionalResult->NumeroSolicitud;
        $url = $wsdl_url2 . "Panel.aspx?Franq=" . $mrwsettings['mrwfranchise'] . "&Ab=" . $mrwsettings['mrwsubscriber'] . "&Dep=" . $mrwsettings['mrwdepartment'] ."&Usr=" . $mrwsettings['mrwuser'] . "&Pwd=" . $mrwsettings['mrwpass'] . "&NumSol=" . $num_sol . "&NumEnv=" . $mrw_tracking_number;

        $wpdb->insert( 
            $table_name, 
            array( 
                'order_id'        => $order_id, 
                'tracking_number' => $mrw_tracking_number,
                'URL'             => $url,
                'options'         => serialize($options),
                'date'            => date("Y-m-d H:i:s")
                )
            );

        //Download mrw label 
        if ($mrwsettings['mrwtype'] == 'live'){
            $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';
        }
        else if ($mrwsettings['mrwtype'] == 'development'){
            $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
        }

        //SOAP Request
        $clientMRW = new SoapClient($wsdl_url, array('trace' => true));

        $headers = array(
            'CodigoFranquicia'      => $mrwsettings['mrwfranchise'],
            'CodigoAbonado'         => $mrwsettings['mrwsubscriber'],
            'CodigoDepartamento'    => $mrwsettings['mrwdepartment'], 
            'UserName'              => $mrwsettings['mrwuser'], 
            'Password'              => $mrwsettings['mrwpass']); 

        $params = array(
            'request' => array(
                'NumeroEnvio'           => $mrw_tracking_number,
                'SeparadorNumerosEnvio' => ';',
                'FechaInicioEnvio'      => '',
                'FechaFinEnvio'         => '',
                'TipoEtiquetaEnvio'     => '0',
                'ReportTopMargin'       => '20',
                'ReportLeftMargin'      => '20',
                ),
            );

        $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $headers);

        $clientMRW->__setSoapHeaders($header);

        $responseCode = $clientMRW->EtiquetaEnvioInternacional($params);

        if ($mrwsettings['mrwerrorlog'] == 'yes'){
            mrw_log("MRW Download Label Request", $clientMRW->__getLastRequest());
            
            if (!empty($responseCode->GetEtiquetaEnvioInternacionalResult->Mensaje))
            mrw_log("MRW Download Message Request", $responseCode->GetEtiquetaEnvioInternacionalResult->Mensaje);
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
            "message" => __( 'There is no tracking information yet', 'woocommerce-mrw-carrier' ), 
            "success" => __( 'The shipping label was generated successfully!', 'woocommerce-mrw-carrier' ), 
            "service"   => get_service_name($mrw_service));

        $order->update_status($mrwsettings['mrwstatus'], 'order_note');

    }
    //If the generation fails, show the error.
    else{
        $json_arr = array("nosuccess" => __( $responseCode->TransmEnvioInternacionalResult->Mensaje, 'woocommerce-mrw-carrier' ), "state"    => 0);
    }
}

function bulk_print_mrw_int($tracking_numbers_int){

    global $wpdb;

    $options_table = $wpdb->prefix . "options";
    //Get woocommerce_mrw_carrier settings.
    $mrwsettings = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name = 'woocommerce_mrw_settings'");
    $mrwsettings = get_object_vars($mrwsettings[0]);
    $mrwsettings = unserialize($mrwsettings['option_value']);

    //Check if we are in real or development mode.
    if ($mrwsettings['mrwtype'] == 'development'){
        $wsdl_url = 'http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL';
    }
    else $wsdl_url = 'http://sagec.mrw.es/MRWEnvio.asmx?WSDL';

    //SOAP Request
    $clientMRW = new SoapClient($wsdl_url, array('trace' => true));

    $headers = array(
        'CodigoFranquicia'      => $mrwsettings['mrwfranchise'],
        'CodigoAbonado'         => $mrwsettings['mrwsubscriber'],
        'CodigoDepartamento'    => $mrwsettings['mrwdepartment'], 
        'UserName'              => $mrwsettings['mrwuser'], 
        'Password'              => $mrwsettings['mrwpass']); 

    $params = array(
        'request' => array(
            'NumeroEnvio'           => implode(",", $tracking_numbers_int),
            'SeparadorNumerosEnvio' => ',',
            'FechaInicioEnvio'      => '',
            'FechaFinEnvio'         => '',
            'TipoEtiquetaEnvio'     => '0',
            'ReportTopMargin'       => '20',
            'ReportLeftMargin'      => '20',
            ),
        );

    $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $headers);

    $clientMRW->__setSoapHeaders($header);

    $responseCode = $clientMRW->EtiquetaEnvioInternacional($params);

    if ($mrwsettings['mrwerrorlog'] == 'yes'){
        mrw_log("Bulk printing request", $clientMRW->__getLastRequest());
        
        if (!empty($responseCode->GetEtiquetaEnvioInternacionalResult->Mensaje))
        mrw_log("Bulk printing Message Request", $responseCode->GetEtiquetaEnvioInternacionalResult->Mensaje);
    }

    //Get label code
    $pdf_code = $responseCode->GetEtiquetaEnvioInternacionalResult->EtiquetaFile;

    $filename = date("d-m-Y-His");

    //Path for downloading
    $urlUpload = wp_upload_dir();
    $MRWFolder = $urlUpload['basedir'] . '/MRW/';
    $urlLabel = $MRWFolder . $filename . '-int.pdf';

    $pdf = fopen($urlLabel, 'w');
    fputs($pdf, $pdf_code);
    fclose($pdf);

    return realpath($urlLabel);
}

function get_mrw_tracking_number_bulk($shop_order_post_id)
{
    global $wpdb; 

    $order_array = NULL;
    $tracking_number = NULL;
    
    //Variables to get id and tracking from multidimensional array
    $oid    = 'order_id';
    $tn     = 'tracking_number';
    $turl   = 'URL';
    $orders_table = $wpdb->prefix . "mrw_orders";
    $query = $wpdb->prepare("SELECT * FROM $orders_table WHERE order_id = %s", $shop_order_post_id);

    $order_array = $wpdb->get_results( $query, ARRAY_A );

    if(!empty($order_array)){
        $tracking_number = $order_array[0][$tn];
    }
    else $tracking_number = NULL;

    return $tracking_number;
}

?>