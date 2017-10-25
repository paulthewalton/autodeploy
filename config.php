<?php

/**
 * Config file for BitBucket Automatic Deploment.
 *
 * Based on 'Automatic Bitbucket Deploy' by Igor Lilliputten (v.151005.001 (0.0.2)):
 * https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/
 *
 * Based on 'Automated git deployment' script by Jonathan Nicoal:
 * http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/
 */


// Auxiliary variables; used only for constructing $CONFIG and $PROJECTS

/**
 * The path to where you will be storing the Git repositories.
 *
 * It is recommended that you store your repos outside of your public website
 * directory. I typically use a folder one level up from the public website
 * directory, which is often the website user's home directory.
 *
 * NB: This is only where the version control part of your projects will be stored
 * (ie: commits, branches, etc.)
 */
$REPOSITORIES_PATH = '/home/<username>/repos';

/**
 * The path to where your source code will be deployed from Bitbucket.
 *
 * This will be the base for all individual project targets, and may just be the
 * public website directory.
 *
 * eg:  /home/user1/public_html
 *      /home/user1/html
 *      /home/user1/www
 */
$PROJECTS_PATH     = '/home/<username>/public_html';

/**
 * The slug for the Bitbucket user or team.
 *
 * This is used for building URLs, so if in doubt copy it from a Bitbucket
 * project URL.
 *
 * eg: https://bitbucket.org/username/repository.git
 *                           ^^^^^^^^
 *
 * If deploying repositories owned by multiple different users and/or teams,
 * leave this as an empty string and include the user/team names when writing
 * the individual repository slugs in the $PROJECTS array.
 */
$USER_NAME = '<user_name_slug>';

/**
 * List of deploying projects.
 *
 * If deploying repositories owned by multiple different users and/or teams,
 * leave $USER_NAME as an empty string and include the user/team names when
 * writing the individual repository slugs here.
 *
 * eg: 'Repository A' => array(
 *        'myBranch'  => array(...),
 *        'slug'      => 'team_name_1/repository_a',
 *      ),
 *      'Repository B'  => array(
 *        'myBranch'  => array(...),
 *        'slug'      =>'team_name_2/repository_b'
 *      ),
 *      ...
 *
 * Repository slugs can be found in Bitbucket project URLS:
 *
 * eg: https://bitbucket.org/username/repository.git
 *                                    ^^^^^^^^^^
 *
 */
$PROJECTS = array(
  '<repository_name>' => array( // The key must match a bitbucket.org repository name (ie: 'My Repository')
    'slug' => '<repository_slug>', // The key must match a bitbucket.org repository name, see above *REQUIRED*
    '<branch>' => array(      // The key must match the branch name you wish to deploy (ie: 'production', 'dist')
      'deployPath'  => $PROJECTS_PATH.'/path/to/deploy/project', // Path to deploy the individual project, *REQUIRED*
      'mailTo'      => 'email@example.com' // log email recipient, optional
    ),
  ),
);


/**
 * Base tool configuration
 */

$CONFIG = array(
  'bitbucketUsername' => $USER_NAME,
  'repositoriesPath'  => $REPOSITORIES_PATH,
  'gitCommand'        => 'git',              // Git command, *REQUIRED*
  'log'               => true,               // Enable logging, optional
  'logFile'           => 'bitbucket.log',    // Logging file name, optional
  'logClear'          => false,               // clear log each time, optional
  'verbose'           => true,               // show debug info in log, optional
  'folderMode'        => 0711,               // creating folder mode, optional, see http://permissions-calculator.org/
  'mailFrom'          => 'Automatic Bitbucket Deploy <git@bitbucket.com>', // The sender e-mail address for info emails
);