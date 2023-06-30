<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

    add_action( 'admin_post_c_get_crm_attributes', 'c_get_crm_attributes' );
    function c_get_crm_attributes() {
        logger('--- Attributes auto sync ---');
        try {
            status_header(200);
            $woocommerce = new Client(get_option('siteurl'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
            
              
            // Connection possibility

            $plugin_file = get_plugin_data( WP_PLUGIN_DIR . '/dokio-store/dokio-store.php');
            $url = get_option( 'API_address' ).'/isLetSync?key='.get_option( 'secret_key' ).'&plugin_version='.$plugin_file['Version'];
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            logger('INFO--attributes/c_get_crm_attributes-- Connection possibility requesting...');
            $data = curl_exec($request);
            $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);

            if($httpcode==200){
                $array = json_decode($data);
                if($array->is_sync_allowed==true){
            


            
                    
                    $url = get_option( 'API_address' ).'/syncProductAttributesToStore?key='.get_option( 'secret_key' );
                    $request = curl_init($url); 
                    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($request, CURLOPT_HEADER, 0);
                    logger('INFO--attributes/c_get_crm_attributes-- Getting the list of attributes from CRM');
                    $data = curl_exec($request);
                    curl_close($request);

                    if($httpcode==200){


                        $array = json_decode($data);
                        if($array->queryResultCode==1){

                            $ids_pairs = array(); //the array of pairs woo_id and crm_id that will be sent to CRM for update woo_id's
                            $was_query_all_store_attributes = false;
                            $all_woo_ids = array(); // array of all woo attribute ID's
                            // $all_crm_ids = array(); // array of all crm attribute ID's
                            // $all_crm_woo_ids_to_sync = array(); // array of all Woo ID's in CRM that sent to synchronization
                            $all_crm_woo_ids = array(); // array of all Woo ID's in CRM 
                            $per_page=100;    

                            $all_crm_woo_ids = $array->allProductAttributesWooIds;
                            // get all crm attributes ID's and all Woo ID's in CRM
                            // foreach ($array->productAttributes as $crm_attribute ) {
                            //     array_push($all_crm_woo_ids_to_sync, $crm_attribute->woo_id); 
                            // }
                            //echo('<b>Received data: </b><br>'. $data . '<br>');
                            //logger('INFO--attributes/c_get_crm_attributes-- Received data: '. $data );
                        
                            


                            //Getting all WooCommerce attributes to get the array of all their IDs
                            logger('INFO--attributes/c_get_crm_attributes-- Getting all WooCommerce attributes to get the array of all their IDs');
                            $all_woo_attributes = $woocommerce->get('products/attributes');
                            if(count($all_woo_attributes)==$per_page){// 100 per page is max number in WooCommerce, but may be this is not all attributes
                                echo('this is not all attributes<br>');
                                $page=2;
                                $do_cycle=true;
                                while ($do_cycle) {
                                    echo("page = " . $page ."<br>");
                                    $current_cycle_attributes = $woocommerce->get('products/attributes', ['per_page' => $per_page, 'page' => $page]);
                                    if(count($current_cycle_attributes) > 0){
                                        foreach ($current_cycle_attributes as $ccc ) {
                                            array_push($all_woo_attributes, $ccc);
                                        }
                                    } else $do_cycle = false;
                                    if(count($current_cycle_attributes) < $per_page) $do_cycle = false;
                                    $page++;
                                }
                            }
                            echo '<b>All woo_attributes:</b><br><pre>';print_r($all_woo_attributes);echo '</pre>';
                            // get all woo attributes ID's
                            foreach ($all_woo_attributes as $woo_attribute ) {
                                array_push($all_woo_ids, $woo_attribute->id);
                            }  
                            //Deleting attributes that was deleted in CRM
                            
                            // logger('INFO--attributes/c_get_crm_attributes--  All store Woo IDs:');
                            // foreach($all_woo_ids as $woo_id) {
                            //     logger('INFO--attributes/c_get_crm_attributes--'.$woo_id);
                            // }
                            // logger('INFO--attributes/c_get_crm_attributes--  All CRM Woo IDs:');
                            // foreach($all_crm_woo_ids as $woo_id) {
                            //     logger('INFO--attributes/c_get_crm_attributes--'.$woo_id);
                            // }
                            $wasDelLog=false;
                            foreach ($all_woo_ids as $curr_woo_id ) {
                                if (!in_array($curr_woo_id, $all_crm_woo_ids)){
                                    if(!$wasDelLog){$wasDelLog=true; logger('INFO--attributes/c_get_crm_attributes-- Deleting attributes that was deleted in CRM:');}
                                    echo('<br>Trying to delete attribute with woo_id = '.$curr_woo_id.'<br>');
                                    logger('INFO--attributes/c_get_crm_attributes-- Trying to delete attribute with woo_id = '.$curr_woo_id);
                                    print_r($woocommerce->delete('products/attributes/'.$curr_woo_id, ['force' => true]));
                                }
                            }



                            if(count($array->productAttributes)>0) logger('INFO--attributes/c_get_crm_attributes-- Cycle by all CRM attributes:');
                                else logger('INFO--attributes/c_get_crm_attributes-- No data for synchronize');
                            foreach ($array->productAttributes as $attribute ) { // This will search in the 2 jsons
                                echo('<b>Current attribute: </b><br>');
                                print_r ($attribute);
                                echo('<br>');
                                echo('<b>Current attribute name: </b><br>');
                                print_r ($attribute->name);
                                echo('<br>');
                                // if there is no id of WooCommerce - then this attribute is not in the WooCommerce - Create attribute in the WooCommerce
                                if($attribute->woo_id == NULL) {
                                    echo '$attribute->woo_id is null<br>';
                                    logger('INFO--attributes/c_get_crm_attributes-- There is no woo_id of current attribute with crm_id='.$attribute->crm_id.'- then this attribute is not in the WooCommerce');
                                    logger('INFO--attributes/c_get_crm_attributes-- Creating attribute in the WooCommerce...');   
                                    $operation_result = $woocommerce->post('products/attributes', (object) ['name' => $attribute->name, 'slug' => $attribute->slug, 'type' => $attribute->type, 'order_by' => $attribute->order_by, 'has_archives' => $attribute->has_archives]);
                                    echo('Attribute created with name = '.$operation_result->name.' , woo_id = '.$operation_result->id.'<br>');
                                    logger('INFO--attributes/c_get_crm_attributes-- Attribute created with name = '.$operation_result->name.' and woo_id = '.$operation_result->id);
                                    array_push($ids_pairs, (object) ['id'=>$operation_result->id, 'crm_id'=>$attribute->crm_id]);
                                } else { 
                                // If there is woo_id - then MAYBE this attribute is already in WooCommerce - Update attribute in WooCommerce
                                // Maybe - because this attribute could be removed manually (bad, bad users!)
                                // And firsteval before update I need to check that the WooCommerce has the attribute with this id:
                                    logger('INFO--attributes/c_get_crm_attributes-- There is woo_id. Maybe this attribute is already in WooCommerce (if it wasn\'t removed manually)');
                                    
                                    // If the sent from CRM to here attribute wasn't removed manually in WooCommerce, i.e. all_woo_ids[] contain woo's id receqved from CRM
                                    if (in_array($attribute->woo_id, $all_woo_ids)) {
                                        logger('INFO--attributes/c_get_crm_attributes-- The woo_id of the current attribute is in the array - updating the current attribute with crm_id = '.$attribute->crm_id.', woo_id = '.$attribute->woo_id.', name = '. $attribute->name.', slug = '.$attribute->slug);
                                        // Updating this attribute
                                        echo('<b>Updating Attribute</b> with crm_id = '.$attribute->crm_id.', woo_id = '.$attribute->woo_id.', name = '. $attribute->name.', slug = '.$attribute->slug.', type = '.$attribute->type.', order_by = '.$attribute->order_by.', has_archives = '.$attribute->has_archives.'<br>');
                                        $woocommerce->put('products/attributes/'.$attribute->woo_id, (object) ['name' => $attribute->name, 'slug' => $attribute->slug, 'type' => $attribute->type, 'order_by' => $attribute->order_by, 'has_archives' => $attribute->has_archives]);
                                        array_push($ids_pairs, (object) ['id'=>$attribute->woo_id, 'crm_id'=>$attribute->crm_id]);
                                    } else { //if the attribute was removed manually, i.e. all_woo_ids[] does not contain woo's id receqved from CRM
                                        logger('INFO--attributes/c_get_crm_attributes-- The attribute with woo_id = '.$attribute->woo_id.' is not in WooCommerce, it was removed manually.');
                                        logger('INFO--attributes/c_get_crm_attributes-- Creating attribute that was removed manually');
                                        echo('The attribute with woo_id = '.$attribute->woo_id.' is not in WooCommerce, it was removed manually.<br>');
                                        echo('Creating attribute that was removed manually<br>');
                                        $creation_result = $woocommerce->post('products/attributes', (object) ['name' => $attribute->name, 'slug' => $attribute->slug, 'type' => $attribute->type, 'order_by' => $attribute->order_by, 'has_archives' => $attribute->has_archives]);
                                        echo('Created attribute id = '.$creation_result->id.'<br>');
                                        logger('INFO--attributes/c_get_crm_attributes-- Created attribute with id = '.$creation_result->id.'crm_id = '.$attribute->crm_id);
                                        array_push($ids_pairs, (object) ['id'=>$creation_result->id, 'crm_id'=>$attribute->crm_id]);
                                    }
                                }
                            } 

                            

                            // echo ('<b>ids_pairs:</b><br>');
                            // foreach ($ids_pairs as $pair ) {   
                            //     echo 'id = ' . $pair->id . ', crm_id = ' . $pair->crm_id.'<br>';
                            // }
                            // echo ('<b>all_woo_ids:</b><br>');
                            // foreach ($all_woo_ids as $woo_id ) {   
                            //     echo 'woo_id = ' . $woo_id.'<br>';
                            // }
                            // Sending ID's of all created in WooCommerce attributes to the CRM for IDs synchronization
                            if(count($ids_pairs) >0 ){
                                echo '<b>Sending POST request to the CRM server:</b><br>';
                                $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","idsSet":'.json_encode($ids_pairs).'}';
                                logger ('INFO--attributes/c_get_crm_attributes-- Sending POST request syncProductAttributesIds to the CRM server with data: '.$data_to_sent);
                                $url = get_option( 'API_address' ).'/syncProductAttributesIds';
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
                                logger ('INFO--attributes/c_get_crm_attributes-- The response: '.$response);
                            }

                        } else {
                            logger ('ERROR--terms/c_get_crm_terms-- Server error with response code = '.$array->queryResultCode.'. Synchronization failed!');
                        }


                    } else {
                        echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                        logger ('ERROR--attributes/c_get_crm_attributes-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!, Received data = '.$data);
                    }


                } else {
                    echo('Connection possibility rejected. Reason: '.$array->reason);  
                    logger('INFO--attributes/c_get_crm_attributes-- Connection possibility rejected. Reason: '.$array->reason);  
                }


            } else {
                echo '<b>Server error with response code = '.$httpcode.' Connection possibility request failed!</b><br>';
                logger ('ERROR--attributes/c_get_crm_attributes-- Server error with response code = '.$httpcode.', Response = '.$response.' Connection possibility request failed!, Received data = '.$data);
            }





        }  catch (HttpClientException $e) {
            echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
            echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
            echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
            logger ('ERROR--attributes/c_get_crm_attributes-- HttpClientException.');
            logger ('The Message: '.print_r($e->getMessage(), true));
            logger ('The Request: '.print_r($e->getRequest(), true));
            logger ('The Response: '.print_r($e->getResponse(), true));
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--attributes/c_get_crm_attributes-- The response: '.$e->getMessage());
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        } catch(Throwable $e){
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--attributes/c_get_crm_attributes-- The response: '.$e->getMessage());
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        }
    }
    

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


