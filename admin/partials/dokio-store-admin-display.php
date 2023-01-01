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
                <h1 class="display-4">DokioCRM plugin settings</h1>
                <p class="lead">Use this section to set up DokioCRM plugin parameters.</p>
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
                    <!-- <div class="form-group">
                        <label for="woo_address">Woocommerce store site address</label>
                        <input type="text" name="woo_address" value="<?php //echo get_option( 'woo_address' ); ?>" class="form-control" id="woo_address" placeholder="Store address">
                    </div> -->
                    <div class="form-group">
                        <label for="woo_consumer_key">Woocommerce consumer key</label>
                        <input type="text" name="woo_consumer_key" value="<?php echo get_option( 'woo_consumer_key' ); ?>" class="form-control" id="woo_consumer_key" placeholder="Consumer key">
                    </div>
                    <div class="form-group">
                        <label for="woo_consumer_secret">Woocommerce consumer secret</label>
                        <input type="text" name="woo_consumer_secret" value="<?php echo get_option( 'woo_consumer_secret' ); ?>" class="form-control" id="woo_consumer_secret" placeholder="Consumer secret">
                    </div>

                    <div class="form-check form-switch">
                    <input  class="form-check-input" 
                            type="checkbox" 
                            name="use_annasta_filter" 
                            id="use_annasta_filter"  
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Switch it in if your store use the free version of Annasta product filters, and if you have attributes that need to be shown in some categories and hide in the other categories, or you have attributes that should be shown or hided depending on the values of their parent attributes"
                            style="margin-top: 0.3rem; margin-left: -1.25rem;" 
                            <?php echo (get_option( 'use_annasta_filter' )=='on'?"checked":""); ?>
                            >
                        <label  class="form-check-label" 
                                for="use_annasta_filter" 
                                data-toggle="tooltip" 
                                data-placement="top" 
                                title="Switch it in if your store use the free version of Annasta product filters, and if you have attributes that need to be shown in some categories and hide in the other categories, or you have attributes that should be shown or hided depending on the values of their parent attributes">Store use free Annasta product filter</label>
                    </div>
                    <div class="form-group" style="display:<?php echo (get_option( 'use_annasta_filter' )=='on'?"block":"none");?>">
                        <label for="annasta_filter_value">Filter text</label>
                        <textarea id="annasta_filter_value" name="annasta_filter_value" placeholder="   If you use free version of Annasta product filter, and if you have attributes that need to be shown in some categories and hide in the other categories, or you have attributes that should be shown or hided depending on the values of their parent attributes, then all these parameters you can set here.
    It must be an array of string, separated by ; in the format:
[attribute's slug name for which this condition made]:[condition by categories separated by commas]|[parent attribute's slug]=[condition by slugs of parent attribute, separated by commas] [;] [Next condition...

                        Example:
                        rent-period:houses,apartaments|deal-type=rent;
                        construction-phase:|deal-type=sale,exchange;
                        floor:apartaments|
                        
                        In this example the attribute with slug 'rent-period' will be shown only in categories 'houses','apartaments', and if attribute with slug 'deal-type' has value 'rent'
                        the attribute with slug 'construction-phase' will be shown in all categories, and if attribute with slug 'deal-type' has values 'sale or 'exchange'
                        the attribute with slug 'floor' will be shown only in category 'apartaments', and not depends from another attributes

                        " class="form-control" rows="12"><?php echo get_option( 'annasta_filter_value' ); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-primary" id="test_connection">Test connection to DokioCRM</button>
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
                                    action="<?php echo get_option( 'siteurl' ); ?>/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_on_cron_products">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-primary">Start</button>
                                </form>
                                <form 
                                    style = "display:<?php echo(task_works('dokiocrm_products_cronjob')?'block':'none');?>"
                                    action="<?php echo get_option( 'siteurl' ); ?>/wp-admin/admin-post.php" 
                                    method="post">
                                    <input type="hidden" name="action" value="turn_off_cron_products">
                                    <input type="hidden" name="backpage" value="<?php echo($curr_url); ?>">
                                    <button type="submit" class="btn btn-primary">Stop</button>
                                </form>    
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
                
            </div>

        </div>
        
    </div>
    
</div>


<?php
// require __DIR__ . '/../../vendor/autoload.php';

// use Automattic\WooCommerce\Client;
// use Automattic\WooCommerce\HttpClient\HttpClientException;

// $woocommerce = new Client(
//   get_option('siteurl'),
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
