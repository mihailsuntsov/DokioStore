<?php
    ini_set('memory_limit', '1024M'); // or you could use 1G
    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

    add_action( 'admin_post_c_get_crm_categories', 'c_get_crm_categories' );

    function c_get_crm_categories() {
        logger('--- Categories auto sync ---');
        try {
            $woocommerce = new Client(get_option('woo_address'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
            $url = 'http://localhost:8080/api/public/woo_v3/syncProductCategoriesToStore?key='.get_option( 'secret_key' );
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            logger('INFO--categories/c_get_crm_categories-- Getting the list of categories from CRM');
            $data = curl_exec($request);
            $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);
            echo('httpcode - '.$httpcode);

            if($httpcode==200){

                $array = json_decode($data);
                $ids_pairs_to_post_crm = array(); //the array of objects that contains: woo_id and crm_id that will be sent to CRM for update woo_id's
                $ids_pairs_to_synchronize = array(); //the array of objects that contains: woo_id and crm_id that needs to synchronize categories in order "from parents to childs"
                $all_woo_ids = array(); // array of all woo category ID's
                $all_crm_ids = array(); // array of all crm category ID's
                $all_crm_woo_ids = array(); // array of all Woo ID's in CRM
                $all_woo_ids_and_images = array(); // the array of objects that contains: category woo_id and the name of image. It needs to prevent of creating image duplicates
                $per_page=100;
                // echo('<b>Received data: </b><br>'. $data . '<br>');
                logger('INFO--categories/c_get_crm_categories-- Received data: '. $data );
                // echo $array->productCategories[0]->crm_id;   

                // I can't handle or insert new categories in order of download, because this is hierarchy structure,
                // and in some cases child category can be created before its parent.
                // So, I need to create categories in order "from parents to childs"

                // Counting the number of downloaded crm categories
                $synchronized_crm_ids = array();//the array of synchronized crm categories ID's
                
                



                //Getting all WooCommerce categories to get the array of all their IDs
                logger('INFO--categories/c_get_crm_categories-- Getting all WooCommerce categories to get the array of all their IDs');
                $all_woo_categories = $woocommerce->get('products/categories', ['per_page' => $per_page]);
                if(count($all_woo_categories)==$per_page){// 100 per page is max number in WooCommerce, but may be this is not all categories
                    echo('this is not all categories<br>');
                    $page=2;
                    $do_cycle=true;
                    while ($do_cycle) {
                        echo("page = " . $page ."<br>");
                        $current_cycle_categories = $woocommerce->get('products/categories', ['per_page' => $per_page, 'page' => $page]);
                        if(count($current_cycle_categories) > 0){
                            foreach ($current_cycle_categories as $ccc ) {array_push($all_woo_categories, $ccc);}
                        } else $do_cycle = false;
                        if(count($current_cycle_categories) < $per_page) $do_cycle = false;
                        $page++;
                    }
                }
                echo '<b>All woo_categories:</b><br><pre>';print_r($all_woo_categories);echo '</pre>';
                
                // get all woo categories ID's
                foreach ($all_woo_categories as $woo_category ) {
                    array_push($all_woo_ids, $woo_category->id);
                    if($woo_category->image != NULL)
                        array_push($all_woo_ids_and_images, (object) ['id'=>$woo_category->id, 'name'=>$woo_category->image->name]);
                }
                echo('<b>All images in woo: </b><br>');
                print_r ($all_woo_ids_and_images);

                // get all crm categories ID's and all Woo ID's in CRM
                foreach ($array->productCategories as $crm_category ) {
                    array_push($all_crm_ids, $crm_category->crm_id); 
                    array_push($all_crm_woo_ids, $crm_category->woo_id); 
                    
                }
                echo('Total Woo categories = '.count($all_woo_ids).'<br>');
                echo('Total CRM categories = '.count($all_crm_ids).'<br>');

                $uncategorized_id = get_option( 'default_product_cat' );

                echo('<b>$all_woo_ids: </b><br>');
                print_r ($all_woo_ids);
                echo('<br>');
                echo('<b>$all_crm_woo_ids: </b><br>');
                print_r ($all_crm_woo_ids);
                echo('<br>');
                // Deleting categories that was deleted in CRM
                foreach ($all_woo_ids as $curr_woo_id ) {
                    if (!in_array($curr_woo_id, $all_crm_woo_ids) && $curr_woo_id != $uncategorized_id){
                        echo('<br>Trying to delete category with woo_id = '.$curr_woo_id.'<br>');
                        print_r($woocommerce->delete('products/categories/'.$curr_woo_id, ['force' => true]));
                    }
                }




                $cycle_counter=0; // needs for analyze of working in logs
                while (count($synchronized_crm_ids) < count($all_crm_ids)) {
                $cycle_counter++;
                logger('INFO--categories/c_get_crm_categories-- Start cycle #'.$cycle_counter);    
                    foreach ($array->productCategories as $category ) 
                    { 
                        echo('<b>Current category: </b><br>');
                        print_r ($category);
                        echo('<br>');
                        echo('<b>Current category name: </b>');
                        print_r ($category->name);
                        echo('<br>');
                        echo('<b>Current category crm_id: </b>');
                        print_r ($category->crm_id);
                        echo('<br>');
                        echo('<b>synchronized_crm_ids:</b> ');
                        print_r ($synchronized_crm_ids);
                        echo('<br>');
                        //if (this is a root category, or its parent category ID is in $synchronized_crm_ids array) and category ID not in ID's of synchronized categories
                        if(($category->parent_crm_id == 0 || in_array($category->parent_crm_id, $synchronized_crm_ids)) && !in_array($category->crm_id, $synchronized_crm_ids))
                        {
                            $woo_data = [
                                'name' => $category->name,
                                'description' => $category->description,
                                'slug' => $category->slug,
                                'display' => $category->display,
                                'menu_order' => $category->menu_order,
                                'parent' => ($category->parent_crm_id == 0)?NULL:getWooIdByCrmId($ids_pairs_to_synchronize,$category->parent_crm_id),
                                'image' => [
                                    'src'   => ($category->img_address == NULL)?'':$category->img_address,
                                    'name'  => ($category->img_address == NULL)?'':$category->img_original_name,
                                    'alt'   => ($category->img_address == NULL)?'':$category->img_alt                   
                                ]
                            ];
                            if($category->img_address != NULL){
                            echo("woo_data - name - ". $woo_data['name'] . "<br>");
                            echo ("woo_data - image - name". $woo_data['image']['name'] . "<br>");  
                            }
                            
                            // if there is no woo_id of WooCommerce - then this category is not in WooCommerce - Create category in WooCommerce
                            if($category->woo_id == NULL) {
                                echo '$category->woo_id is null<br>';
                                logger('INFO--categories/c_get_crm_categories-- There is no woo_id of current category with crm_id='.$category->crm_id.'- then this category is not in WooCommerce');
                                logger('INFO--categories/c_get_crm_categories-- Creating category in WooCommerce...');    
                                
                                // echo '<pre>';
                                // print_r($woocommerce->post('products/categories', $woo_data));
                                // echo '</pre>';
                                $operation_result = $woocommerce->post('products/categories', $woo_data);
                                echo '<b>Operation_result:</b><br><pre>';print_r($operation_result);echo '</pre>';
                                echo('category created with name = '.$operation_result->name.' , woo_id = '.$operation_result->id.'<br>');
                                logger('INFO--categories/c_get_crm_categories-- category created with name = '.$operation_result->name.' and woo_id = '.$operation_result->id);
                                array_push($ids_pairs_to_post_crm, (object) ['id'=>$operation_result->id, 'crm_id'=>$category->crm_id]);
                                array_push($ids_pairs_to_synchronize, (object) ['id'=>$operation_result->id, 'crm_id'=>$category->crm_id]);
                                array_push($synchronized_crm_ids, $category->crm_id);
                            } else { 
                            // If there is woo_id - then MAYBE this category is already in WooCommerce - Update category in WooCommerce
                            // Maybe - because this category could be removed manually
                            // And firsteval before update I need to check that WooCommerce has the category with this woo_id:
                                logger('INFO--categories/c_get_crm_categories-- There is woo_id. Maybe this category is already in WooCommerce (if it wasn\'t removed manually)');
                                // If the category wasn't removed manually, i.e. all_woo_ids[] contains woo's woo_id receqved from CRM
                                echo '<b>$category->woo_id: </b>'.$category->woo_id.', <b>all_woo_ids: </b><pre>';print_r($all_woo_ids);echo '</pre>';
                                if (in_array($category->woo_id, $all_woo_ids)) {
                                    logger('INFO--categories/c_get_crm_categories-- The woo_id of the current category is in the array - updating the current category with crm_id = '.$category->crm_id.', woo_id = '.$category->woo_id.', name = '. $category->name.', rate = '.$category->rate);
                                    // Updating this category
                                    echo('<b>Updating category</b> with crm_id = ' . $category->crm_id . ', woo_id = ' . $category->woo_id . ', name = ' . $category->name . '<br>');
                                    if(isPictureInWoo($all_woo_ids_and_images,$category->woo_id, $woo_data['image']['name'])){
                                        unset($woo_data['image']);
                                    }
                                    $operation_result = $woocommerce->put('products/categories/'.$category->woo_id, $woo_data);
                                    array_push($ids_pairs_to_synchronize, (object) ['id'=>$operation_result->id, 'crm_id'=>$category->crm_id]);
                                } else { // If the category was removed manually, i.e. all_woo_ids[] does not contain woo's woo_id received from CRM
                                    logger('INFO--categories/c_get_crm_categories-- The category with woo_id = '.$category->woo_id.' is not in WooCommerce, it was removed manually.');
                                    logger('INFO--categories/c_get_crm_categories-- Creating category that was removed manually');
                                    echo('The category with woo_id = '.$category->woo_id.' is not in WooCommerce, it was removed manually.<br>');
                                    echo('Creating category that was removed manually<br>');
                                    $creation_result = $woocommerce->post('products/categories', $woo_data);
                                    echo('Created category woo_id = '.$creation_result->id.'<br>');
                                    logger('INFO--categories/c_get_crm_categories-- Created category with woo_id = '.$creation_result->id.'crm_id = '.$category->crm_id);
                                    array_push($ids_pairs_to_post_crm, (object) ['id'=>$creation_result->id, 'crm_id'=>$category->crm_id]);
                                    array_push($ids_pairs_to_synchronize, (object) ['id'=>$creation_result->id, 'crm_id'=>$category->crm_id]);
                                }
                                array_push($synchronized_crm_ids, $category->crm_id);
                            }
                        }
                    } 
                } //while
                
                    
                //   echo ('<b>all_woo_ids:</b><br>');
                //   foreach ($all_woo_ids as $woo_id ) {   
                //       echo 'woo_id = ' . $woo_id.'<br>';
                //   }
                // Sending woo_ID's of all created in WooCommerce categories to the CRM for IDs synchronization
                if(count($ids_pairs_to_post_crm) >0 ){
                    echo ('<b>ids_pairs_to_post_crm:</b><br>');
                    foreach ($ids_pairs_to_post_crm as $pair ) {   
                        echo 'woo_id = ' . $pair->id . ', crm_id = ' . $pair->crm_id.'<br>';
                    }

                    echo '<b>Sending POST request to the CRM server:</b><br>';
                    $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","idsSet":'.json_encode($ids_pairs_to_post_crm).'}';
                    logger ('INFO--categories/c_get_crm_categories-- Sending POST request syncProductCategoriesIds to the CRM server with data: '.$data_to_sent);
                    $url = 'http://localhost:8080/api/public/woo_v3/syncProductCategoriesIds';
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_to_sent);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);
                    echo('httpcode - '.$httpcode);
                    echo '<pre>1-<br>';
                    print_r ($response);
                    echo '<br>-1</pre>'; 

                    if($httpcode==200){
                        switch ($response) {
                            case '1':
                                echo "Fine!";
                                logger ('INFO--categories/c_get_crm_categories-- The response: '.$response);
                                break;
                            case '-200':
                                echo "WrongCrmSecretKeyException!";
                                logger ('ERROR--categories/c_get_crm_categories-- Server error with WrongCrmSecretKeyException. Synchronization failed!');
                                break;
                            case NULL:
                                echo "Server Exception";
                                logger ('ERROR--categories/c_get_crm_categories-- Server error with Exception. See server logs for details. Synchronization failed!');
                                break;
                        }
                    } else {
                        echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                        logger ('ERROR--categories/c_get_crm_categories-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!');
                    }                    
                } else {
                    echo '<b>Nothing to send to the CRM server. Synchronization finished!</b><br>';
                    logger ('INFO--categories/c_get_crm_categories-- Nothing to send to the CRM server. Synchronization finished!');
                }
            } else {
                echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                logger ('ERROR--categories/c_get_crm_categories-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!');
            }

        }  catch (HttpClientException $e) {
            echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
            echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
            echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
            logger ('ERROR--categories/c_get_crm_categories-- HttpClientException.');
            logger ('The Message: '.print_r($e->getMessage(), true));
            logger ('The Request: '.print_r($e->getRequest(), true));
            logger ('The Response: '.print_r($e->getResponse(), true));
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--categories/c_get_crm_categories-- The response: '.$e->getMessage());
        }
    }
    
    function getWooIdByCrmId($arr,$crm_id){
        $result=NULL;
        foreach ($arr as $e ) {if($crm_id==$e->crm_id) $result=$e->id;}
        return $result;
    }


    function isPictureInWoo($arr, $woo_id, $name){
        // echo("Search image... <br>");
        foreach ($arr as $e ) {
            // echo("e->id = ".$e->id.", woo_id = ".$woo_id.", e->name = ".$e->name.", name = ". $name . "<br>");
            if($e->id == $woo_id && $e->name == $name) return true;
        }
        return false;
    }