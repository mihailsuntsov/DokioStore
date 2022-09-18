
<?php
require __DIR__ . '/../vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;


add_action( 'admin_post_c_get_crm_tax_rates', 'c_get_crm_tax_rates' );
function c_get_crm_tax_rates() {
    $woocommerce = new Client(get_option('woo_address'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
    status_header(200);
    // var_dump($_POST);


    $url = 'http://localhost:8080/api/public/woo_v3/syncTaxesToStore?key='.get_option( 'secret_key' );
    $request = curl_init($url); 
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_HEADER, 0);
    $data = curl_exec($request);
    curl_close($request);
    header('Content-Type: text/html; charset=UTF-8');
    echo "<pre>";
    print_r($data);
    echo "</pre>";

    
    $array = json_decode($data);
    // echo "<pre>";
    // print_r($array);
    // echo "</pre>";

    echo "<pre>";


//     foreach ($array as $key ) { // This will search in the 2 jsons
//         print_r ($key); // This will show jsut the value f each key like "var1" will print 9
//    }


// echo $array->taxes[0]->id;

    foreach ($array->taxes as $tax ) { // This will search in the 2 jsons
        
        print_r ($tax->id); // This will show jsut the value f each key like "var1" will print 9
        
        // if there is no id of WooCommerce - then this tax rate is not in the WooCommerce - Create tax rate in the WooCommerce
        if($tax->id == NULL) {
            echo 'is null';
                
            $data = [
                'rate' => $tax->rate,
                'name' => $tax->name
            ];    

            $operation_result = $woocommerce->post('taxes', $data);
            echo('Tax rate created with id = '.$operation_result->id.'<br>');
            echo "<pre>";
            echo json_encode($operation_result);
            echo "</pre>";
        }





   }


   echo "</pre>";



    // wp_redirect($_POST['backpage'],302 ); 




    // try {

    //   echo '<pre>';
    //   print_r($woocommerce->get('products'));
    //   echo '</pre>';

    // } catch (HttpClientException $e) {
    //     echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
    //     echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
    //     echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
    // }

    die("__DIR__ = ".__DIR__.", Server received '{$_POST['backpage']}' from your browser.");
}


