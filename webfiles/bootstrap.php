<?php

error_reporting (E_ALL);
require_once dirname(__FILE__) . "/App.php";

function main ()
{
  $app = new App();
  $app->bootstrap();
  $app->setupErrorHandling();
  $app->dispatch();
}

main ();

