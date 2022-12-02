<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

    add_action( 'admin_post_c_get_crm_terms', 'c_get_crm_terms' );
    function c_get_crm_terms() {
        logger('--- Attributes auto sync ---');
        try {
            status_header(200);
            $woocommerce = new Client(get_option('woo_address'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
            $url = 'http://localhost:8080/api/public/woo_v3/syncAttributeTermsToStore?key='.get_option( 'secret_key' );
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            logger('INFO--terms/c_get_crm_terms-- Getting the list of terms from CRM');
            $data = curl_exec($request);
            $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);

            if($httpcode==200){

                echo('<b>Received data: </b><br>'. $data . '<br>');
                logger('INFO--terms/c_get_crm_terms-- Received data: '. $data );
                $array = json_decode($data);
                $ids_pairs = array(); //the array of pairs woo_id and crm_id that will be sent to CRM for update woo_id's
                // ID's on a Woo side
                $all_woo_terms_ids = array(); // array of all woo terms ID's
                $all_woo_attributes_ids = array(); // array of all woo attributes ID's
                $all_woo_attribute_term_pairs_ids = array(); //the array of pairs of terms id and their parent attribute id
                // ID's on a CRM side
                // $all_crm_crm_ids = array(); // array of all crm terms ID's
                $all_crm_woo_ids = array(); // array of all Woo ID's in CRM
                $all_crm_crm_attributes_ids = array(); // array of all crm attributes ID's in CRM
                // $all_crm_woo_attributes_ids = array(); // array of all Woo attributes ID's in CRM
                $per_page=100;
                
                // get all crm terms ID's and all Woo ID's in CRM
                foreach ($array->attributeTerms as $crm_term ) {
                    // array_push($all_crm_crm_ids, $crm_term->crm_id); 
                    array_push($all_crm_woo_ids, $crm_term->woo_id); 
                    // array_push($all_crm_crm_attributes_ids, $crm_term->attribute_crm_id); 
                    // array_push($all_crm_woo_attributes_ids, $crm_term->attribute_woo_id); 
                    
                }

                //Getting all WooCommerce ATTRIBUTES to get the array of all their IDs
                logger('INFO--terms/c_get_crm_terms-- Getting all WooCommerce attributes to get the array of all their IDs');
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
                    array_push($all_woo_attributes_ids, $woo_attribute->id);
                }               
                //Getting all WooCommerce TERMS to get the array of all their IDs
                logger('INFO--terms/c_get_crm_terms-- Getting all WooCommerce terms to get the array of all their IDs');

                foreach ($all_woo_attributes_ids as $attribute_id ) {
                    echo("Querying terms for attribute with id = " . $attribute_id ."<br>");
                    $current_attribute_terms = $woocommerce->get('products/attributes/'.$attribute_id.'/terms');
                        if(count($current_attribute_terms) > 0){
                            foreach ($current_attribute_terms as $ccc ) {
                                array_push($all_woo_terms_ids, $ccc->id);
                                array_push($all_woo_attribute_term_pairs_ids, (object) ['attribute_id'=>$attribute_id, 'term_id'=>$ccc->id]);
                            }
                        }
                }
                echo '<b>All woo_terms IDs : [</b><br><pre>';print_r($all_woo_terms_ids);echo '<br><b>]</b></pre>';
                logger('INFO--terms/c_get_crm_terms-- Starting the cycle on all CRM terms...');
                
                // Deleting terms that was deleted in CRM
                foreach ($all_woo_attribute_term_pairs_ids as $curr_pair ) {
                    if (!in_array($curr_pair->term_id, $all_crm_woo_ids)){
                        echo('<br>Trying to delete term with woo_id = '.$curr_pair->term_id.'<br>');
                        logger('INFO--terms/c_get_crm_terms-- Trying to delete term with woo_id = '.$curr_pair->term_id);
                        print_r($woocommerce->delete('products/attributes/'.$curr_pair->attribute_id.'/terms/'.$curr_pair->term_id, ['force' => true]));
                    }
                }

                foreach ($array->attributeTerms as $crm_term ) {
                    echo('<b>Current term: </b><br>');
                    print_r ($crm_term);
                    echo('<br>');
                    echo('<b>Current term name: </b><br>');
                    print_r ($crm_term->name);
                    echo('<br>');

                    // All actions of creating or updating terms are matter only if term's parent attribute there is in WooCommerce 
                    logger('INFO--terms/c_get_crm_terms-- Checking that current term\'s attribute there is in WooCommerce...');
                    if (in_array($crm_term->attribute_woo_id, $all_woo_attributes_ids)) {
                        logger('INFO--terms/c_get_crm_terms-- The woo_id of parent attribute of current term is in the array $all_woo_attributes_ids');
                        echo('The woo_id of parent attribute of current term is in the array $all_woo_attributes_ids<br>');
                        // if there is no term's id of WooCommerce - then this term is not in the WooCommerce - Create term in the WooCommerce
                        if($crm_term->woo_id == NULL) {
                            echo '$crm_term->woo_id is null<br>';
                            logger('INFO--terms/c_get_crm_terms-- There is no woo_id of current term with crm_id='.$crm_term->crm_id.'- then this term is not in the WooCommerce');
                            logger('INFO--terms/c_get_crm_terms-- Creating term in the WooCommerce...');   
                            $operation_result = $woocommerce->post('products/attributes/'.$crm_term->attribute_woo_id.'/terms', (object) ['name' => $crm_term->name, 'slug' => $crm_term->slug, 'description' => $crm_term->description, 'menu_order' => $crm_term->menu_order]);
                            echo('Term created with name = '.$operation_result->name.' , woo_id = '.$operation_result->id.'<br>');
                            logger('INFO--terms/c_get_crm_terms-- Term created with name = '.$operation_result->name.' and woo_id = '.$operation_result->id);
                            array_push($ids_pairs, (object) ['id'=>$operation_result->id, 'crm_id'=>$crm_term->crm_id]);
                        } else { 
                            // If there is woo_id - then MAYBE this term is already in WooCommerce - Update term in WooCommerce
                            // Maybe - because this term could be removed manually (bad, bad users!)
                            // And firsteval before update I need to check that the WooCommerce has the term with this id:
                            logger('INFO--terms/c_get_crm_terms-- There is woo_id. Maybe this term is already in WooCommerce (if it wasn\'t removed manually)');
                            // If the term wasn't removed manually, i.e. all_woo_terms_ids[] contains woo's id receqved from CRM
                            if (in_array($crm_term->woo_id, $all_woo_terms_ids)) {
                                logger('INFO--terms/c_get_crm_terms-- The woo_id of the current term is in the array - updating the current term with crm_id = '.$crm_term->crm_id.', woo_id = '.$crm_term->woo_id.', name = '. $crm_term->name.', slug = '.$crm_term->slug);
                                // Updating this term
                                echo('<b>Updating Term</b> with crm_id = '.$crm_term->crm_id.', woo_id = '.$crm_term->woo_id.', name = '. $crm_term->name.', slug = '.$crm_term->slug.', description = '.$crm_term->description.', menu_order = '.$crm_term->menu_order.'<br>');
                                $woocommerce->put('products/attributes/'.$crm_term->attribute_woo_id.'/terms'.'/'.$crm_term->woo_id, (object) ['name' => $crm_term->name, 'slug' => $crm_term->slug, 'description' => $crm_term->description, 'menu_order' => $crm_term->menu_order]);
                            } else { //if the term was removed manually, i.e. all_woo_terms_ids[] does not contain woo's id receqved from CRM
                                logger('INFO--terms/c_get_crm_terms-- The term with woo_id = '.$crm_term->woo_id.' is not in WooCommerce, it was removed manually.');
                                logger('INFO--terms/c_get_crm_terms-- Creating term that was removed manually');
                                echo('The term with woo_id = '.$crm_term->woo_id.' is not in WooCommerce, it was removed manually.<br>');
                                echo('Creating term that was removed manually<br>');
                                $creation_result = $woocommerce->post('products/attributes/'.$crm_term->attribute_woo_id.'/terms', (object) ['name' => $crm_term->name, 'slug' => $crm_term->slug, 'description' => $crm_term->description, 'menu_order' => $crm_term->menu_order]);
                                echo('Created term woo_id = '.$creation_result->id.'<br>');
                                logger('INFO--terms/c_get_crm_terms-- Created term with woo_id = '.$creation_result->id.'crm_id = '.$crm_term->crm_id);
                                array_push($ids_pairs, (object) ['id'=>$creation_result->id, 'crm_id'=>$crm_term->crm_id]);
                            }
                        }
                    } else {
                        //elsewhere, this attribute will be created on a next cycle of the synchronization, and its terms also will be synchronized
                        logger('WARN--terms/c_get_crm_terms-- The woo_id of parent attribute of current term is NOT in the array $all_woo_attributes_ids');
                        logger('WARN--terms/c_get_crm_terms-- This attribute will be created on a next cycle of the synchronization, and its terms also will be synchronized');
                        echo('The woo_id of parent attribute of current term is NOT in the array $all_woo_attributes_ids<br>');
                    }
                }
                echo ('<b>ids_pairs:</b><br>');
                foreach ($ids_pairs as $pair ) {   
                    echo 'id = ' . $pair->id . ', crm_id = ' . $pair->crm_id.'<br>';
                }
                // Sending ID's of all created in WooCommerce terms to the CRM for IDs synchronization
                if(count($ids_pairs) >0 ){
                    echo '<b>Sending POST request to the CRM server:</b><br>';
                    $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","idsSet":'.json_encode($ids_pairs).'}';
                    logger ('INFO--terms/c_get_crm_terms-- Sending POST request syncAttributeTermsIds to the CRM server with data: '.$data_to_sent);
                    $url = 'http://localhost:8080/api/public/woo_v3/syncAttributeTermsIds';
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
                    logger ('INFO--terms/c_get_crm_terms-- The response: '.$response);
                }
            } else {
                echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                logger ('ERROR--terms/c_get_crm_terms-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!');
            }

        }  catch (HttpClientException $e) {
            echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
            echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
            echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
            logger ('ERROR--terms/c_get_crm_terms-- HttpClientException.');
            logger ('The Message: '.print_r($e->getMessage(), true));
            logger ('The Request: '.print_r($e->getRequest(), true));
            logger ('The Response: '.print_r($e->getResponse(), true));
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--terms/c_get_crm_terms-- The response: '.$e->getMessage());
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


