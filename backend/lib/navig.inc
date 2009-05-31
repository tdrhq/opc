<?
void retreive(&$offset, &$limit) {
  $offset = (int) $_REQUEST['offset'] ; 
  $limit = (int) $_REQUEST['limit'] ;
  if ( empty($offset) or !is_int($offset) )
	$offset = 0 ;

  if (empty($limit) or !is_int($limit) ) 
	$limit = 50 ; 
  
  if ( $limit > 100 ) $limit = 100 ; 

}
function createNavigBar($offset, $limit) { 

 echo "<a href={$_SERVER['PHP_SELF']}?

}

?>
