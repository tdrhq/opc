<?

class CompilerJava { 

  var $compiler_string ; 
  
  var $pathtocode ; 

  var $error ; 

  public function __construct() {
	if ( !empty(config::$compiler_string_java) )
	  $this->compiler_string = config::$compiler_string_java ;
	$this->compiler_string = 'javac ' ;
  }
  public function  compile($pathtocode){
	
	//Copy the file to Main.java within the same directory
	if ( config::$enable_unique_java_classname) {

	  $dir = trim(`mktemp -d`) ; 

	  $this->pathtocode = $dir . "/Main.java" ;

	  if (! copy( $pathtocode, $this->pathtocode ) ) {
		throw new Exception("Unable to copy file");
	  }
	  $pathtocode = $this->pathtocode ;
	  
	} else { 
	  throw new Exception("please enable unique java classname");
	}

	$ret =runandgetoutput( $this->compiler_string . 
						   " " . escapeshellarg($pathtocode),
						   $bluff, $this->error) ;
	return ($ret==0) ; 
  }
  public function get_error(){
	return $this -> error;
  }
  public function get_exec_string() { 
	return "java Main" ;
  }
  public function get_exec_dir() {
	return dirname($this->pathtocode) ;
  }
  public function __destruct() {
	unlink($this->pathtocode) ;
	unlink(dirname($this->pathtocode). "/Main.class" );
	rmdir(dirname($this->pathtocode)) ;
  }

}
