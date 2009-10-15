<?php

require_once dirname (__FILE__) . "/UploadTest.php";
class UploadWithChrootTest extends UploadTest
{
	public function setUp ()
	{
		config::$chroot_dir = realpath ("./chroot");
		config::$compile_temp_directory = realpath ("./chroot/tmp");
		parent::setUp ();
	}
      	public function tearDown ()
        {
                parent::tearDown ();
                /* required for PHP < 5.3 */
                config::$chroot_dir = "";
                config::$compile_temp_directory = "/tmp";
        }

}
