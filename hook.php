<?php

/**
 * Bitbucket/GitHub webhook interface.
 *
 * Based on 'Automatic Bitbucket Deploy' by Igor Lilliputten (v.151005.001 (0.0.2)):
 * https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/
 *
 * Based on 'Automated git deployment' script by Jonathan Nicoal:
 * http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Initalize:
require_once 'utils.php';
require_once 'log.php';
require_once 'deploy.php';

// Load config:
require_once 'config.php';

// Let's go:
init_logging(); // Initalize log variables
resolve_payload(); // Get posted data
parse_payload(); // Get parameters from bitbucket payload (REPO)
check_paths(); // Check repository and project paths; create them if neccessary
fetch_repository(); // Fetch or clone repository
checkout_project(); // Checkout project into target folder
log_verbose_info(); // Place verbose log information if specified in config
