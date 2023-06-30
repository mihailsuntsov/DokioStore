<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

    add_action( 'admin_post_c_get_crm_vatiations', 'c_get_crm_variations' );
    function c_get_crm_variations() {
        logger('--- Variations auto sync ---');
        try {
            status_header(200);
            $woocommerce = new Client(get_option('siteurl'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3','timeout' => 240]);
            

            $plugin_file = get_plugin_data( WP_PLUGIN_DIR . '/dokio-store/dokio-store.php');
            $url = get_option( 'API_address' ).'/isLetSync?key='.get_option( 'secret_key' ).'&plugin_version='.$plugin_file['Version'];
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            logger('INFO--variations/c_get_crm_variations-- Connection possibility requesting...');
            $data = curl_exec($request);
            $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);
            if($httpcode==200){
                $array = json_decode($data);
                if($array->is_sync_allowed==true){



                    $url = get_option( 'API_address' ).'/countVariationsToStoreSync?key='.get_option( 'secret_key' );
                    echo $url;
                    $request = curl_init($url); 
                    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($request, CURLOPT_HEADER, 0);
                    curl_setopt($request, CURLOPT_TIMEOUT,500); // 500 seconds
                    curl_setopt($request, CURLOPT_FOLLOWLOCATION, false);
                    logger('INFO--variations/c_get_crm_variations-- Getting the number of variations to synchronize from CRM');
                    $data = curl_exec($request);
                    $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
                    curl_close($request);
                    if($httpcode==200){
                        echo('<br><b>Received data: </b><br>'. $data . '<br>');
                        logger('INFO--variations/c_get_crm_variations-- Received data: '. $data );
                        
                        
                        $array = json_decode($data);
                        if($array->queryResultCode==1){


                            
                            $ids_pairs = array(); //the array of pairs woo_id and crm_id that will be sent to CRM for update woo_id's
                            $variationsQuantity = $array->productCount;
                            echo('<b>Quantity of variations to sync: </b>'. $variationsQuantity . '<br>');
                            logger('INFO--variations/c_get_crm_variations-- Quantity of variations to sync: ' . $variationsQuantity);
                            
                            if($variationsQuantity>0){

                                $firstResult = 0;
                                $maxResults  = 100;
                                $totalNumOfQueryCycles = ceil($variationsQuantity/$maxResults);
                                echo('<b>Total number of query cycles: </b>'. $totalNumOfQueryCycles . '<br>');
                                logger('INFO--variations/c_get_crm_variations-- Total number of query cycles: ' . $totalNumOfQueryCycles);
                                
                                $currentCycle = 0;
                                while ($currentCycle < $totalNumOfQueryCycles){
                                    echo('<b>Current cycle: </b>'. $currentCycle . '<br> $firstResult: ' . $firstResult . '<br> $maxResults: ' . $maxResults. '<br>');
                                    logger('INFO--variations/c_get_crm_variations-- Current cycle: ' . $currentCycle);

                                    $url = get_option( 'API_address' ).'/syncVariationsToStore?key='.get_option( 'secret_key' ).'&first_result='.$firstResult.'&max_results='.$maxResults;
                                    $request = curl_init($url); 
                                    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($request, CURLOPT_HEADER, 0);
                                    curl_setopt($request, CURLOPT_TIMEOUT,500); // 500 seconds
                                    curl_setopt($request, CURLOPT_FOLLOWLOCATION, false);
                                    logger('INFO--variations/c_get_crm_variations-- Getting the list of products from CRM');
                                    $data = curl_exec($request);
                                    $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
                                    curl_close($request);
                        
                                    if($httpcode==200){
                        
                                        echo('<b>Received data: </b><br>'. $data . '<br>');
                                        $array = json_decode($data);

                                        foreach ($array->variations as $crm_variation ) {
                                            echo('<b>Current variation: </b><br>');
                                            print_r ($crm_variation);
                                            echo('<br>');
                                            echo('<b>Current product name: </b><br>');
                                            print_r ($crm_variation->name);
                                            echo('<br>');
                                            $currentVariationAttributesArray =    formVariationAttributesArray ($crm_variation->listOfVariationAttributes);
                                            $currentVariationObject = array(
                                                'regular_price' => $crm_variation->regular_price, 
                                                'sale_price' => $crm_variation->sale_price, 
                                                'description' => $crm_variation->description, 
                                                'attributes' =>$currentVariationAttributesArray,
                                                'stock_status' => $crm_variation->stock_status, 
                                                'sku' => $crm_variation->sku, 
                                                'stock_quantity' => $crm_variation->stock_quantity, 
                                                'manage_stock' => $crm_variation->manage_stock, 
                                                'backorders' => $crm_variation->backorders, 
                                                'menu_order' => $crm_variation->menu_order, 
                                                'image' => [
                                                    'src'   => ($crm_variation->image->img_address == NULL)?'':$crm_variation->image->img_address,
                                                    'name'  => ($crm_variation->image->img_address == NULL)?'':$crm_variation->image->img_original_name,
                                                    'alt'   => ($crm_variation->image->img_address == NULL)?'':$crm_variation->image->img_alt                   
                                                ]
                                            );
                                            if($crm_variation->woo_id == NULL) {
                                                echo '$crm_variation->woo_id is null<br>';
                                                logger('INFO--variations/c_get_crm_variations-- There is no woo_id of current variation with crm_id = '.$crm_variation->crm_id.', then this variation is not in the WooCommerce');
                                                logger('INFO--variations/c_get_crm_variations-- Creating variation in the WooCommerce...'); 

                                                $operation_result = $woocommerce->post('products/'.$crm_variation->parent_product_woo_id.'/variations', (object)$currentVariationObject);
                                                echo('Variation (crm_id = '.$crm_variation->crm_id.') created with woo_id = '.$operation_result->id.'<br>');
                                                logger('INFO--variations/c_get_crm_variations-- Variation (crm_id = '.$operation_result->crm_id.') created with woo_id = '.$operation_result->id);
                                                array_push($ids_pairs, (object) ['id'=>$operation_result->id, 'crm_id'=>$crm_variation->crm_id]);
                                            } else {
                                                // If there is woo_id - then MAYBE this variation is already in WooCommerce - Update variation in WooCommerce
                                                // Maybe - because this variation could be removed manually (bad, bad users!)
                                                // And firsteval before update I need to check that the WooCommerce has the variation with this id:
                                                logger('INFO--variations/c_get_crm_variations-- There is woo_id. Maybe this variation is already in WooCommerce (if it wasn\'t removed manually)');
                                                
                                                $variations = $woocommerce->get('products/'.$crm_variation->parent_product_woo_id.'/variations');
                                                // logger('INFO--variations/c_get_crm_variation-- $variations = '.json_encode($variations));
                                                $current_product_variations_woo_ids = wp_list_pluck( $variations, 'id' ); 
                                                $woo_variation_picture_name='';

                                                foreach ($variations as $variation) {
                                                    // logger('INFO--variations/c_get_crm_variation-- $variation = '.json_encode($variation));
                                                    if($variation->id == $crm_variation->woo_id){
                                                        //  logger('INFO--variations/c_get_crm_variations-- image->name = '.$variation->image->name);
                                                        $woo_variation_picture_name = $variation->image->name;
                                                    }
                                                }
                                                logger('INFO--variations/c_get_crm_variations-- All current product (with id='.$crm_variation->parent_product_woo_id.') variations woo_ids: '.json_encode($current_product_variations_woo_ids));
                                            
                                                if (in_array($crm_variation->woo_id, $current_product_variations_woo_ids)) {
                                                    logger('INFO--variations/c_get_crm_variations-- The woo_id of the current variation with id='.$crm_variation->woo_id.' is in the array  '.json_encode($current_product_variations_woo_ids).'. Updating the current variation with crm_id = '.$crm_variation->crm_id.', woo_id = '.$crm_variation->woo_id.', variation object = '. json_encode($currentVariationObject));

                                                    logger('INFO--variations/c_get_crm_variations-- woo_variation_picture_name: '.$woo_variation_picture_name.', $currentVariationObject[image][name]= '.$currentVariationObject['image']['name']); 
                                                    if($woo_variation_picture_name == $currentVariationObject['image']['name']){
                                                        unset($currentVariationObject['image']);
                                                    }

                                                    // Updating this variation
                                                    echo('<b>Updating variation</b> with crm_id = '.$crm_variation->crm_id.', woo_id = '.$crm_variation->woo_id.', sku = '. $crm_variation->sku.', description = '.$crm_variation->description.', menu_order = '.$crm_variation->menu_order.'<br>');
                                                    echo('<b>Changed variation: </b><br><pre>');
                                                    print_r ($woocommerce->put('products/'.$crm_variation->parent_product_woo_id.'/variations/'.$crm_variation->woo_id, (object)$currentVariationObject));
                                                    echo('</pre><br>');
                                                    
                                                    array_push($ids_pairs, (object) ['id'=>$crm_variation->woo_id, 'crm_id'=>$crm_variation->crm_id]);
                                                } else { //if the variation was removed manually, i.e. current_product_variations_woo_ids[] does not contain woo's id receqved from CRM
                                                    logger('INFO--variations/c_get_crm_variations-- The variation with woo_id = '.$crm_variation->woo_id.' is not in WooCommerce, it was removed manually.');
                                                    logger('INFO--variations/c_get_crm_variations-- Creating variation that was removed manually');
                                                    echo('The variation with woo_id = '.$crm_variation->woo_id.' is not in WooCommerce, it was removed manually.<br>');
                                                    echo('Creating variation that was removed manually<br>');
                                                    //$currentVariationObject['images'] = $currentProductImagesArray;
                                                    $creation_result = $woocommerce->post('products/'.$crm_variation->parent_product_woo_id.'/variations', (object)$currentVariationObject);
                                                    echo('Created variation woo_id = '.$creation_result->id.'<br>');
                                                    logger('INFO--variations/c_get_crm_variations-- Created variation with woo_id = '.$creation_result->id.', crm_id = '.$crm_variation->crm_id);
                                                    array_push($ids_pairs, (object) ['id'=>$creation_result->id, 'crm_id'=>$crm_variation->crm_id]);
                                                }
                                            }
                                        }
                                    } else {
                                        echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                                        logger ('ERROR--variations/c_get_crm_variations-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!, Received data = '.$data);
                                    }
                                    $firstResult = $firstResult + $maxResults;
                                    $currentCycle++;
                                }
                                
                                // Sending ID's of all created in WooCommerce variations to the CRM for IDs synchronization  
                                echo ('<b>ids_pairs:</b><br>');
                                foreach ($ids_pairs as $pair ) {   
                                    echo 'woo_id = ' . $pair->id . ', crm_id = ' . $pair->crm_id.'<br>';
                                }
                              
        //    /--------------------<
            /*|*/                
            /*|*/               
            /*|*/           } else {
            /*|*/               echo '<b>Total variations from DokioCRM to be synchronized is 0</b><br>';
            /*|*/                logger ('WARN--variations/c_get_crm_variations-- Total variations from DokioCRM to be synchronized is 0. DokioCRM hasn\'t non-deleted variations');
            /*|*/           }
            /*|*/
            /*|*/
            /*|*/
            /*|*/
            /*|*/          // if(count($ids_pairs) >0 ){
        //    \------------>// send this query in any case to set job of products synchronization as finished 
                            echo '<b>Sending synchronization set of ID\'s to the CRM server:</b><br>';
                            $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","idsSet":'.json_encode($ids_pairs).'}';
                            logger ('INFO--variations/c_get_crm_variations-- Sending POST request syncVariationsIds to the CRM server with data: '.$data_to_sent);
                            $url = get_option( 'API_address' ).'/syncVariationsIds';
                            $curl = curl_init($url);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_to_sent);
                            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                            curl_setopt($curl, CURLOPT_TIMEOUT,500); // 500 seconds
                            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                            $response = curl_exec($curl);
                            curl_close($curl);
                            echo '<pre>The response of syncVariationsIds: ';
                            print_r ($response);
                            echo '<br></pre>';
                            switch ($response) {
                                case NULL:
                                    logger ('ERROR--variations/c_get_crm_variations/syncVariationsIds: Error of the Sending synchronization set of ID\'s to the CRM server!:Response: '.$response); 
                                    break;
                                case '1':
                                    logger ('INFO--variations/c_get_crm_variations/syncVariationsIds: Success! Response: '.$response);
                                    break;
                                case '-200':
                                    logger ('ERROR--variations/c_get_crm_variations/syncVariationsIds: Error of the Sending synchronization set of ID\'s to the CRM server!: WrongCrmSecretKeyException. Response: '.$response);
                                    break;
                            }    
                        // }









                        } else {
                            logger ('ERROR--variations/c_get_crm_variations-- Server error with response code = '.($array->queryResultCode!=NULL?$array->queryResultCode:'null').'. Synchronization failed!');
                        }


                    } else {
                        echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                        logger ('ERROR--variations/c_get_crm_variations-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!, Received data = '.$data);
                    }


                } else {
                    echo('Connection possibility rejected. Reason: '.$array->reason);  
                    logger('INFO--variations/c_get_crm_variations-- Connection possibility rejected. Reason: '.$array->reason);  
                }


            } else {
                echo '<b>Server error with response code = '.$httpcode.' Connection possibility request failed!</b><br>';
                logger ('ERROR--variations/c_get_crm_variations-- Server error with response code = '.$httpcode.', Response = '.$response.' Connection possibility request failed!, Received data = '.$data);
            }


        } catch (HttpClientException $e) {
            echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
            echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
            echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
            logger ('ERROR--variations/c_get_crm_variations-- HttpClientException.');
            logger ('The Message: '.print_r($e->getMessage(), true));
            logger ('The Request: '.print_r($e->getRequest(), true));
            logger ('The Response: '.print_r($e->getResponse(), true));
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--variations/c_get_crm_variations-- The response: '.$e->getMessage());
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        } catch(Throwable $e){
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--variations/c_get_crm_variations-- The response: '.$e->getMessage());
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        }
    }
    
    function formVariationAttributesArray($arr){      
        $attributesArray=[];
        echo 'Variation Attributes Array: \n';
        Print_r($arr);
        foreach ($arr as $e ) {
            array_push($attributesArray,[
                'id' => $e->id,
                'option' => $e->option,
            ]);
        }
        return $attributesArray;
    }



