<?

require_once "lib/contest.inc";

class PagesController extends Zend_Controller_Action
{
	public function indexAction() 
	{
		$page = $this->getRequest()->get("page") ;
		$contest = Contest::factory(webconfig::getContestId());

		if (!$contest) return; 

		$xp = $contest->getXPath() ;
		
		$res = $xp->query("/contest/frontend/page[@id='$page']/@href");
		$href = $res->item(0)->nodeValue ; 
		if ( substr($href,0,5) == "http:" or substr($href,0,6) =="https:") 
			$this->_redirect($href);
		$file = config::getFilename("data/contests/" . $res->item(0)->nodeValue); 
		if ( !is_file($file) )  
			echo "Please edit '$file' to view this page.";
		else
			echo file_get_contents($file);
	}
}
