<?php

/**
 * Bitbucket webhook interface.
 *
 * Based on 'Automatic Bitbucket Deploy' by Igor Lilliputten (v.151005.001 (0.0.2)):
 * https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/
 *
 * Based on 'Automated git deployment' script by Jonathan Nicoal:
 * http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/
 */

// Initalize:
require_once('log.php');
require_once('bitbucket.php');

// Load config:
include('config.php');

// Let's go:
initLog();          // Initalize log variables
initPayload();      // Get posted data
fetchParams();      // Get parameters from bitbucket payload (REPO)
checkPaths();       // Check repository and project paths; create them if neccessary
placeVerboseInfo(); // Place verbose log information if specified in config
fetchRepository();  // Fetch or clone repository
checkoutProject();  // Checkout project into target folder
