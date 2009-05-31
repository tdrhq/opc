<?

class DataController extends Zend_Controller_Action 
{
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
		return "text/plain"; /* unknown */
	}
	public function indexAction() 
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$file = $this->_request->get("file") ;
		$details = pathinfo($file);
		$path = $details['dirname'] ; 
		$filename = "public." . $details['basename'];
		
		$finalFile = config::getFilename("data/" . $path . "/$filename") ;
		if ( is_file($finalFile) ) { 
			$response = $this->getResponse();
			$response->setHeader('Cache-Control', 'public', true)
				->setHeader('Content-Description', 'File Transfer', true)
				->setHeader('Content-Type', $this->getContentType($details['extension']), true)
				->setHeader('Content-Transfer-Encoding', 'binary', true)
				->setBody(file_get_contents($finalFile)); 

			$response->sendResponse();
			//$this->setResponse($response);
			return ;
		}

		echo "File not found";

		/* else send a 404 error */
		$response = $this->getResponse() ;
		$response->setHttpResponseCode(404);
	}
}
