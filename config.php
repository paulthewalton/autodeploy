<?php
/**
 * Config file for BitBucket Automatic Deploment.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

global $CONFIG, $PROJECTS, $REPOS_PATH;

/**
 * Directory for all your git repositories
 * * NB: This is only where the version control part of your projects will be stored (ie: commits, branches, etc.)
 */
$REPOS_PATH = '/home/<username>/repos';

/**
 * Projects array
 *
 * * Key for each repo must be the repo's full name, which can be found in the GitHub or Bitbucket URL
 * e.g. https://github.com/username/my-repo-1 -> username/my-repo-1
 * e.g. https://bitbucket.org/username/my-repo-2 -> username/my-repo-2
 */
$PROJECTS = array(
    '<repo_full_name>' => array(
        '<branch_name>' => array( // Per branch deployment configuration
            'deploy_path' => '<webroot>/path/to/deploy/branch',
            'post_hook_cmd' => '', // Shell command to execute after hook fires, optional
            'mail_to' => '', // Recipent email address for notifications, optional
        ),
        //... set up deployments for whatever branches needed
    ),
    //... configure more repos as needed
);

/**
 * General configuration array
 *
 * * It is recommended that you store your repos outside of your public website directory.
 */
$CONFIG = array(
    'git_cmd' => 'git', // Shell git command *REQUIRED*
    'log' => true, // Enable logging
    'log_file' => '', // Logging file name
    'log_clear' => false, // clear log each time
    'verbose' => false, // show debug info in log
    'project_folder_mode' => 0711, // creating folder mode
    'repo_folder_mode' => 0700, // creating folder mode
    'mail_from' => '', // The sender e-mail address for info emails
);
