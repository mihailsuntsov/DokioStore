<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

    add_action( 'admin_post_c_get_crm_products', 'c_get_crm_products' );
    function c_get_crm_products() {
        logger('--- Products auto sync ---');
        try {
            status_header(200);
            $woocommerce = new Client(get_option('woo_address'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3','timeout' => 240]);
            
            $url = 'http://localhost:8080/api/public/woo_v3/countProductsToStoreSync?key='.get_option( 'secret_key' );
            echo $url;
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            curl_setopt($request, CURLOPT_TIMEOUT,500); // 500 seconds
            curl_setopt($request, CURLOPT_FOLLOWLOCATION, false);
            logger('INFO--products/c_get_crm_products-- Getting the number of products to synchronize from CRM');
            $data = curl_exec($request);
            $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);
            $all_woo_products_ids = wc_get_products( array( 'return' => 'ids', 'limit' => -1 ) ); // array of all woo products ID's
            if($httpcode==200){
                echo('<br><b>Received data: </b><br>'. $data . '<br>');
                logger('INFO--products/c_get_crm_products-- Received data: '. $data );
                $array = json_decode($data);
                $ids_pairs = array(); //the array of pairs woo_id and crm_id that will be sent to CRM for update woo_id's
                $productsQuantity = $array->productCount;
                echo('<b>Quantity of products to sync: </b>'. $productsQuantity . '<br>');
                logger('INFO--products/c_get_crm_products-- Quantity of products to sync: ' . $productsQuantity);
                
                if($productsQuantity>0){

                    $firstResult = 0;
                    $maxResults  = 100;
                    $ids_pairs = array(); //the array of pairs woo_id and crm_id that will be sent to CRM for update woo_id's
                    
                    $totalNumOfQueryCycles = ceil($productsQuantity/$maxResults);
                    echo('<b>Total number of query cycles: </b>'. $totalNumOfQueryCycles . '<br>');
                    logger('INFO--products/c_get_crm_products-- Total number of query cycles: ' . $totalNumOfQueryCycles);
                    
                    $currentCycle = 0;
                    while ($currentCycle < $totalNumOfQueryCycles){
                        echo('<b>Current cycle: </b>'. $currentCycle . '<br> $firstResult: ' . $firstResult . '<br> $maxResults: ' . $maxResults. '<br>');
                        logger('INFO--products/c_get_crm_products-- Current cycle: ' . $currentCycle);

                        // $woocommerce = new Client(get_option('woo_address'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
                        $url = 'http://localhost:8080/api/public/woo_v3/syncProductsToStore?key='.get_option( 'secret_key' ).'&first_result='.$firstResult.'&max_results='.$maxResults;
                        $request = curl_init($url); 
                        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($request, CURLOPT_HEADER, 0);
                        curl_setopt($request, CURLOPT_TIMEOUT,500); // 500 seconds
                        curl_setopt($request, CURLOPT_FOLLOWLOCATION, false);
                        logger('INFO--products/c_get_crm_products-- Getting the list of products from CRM');
                        $data = curl_exec($request);
                        $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
                        curl_close($request);
            
                        if($httpcode==200){
            
                            echo('<b>Received data: </b><br>'. $data . '<br>');
                            // logger('INFO--products/c_get_crm_products-- Received data: '. $data );
                            $array = json_decode($data);
                            // $firstProduct = $woocommerce->get('products/48');
                            // print_r($firstProduct);
                            // $secontProduct = $woocommerce->get('products/40000');
                            // print_r($secontProduct);


                            foreach ($array->products as $crm_product ) {
                                echo('<b>Current product: </b><br>');
                                print_r ($crm_product);
                                echo('<br>');
                                echo('<b>Current product name: </b><br>');
                                print_r ($crm_product->name);
                                echo('<br>');
                                $currentProductImagesArray     =    formImagesArray     ($crm_product->images);
                                $currentProductCategoriesArray =    formCategoriesArray ($crm_product->categories);
                                $currentProductAttributesArray =    formAttributesArray ($crm_product->attributes);
                                $currentProductObject = array(
                                    'name' => $crm_product->name, 
                                    'type' => $crm_product->type, 
                                    'regular_price' => $crm_product->regular_price, 
                                    'sale_price' => $crm_product->sale_price, 
                                    'description' => $crm_product->description, 
                                    'short_description' => $crm_product->short_description,
                                    'categories' => $currentProductCategoriesArray,
                                    'attributes' =>$currentProductAttributesArray,
                                    'stock_status' => $crm_product->stock_status, 
                                    'sku' => $crm_product->sku, 
                                    'stock_quantity' => $crm_product->stock_quantity, 
                                    'sold_individually' => $crm_product->sold_individually, 
                                    'manage_stock' => $crm_product->manage_stock, 
                                    'backorders' => $crm_product->backorders, 
                                    'purchase_note' => $crm_product->purchase_note, 
                                    'menu_order' => $crm_product->menu_order, 
                                    'reviews_allowed' => $crm_product->reviews_allowed 
                                );
                                if($crm_product->woo_id == NULL) {
                                    echo '$crm_product->woo_id is null<br>';
                                    logger('INFO--products/c_get_crm_products-- There is no woo_id of current product with crm_id='.$crm_product->crm_id.'- then this product is not in the WooCommerce');
                                    logger('INFO--products/c_get_crm_products-- Creating product in the WooCommerce...'); 
                                    $currentProductObject['images'] = $currentProductImagesArray;  
                                    $operation_result = $woocommerce->post('products', (object)$currentProductObject);
                                    echo('product created with name = '.$operation_result->name.' , woo_id = '.$operation_result->id.'<br>');
                                    logger('INFO--products/c_get_crm_products-- product created with name = '.$operation_result->name.' and woo_id = '.$operation_result->id);
                                    array_push($ids_pairs, (object) ['id'=>$operation_result->id, 'crm_id'=>$crm_product->crm_id]);
                                } else {
                                    // If there is woo_id - then MAYBE this product is already in WooCommerce - Update product in WooCommerce
                                    // Maybe - because this product could be removed manually (bad, bad users!)
                                    // And firsteval before update I need to check that the WooCommerce has the product with this id:
                                    logger('INFO--products/c_get_crm_products-- There is woo_id. Maybe this product is already in WooCommerce (if it wasn\'t removed manually)');
                                    // If the product wasn't removed manually, i.e. all_woo_products_ids[] contains woo's id receqved from CRM
                                    if (in_array($crm_product->woo_id, $all_woo_products_ids)) {
                                        logger('INFO--products/c_get_crm_products-- The woo_id of the current product is in the array - updating the current product with crm_id = '.$crm_product->crm_id.', woo_id = '.$crm_product->woo_id.', name = '. $crm_product->name.', slug = '.$crm_product->slug);
                                        
                                        $currentWooProduct = $woocommerce->get('products/'.$crm_product->woo_id); 
                                        if(needToUpdateImages($crm_product->images, $currentWooProduct->images))
                                            $currentProductObject['images'] = $currentProductImagesArray;

                                        // Updating this product
                                        echo('<b>Updating product</b> with crm_id = '.$crm_product->crm_id.', woo_id = '.$crm_product->woo_id.', name = '. $crm_product->name.', slug = '.$crm_product->slug.', description = '.$crm_product->description.', menu_order = '.$crm_product->menu_order.'<br>');
                                        echo('<b>Changed product: </b><br><pre>');
                                        print_r ($woocommerce->put('products/'.$crm_product->woo_id, (object)$currentProductObject));
                                        echo('</pre><br>');
                                        
                                        array_push($ids_pairs, (object) ['id'=>$crm_product->woo_id, 'crm_id'=>$crm_product->crm_id]);
                                    } else { //if the product was removed manually, i.e. all_woo_products_ids[] does not contain woo's id receqved from CRM
                                        logger('INFO--products/c_get_crm_products-- The product with woo_id = '.$crm_product->woo_id.' is not in WooCommerce, it was removed manually.');
                                        logger('INFO--products/c_get_crm_products-- Creating product that was removed manually');
                                        echo('The product with woo_id = '.$crm_product->woo_id.' is not in WooCommerce, it was removed manually.<br>');
                                        echo('Creating product that was removed manually<br>');
                                        $currentProductObject['images'] = $currentProductImagesArray;
                                        $creation_result = $woocommerce->post('products', (object)$currentProductObject);
                                        echo('Created product woo_id = '.$creation_result->id.'<br>');
                                        logger('INFO--products/c_get_crm_products-- Created product with woo_id = '.$creation_result->id.'crm_id = '.$crm_product->crm_id);
                                        array_push($ids_pairs, (object) ['id'=>$creation_result->id, 'crm_id'=>$crm_product->crm_id]);
                                    }
                                }
                            }
                        } else {
                            echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                            logger ('ERROR--products/c_get_crm_products-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!');
                        }
                        $firstResult = $firstResult + $maxResults;
                        $currentCycle++;
                    }

                    // Sending ID's of all created in WooCommerce products to the CRM for IDs synchronization  
                    echo ('<b>ids_pairs:</b><br>');
                    foreach ($ids_pairs as $pair ) {   
                        echo 'woo_id = ' . $pair->id . ', crm_id = ' . $pair->crm_id.'<br>';
                    }
                    if(count($ids_pairs) >0 ){
                        echo '<b>Sending synchronization set of ID\'s to the CRM server:</b><br>';
                        $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","idsSet":'.json_encode($ids_pairs).'}';
                        logger ('INFO--products/c_get_crm_products-- Sending POST request syncProductsIds to the CRM server with data: '.$data_to_sent);
                        $url = 'http://localhost:8080/api/public/woo_v3/syncProductsIds';
                        $curl = curl_init($url);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_to_sent);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        curl_setopt($curl, CURLOPT_TIMEOUT,500); // 500 seconds
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                        $response = curl_exec($curl);
                        curl_close($curl);
                        echo '<pre>The response of syncProductsIds: ';
                        print_r ($response);
                        echo '<br></pre>';
                        switch ($response) {
                            case NULL:
                                logger ('ERROR--products/c_get_crm_products/syncProductsIds: Error of the Sending synchronization set of ID\'s to the CRM server!:Response: '.$response); 
                                break;
                            case '1':
                                logger ('INFO--products/c_get_crm_products/syncProductsIds: Success! Response: '.$response);
                                break;
                            case '-200':
                                logger ('ERROR--products/c_get_crm_products/syncProductsIds: Error of the Sending synchronization set of ID\'s to the CRM server!: WrongCrmSecretKeyException. Response: '.$response);
                                break;
                        }    
                    }
                } else {
                    echo '<b>Total products from DokioCRM to be synchronized is 0</b><br>';
                    logger ('WARN--products/c_get_crm_products-- Total products from DokioCRM to be synchronized is 0. DokioCRM hasn\'t non-deleted products');
                }
            } else {
                echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                logger ('ERROR--products/c_get_crm_products-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!');
            }

            // Querying woo_ID's of products that need to be deleted on the store side
            // It can be 1) Deleted on the CRM side products 2) Products than no more belong to store categories
            $url = 'http://localhost:8080/api/public/woo_v3/getProductWooIdsToDeleteInStore?key='.get_option( 'secret_key' );
            echo $url;
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            curl_setopt($request, CURLOPT_TIMEOUT,500); // 500 seconds
            curl_setopt($request, CURLOPT_FOLLOWLOCATION, false);
            echo('<br>Getting the list of products woo_ID\'s from CRM, that need to be deleted on the store side<br>');
            logger('INFO--products/c_get_crm_products-- Getting the list of products woo_ID\'s from CRM, that need to be deleted on the store side');
            $data = curl_exec($request);
            $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);

            if($httpcode==200){
                echo('<br><b>Received data: </b><br>'. $data . '<br>');
                logger('INFO--products/c_get_crm_products-- Received data: '. $data );
                $array = json_decode($data);
                if(count($array) >0 ){
                    foreach ($array as $crm_woo_id ) {
                        echo('<b>Current crm_woo_id: </b>'.$crm_woo_id.'<br>');
                        if (in_array($crm_woo_id, $all_woo_products_ids)) {
                            echo('<br>Trying to delete the product with woo_id = '.$crm_woo_id . '<br>');
                            logger('INFO--products/c_get_crm_products-- Trying to delete the product with woo_id = '.$crm_woo_id);
                            $woocommerce->delete('products/'.$crm_woo_id, ['force' => true]);
                        }
                    }
                    //Clearing woo_ID's on the server side by sending the set of deleted woo_ID's to the CRM server
                    echo '<b>Clearing woo_ID\'s on the server side by sending the set of deleted woo_ID\'s to the CRM server</b><br>';
                    $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","idsSet":'.json_encode($array).'}';
                    logger ('INFO--products/c_get_crm_products-- Clearing woo_ID\'s on the server side by sending the POST request with the set of deleted woo_ID\'s to the CRM server with data: '.$data_to_sent);
                    $url = 'http://localhost:8080/api/public/woo_v3/deleteWooIdsFromProducts';
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_TIMEOUT,500); // 500 seconds
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_to_sent);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    echo '<pre>The response of deleteWooIdsFromProducts: ';
                    print_r ($response);
                    echo '<br></pre>';
                    switch ($response) {
                        case NULL:
                            logger ('ERROR--products/c_get_crm_products/deleteWooIdsFromProducts: Error of the deleting woo_ids on the server side!: Response: '.$response);
                            break;
                        case '1':
                            logger ('INFO--products/c_get_crm_products/deleteWooIdsFromProducts: Success! Response: '.$response);
                            break;
                        case '-200':
                            logger ('ERROR--products/c_get_crm_products/deleteWooIdsFromProducts: Error of the deleting woo_ids on the server side!: WrongCrmSecretKeyException. Response: '.$response);
                            break;
                    }                     
                } else {
                    echo('<br>Nothing to delete on the store side.<br>');
                    logger('INFO--products/c_get_crm_products-- Nothing to delete on the store side');
                }
            } else {
                echo '<b>Server error of request getProductWooIdsToDeleteInStore with response code = '.$httpcode.' Synchronization failed!</b><br>';
                logger ('ERROR--products/c_get_crm_products-- Server error of request getProductWooIdsToDeleteInStore with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!');
            }

        } catch (HttpClientException $e) {
            echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
            echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
            echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
            logger ('ERROR--products/c_get_crm_products-- HttpClientException.');
            logger ('The Message: '.print_r($e->getMessage(), true));
            logger ('The Request: '.print_r($e->getRequest(), true));
            logger ('The Response: '.print_r($e->getResponse(), true));
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--products/c_get_crm_products-- The response: '.$e->getMessage());
        }
    }
    
    function formImagesArray($arr){      
        $imageArray=[];
        echo 'Images Array: \n';
        Print_r($arr);
        foreach ($arr as $e ) {
            array_push($imageArray,[
                'src'   => $e->img_address,
                'name'  => $e->img_original_name,
                'alt'   => $e->img_alt                   
            ]);
        }
        return $imageArray;
    }
    function formCategoriesArray($arr){      
        $categoriesArray=[];
        echo 'categories Array: \n';
        Print_r($arr);
        foreach ($arr as $e ) {
            array_push($categoriesArray,[
                'id'   => $e
            ]);
        }
        return $categoriesArray;
    }
    function formAttributesArray($arr){      
        $attributesArray=[];
        echo 'Attributes Array: \n';
        Print_r($arr);
        foreach ($arr as $e ) {
            array_push($attributesArray,[
                'id' => $e->woo_id,
                'position' => $e->position,
                'visible' => $e->visible,
                'variation' => $e->variation,
                'options' =>     $e->options
            ]);
        }
        return $attributesArray;
    }
    function needToUpdateImages($dokioImages, $wooImages){
        $need = false;

        echo("<br><b>All Dokio Images:</b>");
        Print_r($dokioImages);
        echo("<br><b>All Woo Images:</b>");
        Print_r($wooImages);
        
        if(sizeof($dokioImages) != sizeof($wooImages))
           return true;
        else { //arrays can be the same by size, but order of images can be changed
            for ($i = 0; $i < sizeof($dokioImages); $i++) {
                echo("<br><b>Current Dokio image NAME: </b>" .$dokioImages[$i]->img_original_name."<br>");
                echo("<br><b>Current Woo image NAME: </b>" . $wooImages[$i]->name."<br>");
                if($dokioImages[$i]->img_original_name != $wooImages[$i]->name)
                    $need = true;
            }
        }
        

        echo("<br><b>Need to add Images: ");
        echo($need ? 'true' : 'false');
        echo("</b><br>");
        return $need;
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


