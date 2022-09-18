<?php
add_action('my_hourly_event', 'do_this_hourly');

// The action will trigger when someone visits your WordPress site
function my_activation() {
    if ( !wp_next_scheduled( 'my_hourly_event' ) ) {
        wp_schedule_event( time(), 'hourly', 'my_hourly_event');
    }
}
add_action('wp', 'my_activation');

function do_this_hourly() {
    // do something every hour
    echo(111);
}




// initiate the curl request
// $request = curl_init();

// curl_setopt($request, CURLOPT_URL,"http://localhost:8080/api/public/woo_v3/");
// curl_setopt($request, CURLOPT_POST, 1);
// curl_setopt($request, CURLOPT_POSTFIELDS,
//         "var1=value1&var2=value2");

// catch the response
// curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

// $response = curl_exec($request);

// curl_close ($request);

// do processing for the $response


// $url = 'http://localhost:8080/api/public/woo_v3/syncTaxesToStore?key='.get_option( 'secret_key' );
// $request = curl_init($url); 
// curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($request, CURLOPT_HEADER, 0);
// $data = curl_exec($request);
// curl_close($request);
// echo('<br><br><br>'.$data);