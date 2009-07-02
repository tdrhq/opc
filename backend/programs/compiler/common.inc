<?

function  runandgetoutput($cmd, &$output) {
  $f = popen ( $cmd, "r" ) ;
  if ( ! $f ) return 1;
  $output = "" ;
  $output = stream_get_contents($f);
  return pclose ( $f );

}
/**
 * Run a command and get both the output and error
 * streams
 * 
 * @param $cmd string - command
 * @param[out] $output  string(reference) - the output
 * @param[out] $err string(reference) - the input
 */
function runandgetoutputerr( $cmd, &$output, &$err ) {
  
  if ( DEBUG==1 ) echo "\ncommand called: $cmd \n";
  $descriptorspec = array(
						  0 => array("pipe", "r"),
						  1 => array("pipe", "w"),  
						  2 => array("pipe", "w")  
						  );


  $process = proc_open($cmd, $descriptorspec, $pipes);
  
  if ( ! $process ) return 1 ;

  /*
   * dangerously blockable calls, don't use for very large 
   * output.
   */
  $err    = stream_get_contents($pipes[2]);
  $output = stream_get_contents($pipes[1]) ;
  fclose($pipes[0]) ;
  fclose($pipes[1]);
  fclose($pipes[2]) ;
  
  $ret = proc_close($process);
 
  return $ret ;
}

define(DATE_STRING,'r');
