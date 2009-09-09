<?php
/**
 * Copyright 2007-2009 Chennai Mathematical Institute
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file   DataController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

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
