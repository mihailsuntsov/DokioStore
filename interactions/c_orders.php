<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

    add_action( 'admin_post_c_get_crm_orders', 'c_get_crm_orders' );
    function c_get_crm_orders() {
        logger('--- Orders auto sync ---');
        try {
            status_header(200);
            $woocommerce = new Client(get_option('siteurl'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3','timeout' => 240]);
            
            $url = get_option( 'API_address' ).'/getLastSynchronizedOrderTime?key='.get_option( 'secret_key' );
            echo $url;
            $request = curl_init($url); 
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HEADER, 0);
            curl_setopt($request, CURLOPT_TIMEOUT,500); // 500 seconds
            curl_setopt($request, CURLOPT_FOLLOWLOCATION, false);
            logger('INFO--orders/c_get_crm_orders-- Getting the last order date from CRM');
            $last_sync_date = curl_exec($request);
            echo('<br><b>Received data: </b><br>'. $last_sync_date . '<br>');
            logger('INFO--orders/c_get_crm_orders/getLastSynchronizedOrderTime-- Received data: '. $last_sync_date );
            $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);
            if($httpcode==200 or $last_sync_date = NULL or $last_sync_date=''){
                echo('<b>The last date of synchronized order is: </b>'. $last_sync_date . '<br>');
                logger('INFO--orders/c_get_crm_orders/getLastSynchronizedOrderTime-- The last date of synchronized order is: ' . $last_sync_date);

                $orders = ($woocommerce->get('orders', ['after' => $last_sync_date, 'dates_are_gmt' => true]));

                echo('<b>New non-synchronised orders: </b><br><pre>');
                print_r ($orders);
                echo('</pre><br>');

                if(count($orders)>0){
                    $data_to_sent = '{"crmSecretKey":"'.get_option( 'secret_key' ).'","orders":'.json_encode($orders).'}';
                    logger ('INFO--products/c_get_crm_products-- Sending '.count($orders).' Woo orders to the server side by the POST request putOrdersIntoCRM.');
                    $url = get_option( 'API_address' ).'/putOrdersIntoCRM';
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_TIMEOUT,500); // 500 seconds
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_to_sent);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    echo '<pre>The response of putOrdersIntoCRM: ';
                    print_r ($response);
                    echo '<br></pre>';
                    switch ($response) {
                        case NULL:
                            logger ('ERROR--products/c_get_crm_products/putOrdersIntoCRM: Error of the creating orders on the server side!: Response: '.$response);
                            break;
                        case '1':
                            logger ('INFO--products/c_get_crm_products/putOrdersIntoCRM: Success! Response: '.$response);
                            break;
                        case '-200':
                            logger ('ERROR--products/c_get_crm_products/putOrdersIntoCRM: Error of the creating orders on the server side!: WrongCrmSecretKeyException. Response: '.$response);
                            break;
                        case '-220':
                            logger ('ERROR--products/c_get_crm_products/putOrdersIntoCRM: Error of the creating orders on the server side!: DepartmentIsNotSetException. Response: '.$response);
                            break;
                        case '-222':
                            logger ('ERROR--products/c_get_crm_products/putOrdersIntoCRM: Error of the creating orders on the server side!: DefaultCustomerIsNotSetException. Response: '.$response);
                            break;
                    }     
                }

            } else {
                echo '<b>Server error with response code = '.$httpcode.' Synchronization failed!</b><br>';
                logger ('ERROR--orders/c_get_crm_orders/getLastSynchronizedOrderTime-- Server error with response code = '.$httpcode.', Response = '.$response.' Synchronization failed!, Received data = '.$last_sync_date);
            }            
        } catch (HttpClientException $e) {
            echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
            echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
            echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
            logger ('ERROR--orders/c_get_crm_orders-- HttpClientException.');
            logger ('The Message: '.print_r($e->getMessage(), true));
            logger ('The Request: '.print_r($e->getRequest(), true));
            logger ('The Response: '.print_r($e->getResponse(), true));
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--orders/c_get_crm_orders-- The response: '.$e->getMessage());
            update_option( 'is_sync_task_executed', 'false', 'yes' );
        }
    }