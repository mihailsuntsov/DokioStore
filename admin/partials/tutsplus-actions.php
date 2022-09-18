<?php
 
 add_action( 'admin_post_add_foobar', 'prefix_admin_add_foobar' );

 //this next action version allows users not logged in to submit requests
 //if you want to have both logged in and not logged in users submitting, you have to add both actions!
 add_action( 'admin_post_nopriv_add_foobar', 'prefix_admin_add_foobar' );
 
 function prefix_admin_add_foobar() {
     status_header(200);
     //request handlers should exit() when they complete their task
     exit("Server received '{$_REQUEST['data']}' from your browser.");
 }