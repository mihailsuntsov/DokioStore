<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

    add_action( 'admin_post_c_get_crm_tax_rates', 'c_get_crm_tax_rates' );
    function c_get_crm_tax_rates() {
        logger('--- Taxes auto sync ---');
        try {
            status_header(200);
            $woocommerce = new Client(get_option('woo_address'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
            $url = 'http://localhost:8080/api/public/woo_v3/syncTaxesToStore?key='.get_option( 'secret_key' );
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            logger('INFO--taxes/c_get_crm_tax_rates-- Getting the list of tax rates from CRM');
            $data = curl_exec($request);
            curl_close($request);
            $array = json_decode($data);
            $ids_pairs = array(); //the array of pairs woo_id and crm_id that will be sent to CRM for update woo_id's
            $was_query_all_store_taxes = false;
            $all_woo_ids = array(); // array of all woo tax ids
            echo('<b>Received data: </b><br>'. $data . '<br>');
            logger('INFO--taxes/c_get_crm_tax_rates-- Received data: '. $data );
            // echo $array->taxes[0]->id;   
            foreach ($array->taxes as $tax ) { // This will search in the 2 jsons
                echo('<b>Current tax: </b><br>');
                print_r ($tax);
                echo('<br>');
                echo('<b>Current tax name: </b><br>');
                print_r ($tax->name);
                echo('<br>');
                // if there is no id of WooCommerce - then this tax rate is not in the WooCommerce - Create tax rate in the WooCommerce
                if($tax->id == NULL) {
                    echo '$tax->id is null<br>';
                    logger('INFO--taxes/c_get_crm_tax_rates-- There is no woo_id of current tax rate with crm_id='.$tax->crm_id.'- then this tax rate is not in the WooCommerce');
                    logger('INFO--taxes/c_get_crm_tax_rates-- Creating tax rate in the WooCommerce...');    
                    $operation_result = $woocommerce->post('taxes', (object) ['rate' => $tax->rate,'name' => $tax->name]);

                    echo('Tax rate created with name = '.$operation_result->name.' , woo_id = '.$operation_result->id.'<br>');
                    logger('INFO--taxes/c_get_crm_tax_rates-- Tax rate created with name = '.$operation_result->name.' and woo_id = '.$operation_result->id);

                    array_push($ids_pairs, (object) ['id'=>$operation_result->id, 'crm_id'=>$tax->crm_id]);
                } else { 
                // If there is id - then MAYBE this tax rate is already in WooCommerce - Update tax rate in WooCommerce
                // Maybe - because this tax rate could be removed manually
                // And firsteval before update I need to check that the WooCommerce has the tax rate with this id:
                    logger('INFO--taxes/c_get_crm_tax_rates-- There is woo_id. Maybe this tax rate is already in WooCommerce (if it wasn\'t removed manually)');
                    if ($was_query_all_store_taxes === false){
                        logger('INFO--taxes/c_get_crm_tax_rates-- Getting all WooCommerce taxes to get the array of all their IDs');
                        $all_woo_taxes = $woocommerce->get('taxes');
                        $was_query_all_store_taxes = true;
                        foreach ($all_woo_taxes as $woo_tax ) {
                            array_push($all_woo_ids,$woo_tax->id);
                        }
                    }
                    // If the tax rate wasn't removed manually, i.e. all_woo_ids[] contains woo's id receqved from CRM
                    if (in_array($tax->id, $all_woo_ids)) {
                        logger('INFO--taxes/c_get_crm_tax_rates-- The woo_id of the current tax rate is in the array - updating the current tax rate with crm_id = '.$tax->crm_id.', woo_id = '.$tax->id.', name = '. $tax->name.', rate = '.$tax->rate);
                        // Updating this tax rate
                        echo('<b>Updating Tax Rate</b> with crm_id = '.$tax->crm_id.', woo_id = '.$tax->id.', name = '. $tax->name.', rate = '.$tax->rate.'<br>');
                        $woocommerce->put('taxes/'.$tax->id,  (object) ['rate' => $tax->rate,'name' => $tax->name]);

                    } else { //if the tax rate was removed manually, i.e. all_woo_ids[] does not contain woo's id receqved from CRM
                        logger('INFO--taxes/c_get_crm_tax_rates-- The tax with woo_id = '.$tax->id.' is not in WooCommerce, it was removed manually.');
                        logger('INFO--taxes/c_get_crm_tax_rates-- Creating tax rate that was removed manually');
                        echo('The tax with woo_id = '.$tax->id.' is not in WooCommerce, it was removed manually.<br>');
                        echo('Creating tax rate that was removed manually<br>');
                        $creation_result = $woocommerce->post('taxes', (object) ['rate' => $tax->rate,'name' => $tax->name]);
                        echo('Created tax rate id = '.$creation_result->id.'<br>');
                        logger('INFO--taxes/c_get_crm_tax_rates-- Created tax rate with id = '.$creation_result->id.'crm_id = '.$tax->crm_id);
                        array_push($ids_pairs, (object) ['id'=>$creation_result->id, 'crm_id'=>$tax->crm_id]);
                    }
                }
            } 
            echo ('<b>ids_pairs:</b><br>');
            foreach ($ids_pairs as $pair ) {   
                echo 'id = ' . $pair->id . ', crm_id = ' . $pair->crm_id.'<br>';
            }
            echo ('<b>all_woo_ids:</b><br>');
            foreach ($all_woo_ids as $woo_id ) {   
                echo 'woo_id = ' . $woo_id.'<br>';
            }
            // Sending ID's of all created in WooCommerce taxes to the CRM for IDs synchronization
            if(count($ids_pairs) >0 ){
                echo '<b>Sending POST request to the CRM server:</b><br>';
                $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","idsSet":'.json_encode($ids_pairs).'}';
                logger ('INFO--taxes/c_get_crm_tax_rates-- Sending POST request syncTaxesIds to the CRM server with data: '.$data_to_sent);
                $url = 'http://localhost:8080/api/public/woo_v3/syncTaxesIds';
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_to_sent);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = curl_exec($curl);
                curl_close($curl);
                echo '<pre>1-<br>';
                print_r ($response);
                echo '<br>-1</pre>'; 
                logger ('INFO--taxes/c_get_crm_tax_rates-- The response: '.$response);
            }

        }  catch (HttpClientException $e) {
            echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
            echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
            echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
            logger ('ERROR--taxes/c_get_crm_tax_rates-- HttpClientException.');
            logger ('The Message: '.print_r($e->getMessage(), true));
            logger ('The Request: '.print_r($e->getRequest(), true));
            logger ('The Response: '.print_r($e->getResponse(), true));
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--taxes/c_get_crm_tax_rates-- The response: '.$e->getMessage());
        }
    }
    





    // add_action( 'admin_post_c_get_crm_categories', 'c_get_crm_categories' );
    // function c_get_crm_categories() {
    //     logger('--- Categories auto sync ---');
    //     try {
    //         status_header(200);
    //         $woocommerce = new Client(get_option('woo_address'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);

    //         $data = [
    //             'name' => 'Cups - 2',
    //             'image' => [
    //                 'src' => 'http://localhost:4200/api/public/getFile/6392f155-820-2022-07-27-23-07-30-633.jpg'
    //             ]
    //         ];
            
    //         echo '<pre>';
    //         print_r($woocommerce->post('products/categories', $data));
    //         echo '</pre>';





    //     }  catch (HttpClientException $e) {
    //         echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
    //         echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
    //         echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
    //         logger ('ERROR--categories/c_get_crm_categories-- The response: '.print_r($e->getResponse()));
    //     } catch (Exception $e) {
    //         echo 'Exception: ',  $e->getMessage(), "\n";
    //         logger ('ERROR--categories/c_get_crm_categories-- The response: '.$e->getMessage());
    //     }




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
    // die("__DIR__ = ".__DIR__.", Server received '{$_POST['backpage']}' from your browser.");




    // }


