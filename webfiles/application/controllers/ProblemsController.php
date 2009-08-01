<?

require_once "lib/problems.inc" ;

class ProblemsController extends Zend_Controller_Action { 
	var $content_html = "" ; 

	function fixImages ()
	{
		$dom = new DomDocument ();
		$dom->loadXML ($this->view->content_html, LIBXML_DTDLOAD | LIBXML_NOXMLDECL);
		$xp = new DOMXPath ($dom);
		$xp->registerNamespace(
			'html','http://www.w3.org/1999/xhtml' );

		$url = Zend_Controller_Front::getInstance()->getBaseUrl () . webconfig::getContestRelativeBaseUrl();
		$url .= "problems/" . $this->_request->get("probid");

		$res = $xp->query ("//html:img/@src");
		foreach ($res as $node) {
			$oldImage = $node->nodeValue;

			/* is this a complete path? */
			if (substr ($oldImage, 0, 5) == "http:" || substr ($oldImage, 0, 6) == "https:" || $oldImage[0] == '/')
				continue;
			
			$node->nodeValue = "$url/$oldImage";
		}

		$this->view->content_html = $dom->saveXML();
	}
	public function getContentType($ext) 
	{
		$ext = strtolower($ext);
		if ($ext == "htm" or $ext == "html") return "text/html"; 
		if ($ext == "txt" ) return "text/plain" ;
		if ($ext == "jpg" or $ext == "jpeg" ) return "image/jpeg" ;
		if ($ext == "gif") return "image/gif";
		if ($ext == "tiff") return "image/tiff";
		if ($ext == "bmp") return "image/bmp";
		if ($ext == "png") return "image/png";
		if ($ext == "mpg" or $ext == "mpeg") return "image/mpeg";
		if ($ext == "pdf" ) return "application/pdf" ;
		return NULL; /* unknown */
	}

	public function fileAction() 
	{
		if (!$this->validateProblemAccess ()) return;
		$prob = $this->_request->get("probid");

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$file = $this->_request->get("file") ;
		
		if (strstr ($file, "/") || strstr ($file, "\\")) {
			$this->_forward ("illegal", "error");
			return;
		}
			
		$details = pathinfo ($file);
		$contentType = $this->getContentType($details['extension']);

		if (empty($contentType)) {
			$this->_forward ("illegal", "error");
			return;
		}

		$finalFile = config::getFilename("data/problems/$prob/$file") ;
		if ( is_file($finalFile) ) { 
			$response = $this->getResponse();
			$response->setHeader('Cache-Control', 'public', true)
				->setHeader('Content-Description', 'File Transfer', true)
				->setHeader('Content-Type', $contentType, true)
				->setHeader('Content-Transfer-Encoding', 'binary', true)
				->setBody(file_get_contents($finalFile)); 

			return ;
		}

		echo "File ($finalFile, $contentType) not found";

		/* else send a 404 error */
		$response = $this->getResponse() ;
		$response->setHttpResponseCode(404);
	}

        public function preDispatch()
        {
                $curuser = Zend_Auth::getInstance()->getIdentity();
		$user = User::factory ($curuser);
                if (!empty($curuser) && !empty ($user) && $user->isAdmin()) {
                        return; /* no other tests needed for admin */
                }

		Zend_Loader::loadClass("ContestModel");
                $this->contestmodel = new ContestModel;
                $this->state  = $this->contestmodel->getContestState(webconfig::getContestId());
                if ( $this->state == "before" )
                        $this->_forward("before", "error", NULL, array());
        }

	function indexAction () {
		/* build query */
		$db = contestDB::get_zend_db();
		$this->view->title = "Problems" ;

                $curuser = Zend_Auth::getInstance()->getIdentity();
                if (empty($curuser)) {
			$this->view->problems = ProblemTable::get_problem_list(webconfig::getContestId(), 0, 100);
			return;
                }

		/* case 2: build a complex query */
		$query = "select distinct p.rowid as rowid,p.id as id,p.nickname as nickname,s.state as state from problemdata  as p left join (select * from submissionqueue where uid = ? and state='Accepted') as s on p.id = s.problemid where p.owner=? group by p.rowid,p.id,p.nickname,s.state order by rowid;";
		
		$this->view->problems = $db->fetchAll ($query, array($curuser, webconfig::getContestId()));
	}

	function validateProblemAccess ()
	{
		$this->view->problem_code = $this->_request->get("probid") ;
		$this->view->prob = $prob = ProblemTable::get_problem ("{$this->view->problem_code}") ;

		if (empty($prob) || $prob->owner != webconfig::getContestId()) { 
			$this->_forward ("404", "error");
			return false;
		}
		
		return true;
	}

	function viewAction () { 
		if (!$this->validateProblemAccess ()) return;

		$prob = $this->view->prob;

		$this->view->content_html = file_get_contents(get_file_name("data/problems/"  
							  . $this->_request->get("probid")
									    . "/index.html")) ;


		if (function_exists("tidy_parse_string") && $this->_request->get("tidy") != "false") {
			/* tidy to XHTML strict */
			$opt = array("output-xhtml" => true,
				     "add-xml-decl" => true,
				     "doctype" => "strict");
			$tidy = tidy_parse_string($this->view->content_html, $opt);
			tidy_clean_repair ($tidy);

			$this->view->content_html = tidy_get_output ($tidy);
		}

		$this->fixImages ();

		if ($this->_request->get("plain") == "true") {
			$this->_helper->layout->disableLayout ();
			$this->_helper->viewRenderer->setNoRender ();
			$this->getResponse()->setBody ($this->view->content_html);
		}
	}
	

}
