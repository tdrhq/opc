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
 * @file   problems.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

/**
 * Represents a problem. This is basic problem information.
 * The problem object will provide, in some sense, *static information*.
 * That means that information provided will not depend on submissions made.
 * For example, number of submissions to a problem, is not defined here.
 */
class Problem 
{ 
	/**
	 * The global unique id for the problem.
	 * @private 
	 */
	var $id;
	
	/**
	 * The global unique rowid for this problem.
	 * @private
	 */
	var $rowid;
	
	/**
	 * The friendly name for the problem. 
	 * @private
	 */
	var $nickname;
	
	/**
	 * The state of the problem. 
	 * @note In the future this might be used to specify a contest. As of 
	 * OPC 2007 this field has not been used. 
	 * @private
	 */
	var $state;  

	/**
	 * The owner of the problem. (i.e. the contest to which this belongs to)
	 */
	var $owner;
	
	public function getId() 
	{
		return $this->id; 
	}
	
	public function getRowId ()
	{
		return $this->rowid;
	}

	public function getNickname() 
	{ 
		return $this->nickname;
	}

	public function getState() 
	{ 
		return $this->state; 
	}
	
	public function getContest() 
	{ 
		return $this->state; 
	}
  
	public function getOwner() 
	{
		return $this->owner;
	}
}


class ProblemDetails extends Problem 
{ 
	/**
	 * An integer denoting the number of testcases.
	 */
	var $numcases; 
	
	/**
	 * An array containg $numcases input data file names.
	 */
	var $inputpaths = array();
	
	/**
	 * An array containing $numcases output data filenames. 
	 */
	var $outputpaths = array();
	
	/**
	 * An array (or string: pseudo array) containing $numcases per-testcase
	 * scores.
	 */
	var $scoreweights;
	
	/**
	 * Submission limit per user.
	 */
	var $submissionlimit; 
	
	/**
	 * Resource limit string.
	 * This is used to get the resource string as required by the grading 
	 * backend.
	 * @private
	 * @deprecated Since the resouce limit string is very dependent on how
	 * the backend works, working with this should be avoided. In the future 
	 * each resource will be given a seperate field.
	 */
	var $resourcelimits; 
	
	/**
	 * The checker used to verify a submissions output.
	 * @note This is an internal detail. This data should not reach the end 
	 * user. 
	 */
	var $checker; 
	
	/**
	 * Miscellaneous details.
	 * @deprecated This is only for database compatibility. Do not use this 
	 * field for important data.
	 */
	var $misc;
	
	/**
	 * Grading style. The main grading styles can be "opc" (Stop at the
	 * first testcase that fails and give partial score), "spoj" (Stop at the
	 * first testcase that fails and give zero or full score), and "ioi" (run
	 * all testcases, and give partial credit even if a testcase in between 
	 * fails. 
	 */
	var $grading_style;
	
	/**
	 * Memory limit for this problem. 
	 * @private
	 * @see getMemoryLimit();
	 */ 

	var $memory_limit; 
	
	/**
	 * Runtime CPU limit for a submission to this problem.
	 * @private
	 * @see getRuntimeLimit(); 
	 */
	var $runtime_limit; 
	
	/**
	 * Maximum the program submission can output.
	 * @private
	 */
	var $file_output_size_limit; 
	
	/**
	 * The resource from where this problem was taken from, for example a
	 * contest.  Not to be confused with resourcelimits. 
	 */
	var $resource;
	
	/**
	 * The author of this problem.
	 */
	var $author;
	
	var $doesCheckerOutputScore;
	
	/**
	 * Compile options. An associative array indicating additional compile
	 * options on a per language basis.
	 */
	var $compileOptions;
	
	/**
	 * Gets the memory limit for this problem
	 * Note that this is in human readable format. For example 5MB could 
	 * either be returned as 5M or 5120K or just 5242880. You should
	 * not rely on any specific format.
	 *
	 * @return string
	 */
	function getMemoryLimit() 
	{ 
		if (!empty($this->memory_limit)) 
			return $this->memory_limit;
		else { 
			/** @todo Parse $resourcelimits */
			return "0M";
		}
	}
	
	/**
	 * Gets the runtime for this submission in fractions of a second.
	 * @return float
	 */
	function getRuntimeLimit() 
	{ 
		if (!empty($this->runtime_limit))
			return (float)$this->runtime_limit; 
		else { 
			/** @todo Parse $resourcelimits */
			return 0.0;
		}
	}
	
	/**
	 * Gets the limit on the amount of data the submission can output
	 * in human readable format.
	 * 
	 * @return integer
	 */
	function getFileOutputSizeLimit() 
	{ 
		if (!empty($this->file_output_size_limit)) 
			return $this->file_output_size_limit; 
		else { 
			/** @todo Parse $resourcelimits */
			return "0M"; 
		}
	}
	
	/**
	 * Function to return resource limits as an associative array.
	 *
	 * @return Associative array
	 */
	function getResourceLimitsArray ()
	{
		$ret = array ();
		if ($this->memory_limit) $ret ["mem"] = $this->memory_limit;
		if ($this->runtime_limit) $ret ["time"] = $this->runtime_limit;
		if ($this->file_output_size_limit) 
			$ret["fsize"] = $this->file_output_size_limit;
		return $ret;
	}
       
	static function factory($probid) 
	{ 
		return ProblemTable::get_problem($probid);
	}
	
	function getSubmissionLimit() 
	{
		if (empty($this->submissionlimit)) 
			return 1000000; /* big value */
		else
			return $this->submissionlimit; 
	}
}
/**
 * Manages the list of problems
 *
 */
class ProblemTable 
{
	const tablename='problemdata';
	
	/**
	 * Gets a problem the problem's database in a compact object.
	 * 
	 * @param $id a string denoting the problem-id
	 * @return an object of type ProblemDetails
	 */
	public static function get_problem ($id) 
	{ 
		
		$db = contestDB::get_zend_db(); 
		
		$res = $db->select()->from(ProblemTable::tablename) 
			->where('id = ?', $id) ->query();
		
		
		$prob = new ProblemDetails;
		
		try { 
			$obj = $res->fetch();
		} catch (Exception $e)  { 
			return NULL;
		}
		
		if (empty($obj)) return NULL;
		
		/* fill in all the details. */
		$prob -> id = $id;
		$prob -> nickname = $obj->nickname;
		$prob -> rowid = $obj->rowid;
		$prob -> numcases = $obj->numcases; 
		$prob -> inputpaths = NULL;
		$prob -> outputpaths = NULL;
		$prob -> scoreweights = NULL;
		$prob -> submissionlimit = $obj->submissionlimit; 
		$prob -> resourcelimits = $obj->resourcelimits;
		$prob -> checker = NULL;
		$prob -> owner = $obj->owner;
		$prob -> grading_style = config::$default_grading_style;
		$prob -> compileOptions = array ();
		/* override any information if found in problem XML file. */
		$xml = get_file_name("data/problems/" . $prob->id . ".xml");
		if (empty($xml) || !is_file($xml)) return $prob; 
		
		$data = new SimpleXMLElement($xml, 0, true);
		
		foreach ($data as $key=>$value) { 
			if ($key == "resourcelimits_string") {
				$prob->resourcelimits = (string) $value;
			} else if ($key == "checker") { 
				$prob->checker = config::getFilename((string)$value);
			} else if ($key == "misc") { 
				$prob->misc = (string)$value;
			} else if ($key == "grading-style") { 
				$prob->grading_style = (string) $value;
			} else if ($key == "resource") {
				$prob->resource = (string) $value;
			} else if ($key == "author") {
				$prob->author = (string) $value;
			} else if ($key == "checker-outputs-score") {
				$prob->doesCheckerOutputScore = ($value == "true" || $value == "yes");
			}else if ($key == "resourcelimits") { 
				foreach($value as $key2 => $value2) {
					if ($key2 == "memory") 
						$prob -> memory_limit = (string) $value2; 
					else if ($key2 == "runtime") 
						$prob -> runtime_limit = (float) $value2; 
					else if ($key2 == "stack") {
						/** @todo */
					}  else if ($key2 == "file" or $key2 == "output" 
						     and !empty($value2)) { 
						$prob -> file_output_size_limit = (string)$value2;
					}
				}
			} 
			else if ($key == "compile-options") { 
				$attr = $value->attributes();
				$prob->compileOptions[ "{$attr['lang']}" ] = (string) $value; 
			}
			else if ($key == "test") { 
				if (!is_array($prob->inputpaths))  { 
					$prob->numcases = 0;
					$prob->inputpaths = array();
					$prob->outputpaths = array();
					$prob->scoreweights = array();
				}
				
				$prob->numcases ++;
				array_push($prob->inputpaths, get_file_name($value->inputpath));
				array_push($prob->outputpaths, get_file_name($value->outputpath));
				array_push($prob->scoreweights, (string) ($value->score));
			} 
		}
		
		return $prob;
	}
	
	/**
	 * Get the XML file corresponding to a problem. 
	 * @param $id the problem id 
	 * @return a string containing path to XML file.
	 */
	public static function get_problem_xml_file($id) 
	{ 
		return 	realpath(get_file_name("data/problems/" . $id . ".xml"));
	}

	/**
	 * Gets the list a list of problems.
	 * @param $offset The _index_ of the problem to start with (integer)
	 * @param $count the number of problems to pick
	 * @return An array of objects of type Problem
	 * @see ProblemTable::get_problem()
	 */
	public static function get_problem_list($owner, $offset, $count) 
	{
		$db = contestDB::get_zend_db(); 
		
		$res = $db->select()
			->where("owner = ?", $owner)
			->from(ProblemTable::tablename)
			->order('rowid');
		
		$res = $res->query();
		return $res->fetchAll(); 
	}

	/**
	 * Validate the problem XML file.
	 *
	 * @param void
	 * 
	 * @return TRUE if the file validates against the schema, FALSE o/w
	 */
	public function fsckProblem ($id)
	{
		$xml = get_file_name("data/problems/" . $id . ".xml");
		$dom = new DomDocument;
		$dom->load ($xml);
		if (!$dom->relaxNGValidate (get_file_name ("schema/problem.rng"))) 
			return false;

		/* validate that the HTML file provided can be tidied to XHTML strict */
		$opt = array("output-xhtml" => true,
                                     "add-xml-decl" => true,
                                     "doctype" => "strict");

		$tidy = tidy_parse_string(file_get_contents (
						  get_file_name ("data/problems/$id/index.html")
						  ), $opt);
		tidy_clean_repair ($tidy);
		$xhtml = tidy_get_output ($tidy);

		$dom = new DomDocument;
		$dom->loadXML ($xhtml, LIBXML_DTDLOAD);
		return $dom->validate ();
	}
}


