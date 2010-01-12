<?php

require_once "Zend/View/Helper/Abstract.php";
require_once "lib/problems.inc";
/**
 * Format a problem with appropriate link
 */
class Zend_View_Helper_Problem
	extends Zend_View_Helper_Abstract
{
	var $view;
	public function setView (Zend_View_Interface $_view)
	{
		$this->view = $_view;
	}

	/* username can be username or uid */
	public function problem ($problem)
	{
		$prob = ProblemTable::get_problem ($problem);
		return $this->view->link 
			("/problems/" . $prob->getId (),
			 $prob->getId (),
			 array ("title" => $prob->getNickname ()));
	}
}