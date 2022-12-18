<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://dokio.me/
 * @since      1.0.0
 *
 * @package    Dokio_Store
 * @subpackage Dokio_Store/admin/partials
 */

 $curr_page = $_GET['page'];
 $curr_url = $_SERVER['REQUEST_URI'];
//  echo  $curr_url;
//  include_once( 'tutsplus-actions.php' );
//  tutsplus_action();
?>

<h1>General Settings for DokioStore</h1><br>
<hr>
<div class="container" style="max-width:100%;">
    <div class="row">
        <div class="col">
            <div class="alert alert-warning">
                <h1 class="display-4">DokioCRM API Importer</h1>
                <p class="lead">Use this section to save your API address and shop ID.</p>
                <hr class="my-4">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'ds_custom_settitgs' );
                    do_settings_sections( 'ds_custom_settitgs' )
                    ?>
                    <div class="form-group">
                        <label for="API_address">DokioCRM API address</label>
                        <input name="API_address" value="<?php echo get_option( 'API_address' ); ?>" type="text" class="form-control" id="API_address" placeholder="DokioCRM API address">
                    </div>
                    <div class="form-group">
                        <label for="secret_key">DokioCRM Secret key</label>
                        <input type="text" name="secret_key" value="<?php echo get_option( 'secret_key' ); ?>" class="form-control" id="secret_key" placeholder="DokioCRM Secret key">
                    </div>
                    <div class="form-group">
                        <label for="woo_address">Woocommerce store site address</label>
                        <input type="text" name="woo_address" value="<?php echo get_option( 'woo_address' ); ?>" class="form-control" id="woo_address" placeholder="Store address">
                    </div>
                    <div class="form-group">
                        <label for="woo_consumer_key">Woocommerce consumer key</label>
                        <input type="text" name="woo_consumer_key" value="<?php echo get_option( 'woo_consumer_key' ); ?>" class="form-control" id="woo_consumer_key" placeholder="Consumer key">
                    </div>
                    <div class="form-group">
                        <label for="woo_consumer_secret">Woocommerce consumer secret</label>
                        <input type="text" name="woo_consumer_secret" value="<?php echo get_option( 'woo_consumer_secret' ); ?>" class="form-control" id="woo_consumer_secret" placeholder="Consumer secret">
                    </div>
                    <!-- <div class="form-check form-switch">
                    <input  class="form-check-input" 
                            type="checkbox" 
                            name="save_crm_taxes" 
                            id="save_crm_taxes"  
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Do not remove DokioCRM tax rates that are not related to this online store"
                            style="margin-top: 0.3rem; margin-left: -1.25rem;" 
                            <?php echo (get_option( 'save_crm_taxes' )=='on'?"checked":""); ?>
                            >
                        <label  class="form-check-label" 
                                for="save_crm_taxes" 
                                data-toggle="tooltip" 
                                data-placement="top" 
                                title="Do not remove DokioCRM tax rates that are not related to this online store">Save DokioCRM tax rates</label>
                    </div> -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
        <div class="col">
            <div class="alert alert-warning">
                <!-- <div class="alert alert-warning">


                <button type="button" class="btn btn-success" id="sync_taxes_from_store">Sync taxes from store to DokioCRM</button><br><br>
                <button type="button" class="btn btn-success" id="sync_taxes_to_store">Sync taxes from DokioCRM to store</button>

                </div>
                <div class="progress">
                    <div id="progress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"><span id="precent_text"></span></div>
                </div> -->
                

                <table class="table table-bordered" style="margin-top: 30px; border-radius: 4px; border-collapse: separate; background-color: white; border: 1px solid #8c8f94;" >
                    <thead>
                        <tr>
                        <h1 class="display-4">Synchronization tasks</h1>
                        <p class="lead">In this section you can control synchronization tasks</p>
                            <!-- <th colspan=3 style = "text-align: center;"><h3>Synchronization tasks</h3></th> -->
                        </tr>
                    </thead>
                    <tbody style = "font-size: 24px;">
                        <!-- <tr>
                            <td class="col-1" style = "text-align: center;">
                                <div style="width: 24px;
                                    height: 24px;
                                    margin: 12px auto;
                                    border-radius: 12px;
                                    background: 
                                    <?php echo(task_works('dokiocrm_taxes_cronjob')?'green':'red');?>;">
                                </div>
                            </td>
                            <td><span style = "line-height: 47px;">Taxes</span></td>
                            <td class="col-2" style = "text-align: center;">
                                <form 
                                style = "display:<?php echo(task_works('dokiocrm_taxes_cronjob')?'none':'block');?>"
                                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_on_cron_taxes">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-light">Start</button>
                                </form>
                                <form 
                                    style = "display:<?php echo(task_works('dokiocrm_taxes_cronjob')?'block':'none');?>"
                                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_off_cron_taxes">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-light">Stop</button>
                                </form>    
                            </td>
                        </tr> -->
                        <tr>
                            <td class="col-1" style = "text-align: center; border: 0px solid;">
                                <div style="width: 24px;
                                    height: 24px;
                                    margin: 12px auto;
                                    border-radius: 12px;
                                    background: 
                                    <?php echo(task_works('dokiocrm_products_cronjob')?'green':'red');?>;">
                                </div>
                            </td>
                            <td style="border: 0px solid;"><span style = "line-height: 47px;">Categories, attributes, products and orders</span></td>
                            <td class="col-2" style = "text-align: center; border: 0px solid;">
                                <form 
                                style = "display:<?php echo(task_works('dokiocrm_products_cronjob')?'none':'block');?>"
                                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_on_cron_products">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-primary">Start</button>
                                </form>
                                <form 
                                    style = "display:<?php echo(task_works('dokiocrm_products_cronjob')?'block':'none');?>"
                                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_off_cron_products">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-primary">Stop</button>
                                </form>    
                            </td>
                        </tr>
                        <!-- <tr>
                            <td class="col-1" style = "text-align: center;">
                                <div style="width: 24px;
                                    height: 24px;
                                    margin: 12px auto;
                                    border-radius: 12px;
                                    background: 
                                    <?php echo(task_works('dokiocrm_orders_cronjob')?'green':'red');?>;">
                                </div>
                            </td>
                            <td><span style = "line-height: 47px;">Orders</span></td>
                            <td class="col-2" style = "text-align: center;">
                                <form 
                                style = "display:<?php echo(task_works('dokiocrm_orders_cronjob')?'none':'block');?>"
                                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_on_cron_orders">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-primary">Start</button>
                                </form>
                                <form 
                                    style = "display:<?php echo(task_works('dokiocrm_orders_cronjob')?'block':'none');?>"
                                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_off_cron_orders">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-primary">Stop</button>
                                </form>
                            </td>
                        </tr> -->
                    </tbody>
                </table>
                <!-- <form 
                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                    method="post">
                    <input type="hidden" name="action" value="c_get_crm_categories">
                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                    <button type="submit" class="btn btn-light">Categories</button>
                </form> 
                <form 
                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                    method="post">
                    <input type="hidden" name="action" value="c_get_crm_attributes">
                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                    <button type="submit" class="btn btn-light">Attributes</button>
                </form>
                <form 
                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                    method="post">
                    <input type="hidden" name="action" value="c_get_crm_terms">
                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                    <button type="submit" class="btn btn-light">Terms</button>
                </form>
                <form 
                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                    method="post">
                    <input type="hidden" name="action" value="c_get_crm_products">
                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                    <button type="submit" class="btn btn-light">Products</button>
                </form>                            
                <form 
                    action="http://localhost/DokioShop/wp-admin/admin-post.php" 
                    method="post">
                    <input type="hidden" name="action" value="c_get_crm_orders">
                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                    <button type="submit" class="btn btn-light">Orders</button>
                </form> -->

                <!-- <form method="post" action="c_get_crm_tax_rates">
                    <button type="submit" class="btn btn-primary">get_crm_tax_rates</button>
                </form> -->
            </div>

        </div>
        
    </div>
</div>


<?php
// require __DIR__ . '/../../vendor/autoload.php';

// use Automattic\WooCommerce\Client;
// use Automattic\WooCommerce\HttpClient\HttpClientException;

// $woocommerce = new Client(
//   get_option('woo_address'),
//   get_option('woo_consumer_key'),
//   get_option('woo_consumer_secret'),
//   get_option('API_address'),
//   get_option('secret_key'),
//   [
//     'version' => 'wc/v3',
//   ]
// );

if(isset($_GET['action'])){
    $the_action = $_GET['action'];
}

// if($the_action == 'dotest'){
//     echo '11111111111111111111';
// }








// try {


// // echo json_encode($woocommerce);
// echo '<pre>';
// print_r($woocommerce->get('products'));
// echo '</pre>';

// } catch (HttpClientException $e) {
//     echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
//     echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
//     echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
// }






?>


<!--?php

// get the general settings options
$theyoutubekey = get_option('API_address');
$thechannelid = get_option('secret_key');
$maxResults = '5';
$api_uri='https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId='.$thechannelid.'&maxResults='.$maxResults.'&key='.$theyoutubekey;
$videolist = json_decode(file_get_contents($api_uri));
echo $api_uri.'<br>';
foreach($videolist->items as $item){
    echo '<div style="border: 1px solid black;">';
    echo '<b>'.$item->snippet->title . '</b> <br> ';
    echo $item->snippet->description . ' <br> ';
    echo '<img src="'.$item->snippet->thumbnails->medium->url . '"/> <br>';
    echo '</div>';
}


?-->
