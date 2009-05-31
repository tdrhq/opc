<?

require_once "CompilerGenericGNU.inc" ; 


class CompilerGcj extends CompilerGenericGNU { 

  var $pathtocode ; 

  public function __construct($param) {
	$this -> compiler_string = "/usr/bin/gcj -Wall --main=Main " ;
	parent::__construct($param) ;
  }
  
  public function compile($pathtocode) {
	//first copy this to some temporary directory
	$this->tmpdir = trim(`mktemp -d`) ; 
	
	$this->pathtocode = $this->tmpdir . "/Main.java" ;
	
	if (! copy( $pathtocode, $this->pathtocode ) ) {
	  throw new Exception("Unable to copy file");
	}

	return parent::compile($this->pathtocode) ;
  }

  public function __destruct() {
	unlink($this->pathtocode) ;
	rmdir(dirname($this->pathtocode) ) ;
	parent::__destruct() ;
  }
};

?>
