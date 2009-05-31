
<?

require_once "CompilerGenericGNU.inc" ;

/**
 * The compiler for C++
 */
class CompilerCpp extends CompilerGenericGNU
{

  public function __construct($param) {
	$this ->compiler_string="/usr/bin/g++ -Wall -O2 -x c++ ";
	parent::__construct($param);
  }


}; 

?>
