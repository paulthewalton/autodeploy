<?php

/**
 * Logging module.
 *
 * Based on 'Automatic Bitbucket Deploy' by Igor Lilliputten (v.151005.001 (0.0.2)):
 * https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/
 *
 * Based on 'Automated git deployment' script by Jonathan Nicoal:
 * http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/
 */


// Global variables

$_LOG_FILE    = 'log.txt'; // default log file name
$_LOG_ENABLED = true;      // set to 'true' for enabling logging

/**
 *
 * Clears preexisting log file.
 *
 * @return void
 */
function _LOG_CLEAR ()
{
	global $_LOG_FILE;

	if ( !empty($GLOBALS['_LOG_ENABLED']) ) {
		// file_put_contents($GLOBALS['_LOG_FILE'], "", LOCK_EX);
		// flush();
		if ( is_file($_LOG_FILE) ) {
			unlink($_LOG_FILE);
		}
	}
}

/**
 * Writes a string to the log file.
 *
 * @param string $s
 * @return void
 */
function _LOG ($s)
{
	if ( !empty($GLOBALS['_LOG_ENABLED']) ) {
		$datetime = date('Y.m.d H:i:s');
		file_put_contents($GLOBALS['_LOG_FILE'], $datetime."\t".$s."\n", FILE_APPEND | LOCK_EX);
		flush();
	}
}

/**
 * Writes a string and a value to the log file.
 *
 * @param string $s
 * @param mixed  $p
 * @return void
 */
function _LOG_VAR ($s,$p)
{
	_LOG($s.': '.print_r($p,true));
}

function _ERROR ($s)
{
	_LOG('ERROR: '.$s);
}
