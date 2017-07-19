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

// TODO: explain how/why to write paths
/**
 * 
 */
$REPOSITORIES_PATH = '/home/<domain>/repos';

/**
 * 
 */
$PROJECTS_PATH     = '/home/<domain>/public_html';

/**
 * 
 */
$USER_NAME = '<user_name_slug>';

// Base tool configuration:
$CONFIG = array(
  'bitbucketUsername' => $USER_NAME,
  'gitCommand'        => 'git',              // Git command, *REQUIRED*
  'repositoriesPath'  => $REPOSITORIES_PATH,
  'log'               => true,               // Enable logging, optional
  'logFile'           => 'bitbucket.log',    // Logging file name, optional
  'logClear'          => false,               // clear log each time, optional
  'verbose'           => true,               // show debug info in log, optional
  'folderMode'        => 0700,               // creating folder mode, optional
  'mailFrom'          => 'Automatic Bitbucket Deploy <git@bitbucket.com>', // The sender e-mail address for info emails
);

//==============================================================================
// Projects
//==============================================================================

// List of deploying projects:
$PROJECTS = array(
  '<repository>' => array( // The key must match a bitbucket.org repository name slug
    '<branch>' => array(      // The key must match the branch name you wish to deploy (ie: 'production', 'dist')
      'deployPath'  => $PROJECTS_PATH.'/path/to/deploy/project', // Path to deploy project, *REQUIRED*
      'postHookCmd' => '',               // command to execute after deploy, optional
      'mailTo'      => 'pwalton@live.ca' // log email recipient, optional
    ),
  ),
);
