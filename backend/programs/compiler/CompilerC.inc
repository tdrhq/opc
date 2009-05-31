<?

require_once "CompilerGenericGNU.inc" ;

class CompilerC extends CompilerGenericGNU { 

  public function __construct($param) {
	$this -> compiler_string = "/usr/bin/gcc -Wall -lm -O2 -x c " ;
	parent::__construct($param) ;
  }

};

?>
