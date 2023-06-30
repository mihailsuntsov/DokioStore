<?php
/**
 * Write an entry to a log file in the uploads directory.
 * 
 * @since x.x.x
 * 
 * @param mixed $entry String or array of the information to write to the log.
 * @param string $file Optional. The file basename for the .log file.
 * @param string $mode Optional. The type of write. See 'mode' at https://www.php.net/manual/en/function.fopen.php.
 * @return boolean|int Number of bytes written to the lof file, false otherwise.
 */
// Append an entry to the uploads/plugin.log file.
// logger( 'Something happened.' );
// Append an array entry to the uploads/plugin.log file.
// logger( ['new_user' => 'benmarshall' ] );

if ( ! function_exists( 'logger' ) ) {
  function logger( $entry ) { 
    // Get WordPress uploads directory.
    $upload_dir = wp_upload_dir();
    $upload_dir = $upload_dir['basedir'].'/ErpStore_logs';
    wp_mkdir_p( $upload_dir );
    // If the entry is array, json_encode.
    if ( is_array( $entry ) ) { 
      $entry = json_encode( $entry ); 
    } 
    // Write the log file.
    $file  = $upload_dir . '/'.substr(current_time( 'mysql' ), 0, 10) . '.log';
    $file  = fopen( $file, 'a' );
    $bytes = fwrite( $file, current_time( 'mysql' ) . "::" . $entry . "\n" ); 
    fclose( $file ); 
    return $bytes;
  }
}