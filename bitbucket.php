<?php

/**
 * Routines for work with bitbucket server, repositories and projects.
 *
 * Based on 'Automatic Bitbucket Deploy' by Igor Lilliputten (v.151005.001 (0.0.2)):
 * https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/
 *
 * Based on 'Automated git deployment' script by Jonathan Nicoal:
 * http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/
 */


// Global variables

define('DEFAULT_PROJECT_FOLDER_MODE', 0711);
define('REPO_FOLDER_MODE', 0700);

$PAYLOAD  = array ();
$REPO     = '';
$BRANCHES = array ();

/**
 * Initialize log varaiables.
 *
 * @global array   $CONFIG
 * @global boolean $_LOG_ENABLED
 * @global string  $_LOG_FILE
 * @return void
 */
function initLog ()
{
	global $CONFIG, $_LOG_ENABLED, $_LOG_FILE;

	if ( !empty($CONFIG['log']) ) {
		$_LOG_ENABLED = true;
	}
	if ( !empty($CONFIG['logFile']) ) {
		$_LOG_FILE = $CONFIG['logFile'];
	}
	if ( !empty($CONFIG['logClear']) ) {
		_LOG_CLEAR();
	}
}

/**
 * Get posted data.
 *
 * @global object $PAYLOAD
 * @return void
 */
function initPayload ()
{
	global $PAYLOAD;

	_LOG('*** '.$_SERVER['HTTP_X_EVENT_KEY'].' #'.$_SERVER['HTTP_X_HOOK_UUID'].' ('.$_SERVER['HTTP_USER_AGENT'].')');
	_LOG('remote addr: '.$_SERVER['REMOTE_ADDR']);

	if ( isset($_POST['payload']) ) { // old method
		$PAYLOAD = $_POST['payload'];
	} else { // new method
		$PAYLOAD = json_decode(file_get_contents('php://input'));
	}

	if ( empty($PAYLOAD) ) {
		_ERROR("No payload data for checkout!");
		exit;
	}

	if ( !isset($PAYLOAD->repository->name, $PAYLOAD->push->changes) ) {
		_ERROR("Invalid payload data was received!");
		exit;
	}

	_LOG("Valid payload was received");
}

/**
 * Get parameters from delivered Bitbucket payload.
 *
 * @global object $REPO
 * @global object $PAYLOAD
 * @global array  $PROJECTS
 * @global array  $BRANCHES
 * @return void
 */
function fetchParams ()
{
	global $REPO, $PAYLOAD, $PROJECTS, $BRANCHES;

	// Get repository name:
	$REPO = $PAYLOAD->repository->name;

	// Checks if repository delivered is listed in config
	if ( empty($PROJECTS[$REPO]) ) {
		_ERROR("Not found repository config for '$REPO'!");
		exit;
	}

	foreach ( $PAYLOAD->push->changes as $change ) {
		if ( is_object($change->new) && $change->new->type == "branch" &&
			isset($PROJECTS[$REPO][$change->new->name]) ) {
			// Create branch name for checkout
			array_push($BRANCHES, $change->new->name);
			_LOG("Changes in branch '".$change->new->name."' were fetched");
		}
	}

	if ( empty($BRANCHES) ) {
		_LOG("Nothing to update");
	}
}

/**
 * Check repository and project paths, creates them if necessary.
 *
 * @global object $REPO
 * @global array  $CONFIG
 * @global array  $PROJECTS
 * @global array  $BRANCHES
 * @return void
 */
function checkPaths ()/* Check repository and project paths; create them if neccessary */
{
	global $REPO, $CONFIG, $PROJECTS, $BRANCHES;

	// Check for repositories folder path; create if absent
	if ( !is_dir($CONFIG['repositoriesPath']) ) {
		$mode = REPO_FOLDER_MODE;
		if ( mkdir($CONFIG['repositoriesPath'],$mode,true) ) {
			_LOG("Creating repository folder '".$CONFIG['repositoriesPath']." (".decoct($mode).") for '$REPO'");
		}
		else {
			_ERROR("Error creating repository folder '".$CONFIG['repositoriesPath']." for '$REPO'! Exiting.");
			exit;
		}
	}

	// Create folder if absent for each pushed branch
	foreach ( $BRANCHES as $branchName ) {
		if ( !is_dir($PROJECTS[$REPO][$branchName]['deployPath']) ) {
			$mode = ( !empty($CONFIG['folderMode']) ) ? $CONFIG['folderMode'] : DEFAULT_PROJECT_FOLDER_MODE;
			if ( mkdir($PROJECTS[$REPO][$branchName]['deployPath'],$mode,true) ) {
				_LOG("Creating project folder '".$PROJECTS[$REPO][$branchName]['deployPath'].
					" (".decoct($mode).") for '$REPO' branch '$branchName'");
			}
			else {
				_ERROR("Error creating project folder '".$PROJECTS[$REPO][$branchName]['deployPath'].
					" for '$REPO' branch '$branchName'! Exiting.");
				exit;
			}
		}
	}
}

/**
 * Logs verbose information.
 *
 * Only if specified in config.php
 *
 * @global object $REPO
 * @global array  $CONFIG
 * @global array  $BRANCHES
 * @return void
 */
function placeVerboseInfo ()/* Place verbose log information -- if specified in config */
{
	global $REPO, $CONFIG, $BRANCHES;

	if ( $CONFIG['verbose'] ) {
		_LOG_VAR('CONFIG',$CONFIG);
		_LOG_VAR('REPO',$REPO);
		_LOG_VAR('repoPath',$CONFIG['repositoriesPath'].'/'.$REPO.'.git/');
		_LOG_VAR('BRANCHES',$BRANCHES);
	}
}

/**
 * Fetch or clone repository.
 *
 * @global object $REPO
 * @global array  $CONFIG
 * @return void
 */
function fetchRepository ()/* Fetch or clone repository */
{
	global $REPO, $CONFIG;

	// Compose current repository path
	$repoPath = $CONFIG['repositoriesPath'].'/'.$REPO.'.git/';

	// If repository or repository folder are absent then clone full repository
	if ( !is_dir($repoPath) || !is_file($repoPath.'HEAD') ) {
		_LOG("Absent repository for '$REPO', cloning");
		system('cd '.$CONFIG['repositoriesPath'].' && '.$CONFIG['gitCommand'].
			' clone --mirror git@bitbucket.org:'.$CONFIG['bitbucketUsername'].'/'.$REPO.'.git',
			$status);
		if ( $status !== 0 ) {
			_ERROR('Cannot clone repository git@bitbucket.org:'.
				$CONFIG['bitbucketUsername'].'/'.$REPO.'.git');
			exit;
		}
	}
	// Else fetch changes
	else {
		_LOG("Fetching repository '$REPO'");
		system('cd '.$repoPath.' && '.$CONFIG['gitCommand'].' fetch', $status);
		if ( $status !== 0 ) {
			_ERROR("Cannot fetch repository '$REPO' in '$repoPath'!");
			exit;
		}
	}
}

/**
 * Checkout project into target folder.
 *
 * @global object $REPO
 * @global array  $CONFIG
 * @global array  $PROJECTS
 * @global array  $BRANCHES
 * @return void
 */
function checkoutProject ()
{
	global $REPO, $CONFIG, $PROJECTS, $BRANCHES;

	// Compose current repository path
	$repoPath = $CONFIG['repositoriesPath'].'/'.$REPO.'.git/';

	// Checkout project files
	foreach ( $BRANCHES as $branchName ) {
		system('cd '.$repoPath.' && GIT_WORK_TREE='.$PROJECTS[$REPO][$branchName]['deployPath']
			.' '.$CONFIG['gitCommand'].' checkout -f '.$branchName, $status);
		if ( $status !== 0 ) {
			_ERROR("Cannot checkout branch '$branchName' in repo '$REPO'!");
			exit;
		}

		if ( !empty($PROJECTS[$REPO][$branchName]['postHookCmd']) ) {
			system('cd '.$PROJECTS[$REPO][$branchName]['deployPath'].' && '.$PROJECTS[$REPO][$branchName]['postHookCmd'],
				$status);
			if ( $status !== 0 ) {
				_ERROR("Error in post hook command for branch '$branchName' in repo '$REPO'!");
				exit;
			}
		}

		// Log the deployment
		$hash = rtrim(shell_exec('cd '.$repoPath.' && '.$CONFIG['gitCommand']
			.' rev-parse --short '.$branchName));

		$logLine = "Branch '$branchName' was deployed in '".$PROJECTS[$REPO][$branchName]['deployPath'].
			"', commit #$hash";

		_LOG($logLine);

		if ( !empty($PROJECTS[$REPO][$branchName]['mailTo']) ) {
			$mailto = $PROJECTS[$REPO][$branchName]['mailTo'];
			$headers = 'From: '. $CONFIG['mailFrom']. "\r\n";
			mail($mailto, $subject, $logLine, $headers);
			_LOG("Sent e-mail to ".$mailto);
		}
	}
}
