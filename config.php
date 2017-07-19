<?php

/**
 * Config file for BitBucket hooks.
 *
 * Based on 'Automatic Bitbucket Deploy' by Igor Lilliputten (v.151005.001 (0.0.2)):
 * https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/
 *
 * Based on 'Automated git deployment' script by Jonathan Nicoal:
 * http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/
 */


// Auxiliary variables, used only for constructing $CONFIG and $PROJECTS

$REPOSITORIES_PATH = '/home/paulwalt/repos';
$PROJECTS_PATH     = '/home/paulwalt/public_html';

// Base tool configuration:
$CONFIG = array(
  'bitbucketUsername' => 'refresh_conf',    // The username or team name where the repository is located on bitbucket.org, *REQUIRED*
  'gitCommand'        => 'git',              // Git command, *REQUIRED*
  'repositoriesPath'  => $REPOSITORIES_PATH, // Folder containing all repositories, *REQUIRED*
  'log'               => true,               // Enable logging, optional
  'logFile'           => 'bitbucket.log',    // Logging file name, optional
  'logClear'          => false,               // clear log each time, optional
  'verbose'           => true,               // show debug info in log, optional
  'folderMode'        => 0700,               // creating folder mode, optional
  'mailFrom'          => 'Automatic Bitbucket Deploy <git@bitbucket.com>', // The sender e-mail address for info emails
);

// List of deploying projects:
$PROJECTS = array(
  'refresh_theme' => array( // The key MUST match a bitbucket.org repository name
    'branch' => array(
      'deployPath'  => $PROJECTS_PATH.'/refresh/wordpress/wp-content/themes/refresh_theme', // Path to deploy project, *REQUIRED*
      'postHookCmd' => '',               // command to execute after deploy, optional
      'mailTo'      => 'pwalton@live.ca' // log email recipient, optional
    ),
  ),
);
