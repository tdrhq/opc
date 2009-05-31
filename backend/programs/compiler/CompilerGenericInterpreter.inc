<?


class CompilerGenericInterpreter { 
  var $interpreter ; 
  var $pathtocode ; 
  public function __construct($int ) {
	if ( empty($int) ) {
	  throw new Exception("Interpreted language not supported");
	} 
	$this -> interpreter = $int ; 
	$this->pathtocode = tempnam (
							config::$compile_temp_directory,
							"intr"  ) ;
	file_put_contents($this->pathtocode,"#!" . $int."\n") ;
  }
  
  public function compile($pathtocode)  {
	
	$this ->pathtocode = $pathtocode ;
	file_put_contents($this->pathtocode,
					  file_get_contents($pathtocode), FILE_APPEND);
	chmod($this->pathtocode, 0100) ;
	return true;
  }
  public function get_error() { 
	return "" ; 
  }
  public function get_exec_string() {
	//return $this->interpreter . " " . $this->pathtocode ;
	return $this->pathtocode ;
  }
  public function get_exec_dir() {
	return dirname($this->pathtocode) ;
  }
  public function __destruct() {
	unlink($this->pathtocode);
  }
}

?>
