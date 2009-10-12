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
 * @file   common.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

function  runandgetoutput($cmd, &$output) {
  $f = popen ( $cmd, "r" ) ;
  if ( ! $f ) return 1;
  $output = "" ;
  $output = stream_get_contents($f);
  return pclose ( $f );

}
/**
 * Run a command and get both the output and error
 * streams
 * 
 * @param $cmd string - command
 * @param[out] $output  string(reference) - the output
 * @param[out] $err string(reference) - the input
 */
function runandgetoutputerr( $cmd, &$output, &$err ) {
  if (defined('DEBUG')) echo "\ncommand called: $cmd \n";
  $descriptorspec = array(
						  0 => array("pipe", "r"),
						  1 => array("pipe", "w"),  
						  2 => array("pipe", "w")  
						  );


  $process = proc_open($cmd, $descriptorspec, $pipes);
  
  if ( ! $process ) return 1 ;

  /*
   * dangerously blockable calls, don't use for very large 
   * output.
   */
  $err    = stream_get_contents($pipes[2]);
  $output = stream_get_contents($pipes[1]) ;
  fclose($pipes[0]) ;
  fclose($pipes[1]);
  fclose($pipes[2]) ;
  
  $ret = proc_close($process);
 
  return $ret ;
}


