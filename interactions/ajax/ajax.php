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
                        delay: 3000,
                        allow_dismiss: true,
                    });
                });
            }
	</script> <?php
}


add_action( 'admin_footer', 'my_action' ); 
function my_action() { ?>
	<script type="text/javascript" >
        // Get tax rates from WooCommerce and sent them to CRM
        jQuery(document).ready(function ($) {
            $('#sync_taxes_from_store').click(async function (e) {
                try {
                    const result = await getListAllStoreTaxRates($);
                    if(result){                    
                        sentTaxesToCRM($, result);
                    }
                } catch (error) {
                    console.log('Error! InsertAssignments:', error);
                }
            });
        });
            // Get tax rates from CRM and sent them to WooCommerce
        jQuery(document).ready(function ($) {
            $('#sync_taxes_to_store').click(function (e) {
                
                var crm_api_url_ = "http://localhost:8080/api/public/woo_v3/syncTaxesToStore";
                $.ajax({
                    type: "GET",
                    url: crm_api_url_+'?key=<?php echo get_option( 'secret_key' ); ?>',
                    contentType: "application/json",
                    success: async function (crm_response) {
                                            
                        console.log(crm_response);
                        switch(crm_response.queryResultCode){
                            case 1:{// Data from the CRM system received
                                bootstrapAlert('success', 'Data from the CRM system received');
                                // var ids_pairs  = [];
                                var ids_pairs  = [];
                                var woo_ids = [];//array of tax rates in WooCommerce
                                var was_query_all_store_taxes = false;
                                var total = 0;
                                var left = 0;
                                // crm_response.taxes.forEach(x => {
                                for (const x of crm_response.taxes) {total++};

                                for (const x of crm_response.taxes) {
                                    // console.log('id - '+x.id); // console.log('name - '+x.name);// console.log('rate - '+x.rate);
                                    
                                    // if there is no id of WooCommerce - then this tax rate is not in the WooCommerce - Create tax rate in the WooCommerce
                                    if(!x.id){
                                        var data = {
                                            'action': 'create_tax_rate',
                                            'rate'  : x.rate,
                                            'name'  : x.name
                                        };
                                        console.log('Creating tax rate');
                                        try {
                                            const result = await createTaxRate($,data);
                                            if(result){                    
                                                console.log('Tax rate created with id = ' + result.id);
                                                ids_pairs.push({
                                                    id:result.id,
                                                    crm_id:x.crm_id
                                                });
                                                left++;setProgress($,total,left);
                                            }
                                        } catch (error) {
                                            console.log('Create tax rate error: '.error);
                                            bootstrapAlert('danger', 'Create tax rate error: '.error);
                                        }
                                    } else { 
                                        // If there is id - then MAYBE this tax rate is already in WooCommerce - Update tax rate in WooCommerce
                                        // Maybe - because this tax rate could be removed manually
                                        // And firsteval before update I need to check that the WooCommerce has the tax rate with this id:
                                        
                                        // console.log('was_query_all_store_taxes - '+was_query_all_store_taxes);
                                        if(!was_query_all_store_taxes){
                                            console.log('Querying all taxes from WooCommerce...');
                                            try {
                                                const result = await getListAllStoreTaxRates($);
                                                if(result){                    
                                                    result.forEach(x => {
                                                        woo_ids.push(+x.id);
                                                    });
                                                    // console.log('woo_ids.length = '+woo_ids.length);
                                                    console.log('woo_ids = '+woo_ids);
                                                }
                                                was_query_all_store_taxes=true;// To not doing query on every tax rate. I can't check it by empty woo_ids[] cause WooCommerce can hasn't tax rates
                                            } catch (error) {
                                                console.log('Getting WooCommerce list tax rate error: '.error);
                                                bootstrapAlert('danger', 'Getting WooCommerce list tax rate error: '.error);
                                            }
                                        }
                                        
                                        // If the tax rate wasn't removed manually, i.e. woo_ids[] contains woo_id receqved from CRM
                                        console.log('x.id - '+x.id+', array: '+ woo_ids + ', is include? - '+woo_ids.includes(+x.id));
                                        if(woo_ids.includes(+x.id)){
                                            console.log('INCLUDES!!'); 
                                            // Updating this tax rate
                                            console.log('updating Tax Rate');
                                            var data = {
                                                'action': 'update_tax_rate',
                                                'id'    : x.id,
                                                'rate'  : x.rate,
                                                'name'  : x.name
                                            };
                                            try {
                                                const result = await updateTaxRate($,data);
                                                if(result){                    
                                                    console.log('Tax rate updated!');
                                                left++;setProgress($,total,left);
                                                }
                                            } catch (error) {
                                                console.log('Create tax rate error: '.error);
                                                bootstrapAlert('danger', 'Update tax rate error: '.error);
                                            }
                                        } else { //if the tax rate was removed manually, i.e. woo_ids[] does not contain woo_id receqved from CRM
                                            console.log('Creating tax rate that was removed manually');
                                            try {
                                                var data = {
                                                    'action': 'create_tax_rate',
                                                    'rate'  : x.rate,
                                                    'name'  : x.name
                                                };
                                                const result = await createTaxRate($,data);
                                                if(result){                    
                                                    console.log('Tax rate created with id = ' + result.id);
                                                    ids_pairs.push({
                                                        id:result.id,
                                                        crm_id:x.crm_id
                                                    });
                                                left++;setProgress($,total,left);
                                                }
                                            } catch (error) {
                                                console.log('Create tax rate error: '.error);
                                                bootstrapAlert('danger', 'Create tax rate error: '.error);
                                            }
                                        }
                                        // if(ids_pairs)
                                    }
                                };
                                if(ids_pairs.length>0){sentTaxesIdsToCRM($,ids_pairs);}                                    
                                break;
                            }
                            case -200:{// wrong Secret key
                                bootstrapAlert('danger', 'Wrong CRM Secret key');                        
                                break;
                            }
                            default:{// Error
                                bootstrapAlert('danger', 'Operation error');      
                            }
                        }
                    },
                    error: function (req, error) {
                        bootstrapAlert('danger', error);
                    },
                });
            });
        });
        
	</script> 

    

























    <script type="text/javascript" >
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

        async function createTaxRate($,data){
            return  $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: ajaxurl,
                    data: data
            });
        }
        async function updateTaxRate($,data){
            return  $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: ajaxurl,
                    data: data
                });
        }

        async function getListAllStoreTaxRates($){
            return  $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: ajaxurl,
                    data: {'action': 'list_all_taxes'}
                });
        }

        // -----------------  Sending tax rates to the CRM  -----------------
        function sentTaxesToCRM($,obj) {
            console.log('sentTaxesToCRM function');
            setProgress($,2,1);
            var crm_api_url_ = "http://localhost:8080/api/public/woo_v3/syncTaxesFromStore";
            $.ajax({
                type: "POST",
                url: crm_api_url_,
                data: '{"crmSecretKey":"<?php echo get_option( 'secret_key' ); ?>", "saveTaxes":<?php echo (get_option( 'save_crm_taxes' )=='on'?"true":"false");?>,"taxes":'+JSON.stringify(obj)+'}',
                contentType: "application/json",
                success: function (crm_response) {
                    console.log(crm_response);
                    switch(crm_response){
                        case 1:{// Операция успешно провведена 
                            bootstrapAlert('success', 'Operation completed successfully');
                            setProgress($,2,2);
                            break;
                        }
                        case -200:{// wrong Secret key
                            bootstrapAlert('danger', 'Wrong CRM Secret key');                        
                            break;
                        }
                        default:{// Error
                            bootstrapAlert('danger', 'Operation error');      
                        }
                    }
                },
                error: function (req, error) {
                    bootstrapAlert('danger', error);
                },
            });
        }
        // -----------------  Sending tax rates IDs to the CRM  -----------------
        function sentTaxesIdsToCRM($,ids_pairs){
            var crm_api_url_ = "http://localhost:8080/api/public/woo_v3/syncTaxesIds";
            $.ajax({
                type: "POST",
                url: crm_api_url_,
                data: '{"crmSecretKey":"<?php echo get_option( 'secret_key' ); ?>","idsSet":'+JSON.stringify(ids_pairs)+'}',
                contentType: "application/json",
                success: function (crm_response) {
                    console.log("crm_response - " + crm_response);
                    switch(crm_response){
                        case 1:{// Операция успешно провведена 
                            bootstrapAlert('success', 'Operation completed successfully');
                            break;
                        }
                        case -200:{// wrong Secret key
                            bootstrapAlert('danger', 'Wrong CRM Secret key');                        
                            break;
                        }
                        default:{// Error
                            bootstrapAlert('danger', 'Operation error');      
                        }
                    }
                },
                error: function (req, error) {
                    bootstrapAlert('danger', error);
                },
            });
        }

    </script>
    <?php
}

