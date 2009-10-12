<?php

require_once dirname (__FILE__) . "/PreTest.php";
class PreWithChrootTest extends PreTest
{
	public function setUp ()
	{
		config::$chroot_dir = realpath ("./chroot");
		config::$compile_temp_directory = realpath ("./chroot/tmp");
		parent::setUp ();
	}
}
