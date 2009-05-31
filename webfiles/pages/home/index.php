<?

/* the purpose of this file is to detect if rewrite engine is on.
 * if this file ever runs, then we can safely say that rewrite engine 
 * is off, and so I can specifically redirect to the index.php file.
 */

header( 'Location: ../../index.php' ) ;
?>
