<?php

require_once "OpcDataTest.php";
require_once "lib/submissions.inc";

class SubmissionsTest extends OpcDataTest
{
  public function testIdNumbering ()
  {
    /* try to see if the dirs are being created */
    $this->assertEquals (get_file_name ("data/uploads/0/020"),
			 Submission::getPathToCodeFromId (20));
    $this->assertEquals (get_file_name ("data/uploads/1/000"),
			 Submission::getPathToCodeFromId (1000));
    $this->assertEquals (get_file_name ("data/uploads/1/999"),
			 Submission::getPathToCodeFromId (1999));
    $this->assertEquals (get_file_name ("data/uploads/178/456"),
			 Submission::getPathToCodeFromId (178456));
    
    assert (is_dir (get_file_name ("data/uploads/1")));
    assert (is_dir (get_file_name ("data/uploads/178")));
    
  }
}
