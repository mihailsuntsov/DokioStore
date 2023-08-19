<?php

add_action( 'admin_head', 'set_ajaxurl' ); 
function set_ajaxurl() {?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {
		var crm_api_url = "<?php echo get_option( 'API_address' ); ?>";
	});
	</script> <?php
}


add_action( 'admin_footer', 'alerts' ); 
function alerts() {?>
	<script type="text/javascript" >
            function bootstrapAlert(type, message){
                jQuery(document).ready(function($) {
                    $.bootstrapGrowl(message, {
                        type: type, // null, danger, info, success
                        offset: {from:"top", amount: 50}, // top or bottom 
                        width: 'auto', // (integer, or 'auto')
                        align: "right", // left right center
                        delay: 6000,
                        allow_dismiss: true,
                    });
                });
            }
	</script> <?php
}


add_action( 'admin_footer', 'my_action' ); 
function my_action() { ?>
	<script type="text/javascript" >
        

        


        // Connection DokioCRM test
        jQuery(document).ready(function ($) {
            $('#aaa').click(function (e) {                
                var crm_api_url_ = "<?php echo get_option( 'API_address' ); ?>/DokioCrmConnectionTest";
                $.ajax({
                    type: "GET",
                    url: crm_api_url_+'?key=<?php echo get_option( 'secret_key' ); ?>',
                    contentType: "application/json",
                    success: async function (crm_response) {                                            
                        console.log(crm_response);
                        switch(crm_response){
                            case 1:{// Data from the CRM system received
                                bootstrapAlert('success', 'Ok! Connection test passed!');                      
                                break;
                            }
                            case -200:{// CRM secret key error
                                bootstrapAlert('warning', 'There is a connection, but wrong CRM secret key!');                      
                                break;
                            }
                            default:{// Error
                                bootstrapAlert('danger', 'Connection test controller error!');      
                            }
                        }
                    },
                    error: function (req, error) {
                        bootstrapAlert('danger', 'Connection to CRM test request failed! ' + req.responseText);
                    },
                });
            });
        });

        // Connection CRM test
        jQuery(document).ready(function ($) {
            $('#test_crm_connection').click(async function (e) { 
                try {
                    const result = await getTestCrmConnection($);
                    if(result){                    
                        console.log(result);
                        switch(result){
                            case 1:{// Data from the CRM system received
                                bootstrapAlert('success', 'Ok! Connection test passed!');                      
                                break;
                            }
                            case -200:{// CRM secret key error
                                bootstrapAlert('warning', 'There is a connection, but wrong CRM secret key!');                      
                                break;
                            }
                            default:{// Error
                                bootstrapAlert('danger', 'Connection test controller error!');      
                            }
                        }
                        // bootstrapAlert('success', '<p><b>result=</b>'+JSON.stringify(result)+'</p>'); 
                    }
                } catch (error) {
                    // bootstrapAlert('danger', '<p>Connection CRM test failed!</p><p>Error message: ' + JSON.stringify(error)+'</p>');     
                    bootstrapAlert('danger', '<p>'+ error.responseText+'</p>');  
                }
            });
        });

        // Connection WooCommerce test
        jQuery(document).ready(function ($) {
            $('#test_woo_connection').click(async function (e) { 
                try {
                    const result = await getTestWooConnection($);
                    if(result){                    
                        // sentTaxesToCRM($, result);
                        bootstrapAlert('success', 'Ok! Connection WooCommerce test is passed!');  
                    }
                } catch (error) {
                    bootstrapAlert('danger', '<p>Connection WooCommerce test failed!</p><p><b>Please check that:</b><br>1. Settings->Permalinks->Permalink structure is not equals "Plain"<br>2.The fields <i>Woocommerce consumer key</i> and <i>Woocommerce consumer key</i> are filled in correctly</p><hr><p>Error message: ' + JSON.stringify(error)+'</p>');      
                }
            });
        });

        jQuery(document).ready(function ($) {
            $('#cron_products_btn').click(
                async function (e) { 
                var handleError = function (err) {
                    // console.warn(err);
                    refreshProductsButton();
                };
                await cronJob($,'sync_products').catch(handleError).then((response) => {
                        if (response.status >= 400 && response.status < 600) {
                        throw new Error("Bad response from server");
                        }
                        return response;
                    }).then((returnedResponse) => {
                    // Your response to manipulate
                    refreshProductsButton();
                    }).catch((error) => {
                    // Your error is here!
                    console.log(error)
                    });
            });
        });

        jQuery(document).ready(function ($) {
            $('#cron_orders_btn').click(
                async function (e) { 
                var handleError = function (err) {
                    // console.warn(err);
                    refreshOrdersButton();
                };
                await cronJob($,'sync_orders').catch(handleError).then((response) => {
                        if (response.status >= 400 && response.status < 600) {
                        throw new Error("Bad response from server");
                        }
                        return response;
                    }).then((returnedResponse) => {
                    // Your response to manipulate
                    refreshOrdersButton();
                    }).catch((error) => {
                    // Your error is here!
                    console.log(error)
                    });
            });
        });
        function refreshProductsButton(){
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : ajaxurl,
                data : {action: "refresh_products_cron_status"},
                success: function(response) { 
                    console.log(response.task_works)  ;
                    if(response.task_works){
                        document.getElementById("cron_products_btn").innerHTML = "Stop";
                        document.getElementById("task_products_circle").style.background="green";

                    }else{
                        document.getElementById("cron_products_btn").innerHTML = "Start";
                        document.getElementById("task_products_circle").style.background="red";
                    }
                }
            });
        }
        function refreshOrdersButton(){
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : ajaxurl,
                data : {action: "refresh_orders_cron_status"},
                success: function(response) { 
                    console.log(response.task_works)  ;
                    if(response.task_works){
                        document.getElementById("cron_orders_btn").innerHTML = "Stop";
                        document.getElementById("task_orders_circle").style.background="green";

                    }else{
                        document.getElementById("cron_orders_btn").innerHTML = "Start";
                        document.getElementById("task_orders_circle").style.background="red";
                    }
                }
            });
        }
        function setProgress($,total, left) {
            // alert('total - '+total+', left - '+left)
            precent=Math.round(left/total*100);
                $('#progress').attr('style','width: '+precent+'%');
                $('#precent_text').text(precent+'%');
                if(precent==100)
                {
                    setTimeout(() => {
                        precent=0; 
                        $('#progress').attr('style','width: 0%');
                        $('#precent_text').text('');
                    }, 1000);
                }
        }

        async function getTestWooConnection($){
            return  $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: ajaxurl,
                    data: {'action': 'test_woo_connection'}
                });
        }
        async function getTestCrmConnection($){
            return  $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: ajaxurl,
                    data: {'action': 'test_crm_connection'}
                });
        }
        async function cronJob($, job){
            return  $.ajax({
                    type: "POST",
                    dataType: 'json; charset=utf-8',
                    url: ajaxurl,
                    data: {'action': job}
                });
        }
        async function statusButton($){
            return  $.ajax({
                    type: "POST",
                    dataType: 'json; charset=utf-8',
                    url: ajaxurl,
                    data: {'action': 'refresh_products_cron_status'}
                });
        }

    </script>
    <?php
}

