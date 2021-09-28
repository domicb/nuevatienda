<?php
/*
Template Name: tema
*/
header('Content-Type: text/html; charset=utf-8');
echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">';

$date = date('Y-m-d');
echo '<div class="alert alert-warning" role="alert">Los pedidos se filtrarán con fecha de entrega a partir de hoy día: '.$date.'</div>';

$fechaBusqueda = $date;
if(isset($_REQUEST['fecha'])){
    $fechaBusqueda = $_REQUEST['fecha'];
    echo 'Ultimo producto filtrado :'.$fechaBusqueda;
}
global $wpdb;

$resultados = $wpdb->get_results( "select
    p.ID as order_id,
    p.post_date,
    max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_email,
    max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_first_name,
    max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_last_name,
    max( CASE WHEN pm.meta_key = '_billing_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_1,
    max( CASE WHEN pm.meta_key = '_billing_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_2,
    max( CASE WHEN pm.meta_key = '_billing_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_city,
    max( CASE WHEN pm.meta_key = '_billing_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_state,
    max( CASE WHEN pm.meta_key = '_billing_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_postcode,
    max( CASE WHEN pm.meta_key = '_shipping_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_first_name,
    max( CASE WHEN pm.meta_key = '_shipping_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_last_name,
    max( CASE WHEN pm.meta_key = '_shipping_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_1,
    max( CASE WHEN pm.meta_key = '_shipping_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_2,
    max( CASE WHEN pm.meta_key = '_shipping_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_city,
    max( CASE WHEN pm.meta_key = '_shipping_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_state,
    max( CASE WHEN pm.meta_key = '_shipping_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_postcode,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
    max( CASE WHEN pm.meta_key = '_order_tax' and p.ID = pm.post_id THEN pm.meta_value END ) as order_tax,
    max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ) as paid_date,
    ( select group_concat( order_item_name separator ' | ' ) from wp_woocommerce_order_items where order_id = p.ID and order_item_type = 'line_item') as order_items
from
    wp_posts p 
    join wp_postmeta pm on p.ID = pm.post_id
    join wp_woocommerce_order_items oi on p.ID = oi.order_id
where
    post_type = 'shop_order' and
    post_date BETWEEN '2020-04-01' AND '2021-11-03' and post_status = 'wc-processing'
group by
    p.ID");

//and post_status = 'wc-processing'
//where
//post_type = 'shop_order' and
//post_date BETWEEN '2020-11-10' AND '2021-11-03' and post_status = 'wc-cancelled'
// productos por id de pedido
// lo usamos una vez tengamos filtrados los pedidos
//select * from wp_woocommerce_order_items where order_id = 10908 and `order_item_type` = 'line_item'

if($resultados)
{
    $arrayIDproductPedidos[0][0] = -1;
    $arrayIDproduct[0] = -1;
    $contador = 0;
    $contadorCocidos = 0;
    $contadorCrudos = 0;
    $contadorTotal = 0;
    $sumaKilos = 0;
    $suma = 0;
     
    echo '<form action="/10940-2/" class="form-inline">
    <div class="form-group mb-2">
        <label for="staticEmail2" class="sr-only">Email</label>
        <input type="text" readonly class="form-control-plaintext" id="staticEmail2" value="Filtra por producto">
    </div>
    <div class="form-group mx-sm-3 mb-2">
    <select class="form-control" name="fecha">';

    foreach ( $resultados as $resultado ) {
        //echo 'PEDIDO EN ESTADO PROCESANDO Nº '.$contador++.'<br>';
        $order = wc_get_order( $resultado->order_id );
        foreach ($order->get_items() as $item_key => $item ){
            $item_data    = $item->get_data();
            $product_name = $item_data['name'];
            $product_IDD = $item_data['id'];
            echo '<option value"'.$product_name.'">'.$product_name.$product_IDD.'</option>';
        }   
    }
    echo '</select></div><button type="submit" class="btn btn-primary mb-2">Filtrar</button></form>';

    echo '<table class="table table-bordered"><thead class="thead-dark"><tr><th scope="col">NºOrden</th><th scope="col">PRODUCTO</th><th scope="col"> CANTIDAD </th><th scope="col"> ELABORACION</th></tr>';
    //var_dump($resultados);
    foreach ( $resultados as $resultado ) {
        //echo 'PEDIDO EN ESTADO PROCESANDO Nº '.$contador++.'<br>';
        $order = wc_get_order( $resultado->order_id );
        $contador++; 
        //echo "<tr><td>".var_dump()."</td>";
        // Iterating through each WC_Order_Item_Product objects
        foreach ($order->get_items() as $item_key => $item ):           
            ## Using WC_Order_Item methods ##
            // Item ID is directly accessible from the $item_key in the foreach loop or
            $item_id = $item->get_id();
            ## Using WC_Order_Item_Product methods ##

            $product      = $item->get_product(); // Get the WC_Product object
            $product_id   = $item->get_product_id(); // the Product id
            $arrayIDS[$suma] = $product_id;
            $variation_id = $item->get_variation_id(); // the Variation id
            $item_type    = $item->get_type(); // Type of the order item ("line_item")
            $item_name    = $item->get_name(); // Name of the product
            $quantity     = $item->get_quantity();  
            $sumaKilos = $sumaKilos + $quantity;
            $tax_class    = $item->get_tax_class();
            $line_subtotal     = $item->get_subtotal(); // Line subtotal (non discounted)
            $line_subtotal_tax = $item->get_subtotal_tax(); // Line subtotal tax (non discounted)
            $line_total        = $item->get_total(); // Line total (discounted)
            $line_total_tax    = $item->get_total_tax(); // Line total tax (discounted)

            ## Access Order Items data properties (in an array of values) ##
            $item_data    = $item->get_data();

            $contador2 = 0;
            //var_dump($item_data);
            //var_dump($item);            
            $cocido = $item_data;
            $product_name = $item_data['name'];
            $product_id   = $item_data['product_id'];
            $variation_id = $item_data['variation_id'];
            $quantity     = $item_data['quantity'];

            $arrayID_IDV_Q['ID']['IDV']['Q'];

            $cadenaFiltro = ' ';
            $conElabor = 0;

            if(isset($_REQUEST['fecha']) &&  $_REQUEST['fecha'] != ''){
                $cadenaFiltro = $_REQUEST['fecha'];
                $encon = strpos($product_name, $cadenaFiltro);
                if($encon === false){
                    //si no encuentra resultados
                }
            }else{//resultados por defecto
                echo '<tr><th>'.$resultado->order_id.'</th></td><td>'. $product_name . ' ID ' .$product_id . ' IDV ' . $variation_id .'</td><td> '.$quantity.'</td>';
                    foreach ($item_data as $primo){
                        $contador2++;                  
                        if($contador2 == 13){
                            //var_dump($primo);
                            foreach($primo as $pri){
                                    
                                $findme   = 'Co';
                                $findme2 = 'Cr';
                                $pos = strpos($pri->value, $findme);
                                $pos2 = strpos($pri->value, $findme2);
                                
                                if($pos === false && $pos2 === false){
                                    
                                }else{echo "<td>".$pri->value."</td></tr>";}   
                                
                                if($pos === false)
                                {
                                    $contadorCrudos = $contadorCrudos+$quantity;
                                }else{
                                    $contadorCocidos = $contadorCocidos+$quantity;
                                }
                            $contadorTotal = $contadorTotal+$quantity;    
                               
                            }
                            //var_dump($primo);echo 'PRIMERO'.'<br>';
                            //var_dump($primo);
                            //var_dump($primo->key['key']);echo 'TERCERO'.'<br>'; 
                        }
                    }
            }
            var_dump($arrayIDVariant);
            
            $tax_class    = $item_data['tax_class'];
            $line_subtotal     = $item_data['subtotal'];
            $line_subtotal_tax = $item_data['subtotal_tax'];
            $line_total        = $item_data['total'];
            $line_total_tax    = $item_data['total_tax'];

            //Get data from The WC_product object using methods (examples)
            $product        = $item->get_product(); // Get the WC_Product object
            //$product_type   = $product->get_type();
            //$product_sku    = $product->get_sku();
            //$product_price  = $product->get_price();
            //$stock_quantity = $product->get_stock_quantity();
        endforeach;       
    }
    echo '<div class="alert alert-primary" role="alert">Quedan por elaborar un total de : '.$sumaKilos.' productos para un total de '.$contador.' pedidos</div>';
    echo '<div class="alert alert-primary" role="alert">donde '.$contadorCocidos.' seran productos filtrados a cocer y '.$contadorCrudos.' seran crudos suman un total de '.$contadorTotal.'</div>';
    //var_dump($arrayIDproduct);
    //var_dump($arrayIDS);
}else{
    echo 'no tiene';
}



?>

