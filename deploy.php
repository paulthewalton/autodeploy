<?php
/**
 * Routines for working with and deploying remote repositories
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('DEFAULT_PROJECT_FOLDER_MODE', 0755);
define('DEFAULT_REPO_FOLDER_MODE', 0700);

$PAYLOAD = array();
$BRANCHES = array();
$REPO = '';
$HOST = '';

/**
 * Initialize log variables.
 *
 * @global array   $CONFIG
 * @global boolean $_LOG_ENABLED
 * @global string  $_LOG_FILE
 * @return void
 */
function init_logging()
{
    global $CONFIG, $_LOG_ENABLED, $_LOG_FILE;

    $_LOG_ENABLED = !empty($CONFIG['log']);
    $_LOG_FILE = resolve($CONFIG['log_file'], 'deployment.log');
    if (!empty($CONFIG['log_clear'])) {
        _LOG_CLEAR();
    }
}

/**
 * Get posted data.
 *
 * @global object $PAYLOAD
 * @global string $HOST
 * @return void
 */
function resolve_payload()
{
    global $PAYLOAD, $HOST;

    if ($_SERVER['HTTP_USER_AGENT'] === 'Bitbucket-Webhooks/2.0') {
        $HOST = 'bitbucket.org';
        _LOG('*** ' . $_SERVER['HTTP_X_EVENT_KEY'] . ' #' . $_SERVER['HTTP_X_HOOK_UUID'] . ' (' . $_SERVER['HTTP_USER_AGENT'] . ')');
    } else if (str_starts_with($_SERVER['HTTP_USER_AGENT'], 'GitHub-Hookshot/')) {
        $HOST = 'github.com';
        _LOG('*** ' . $_SERVER['HTTP_X_GITHUB_EVENT'] . ' #' . $_SERVER['HTTP_X_GITHUB_DELIVERY'] . ' (' . $_SERVER['HTTP_USER_AGENT'] . ')');
    } else {
        _ERROR('Access attempted by unauthorized user-agent!');
        exit;
    }
    _LOG('remote addr: ' . $_SERVER['REMOTE_ADDR']);

    if (isset($_POST['payload'])) {
        $PAYLOAD = $_POST['payload'];
    } else {
        $PAYLOAD = json_decode(file_get_contents('php://input'));
    }

    if (empty($PAYLOAD)) {
        _ERROR("No payload data for checkout!");
        exit;
    }

    if (!isset($PAYLOAD->repository->full_name) && !(isset($PAYLOAD->commits) || isset($PAYLOAD->push->changes))) {
        _ERROR("Invalid payload data was received!");
        exit;
    }

    _LOG("Valid payload was received");
}

/**
 * Get parameters from delivered payload.
 *
 * @global string $REPO
 * @global object $PAYLOAD
 * @global array  $PROJECTS
 * @global array  $BRANCHES
 * @return void
 */
function parse_payload()
{
    global $REPO, $PAYLOAD, $PROJECTS, $BRANCHES;

    // Get repository name:
    $REPO = $PAYLOAD->repository->full_name;

    // Checks if repository delivered is listed in config
    if (empty($PROJECTS[$REPO])) {
        _ERROR("Not repository config found for '$REPO'!");
        exit;
    }

    if (isset($PAYLOAD->push->changes)) {
        // Bitbucket Webhooks 2.0
        foreach ($PAYLOAD->push->changes as $change) {
            if (is_object($change->new) && $change->new->type == "branch" &&
                isset($PROJECTS[$REPO][$change->new->name])) {
                // Collect branch names for checkout
                array_push($BRANCHES, $change->new->name);
                _LOG("Changes in branch '" . $change->new->name . "' will be fetched");
            }
        }
    } else if (isset($PAYLOAD->ref)) {
        // GitHub
        $branch_name = substr($PAYLOAD->ref, strlen('refs/head/') + 1);
        if (isset($PROJECTS[$REPO][$branch_name])) {
            // Collect branch name for checkout
            array_push($BRANCHES, $branch_name);
            _LOG("Changes in branch '$branch_name' will be fetched");
        }
    }

    if (empty($BRANCHES)) {
        _LOG("Nothing to update");
    }
}

/**
 * Check repository and project paths, creates them if necessary.
 *
 * @global string $REPO
 * @global array  $CONFIG
 * @global array  $PROJECTS
 * @global array  $BRANCHES
 * @return void
 */
function check_paths()
{
    global $REPO, $CONFIG, $PROJECTS, $BRANCHES, $REPOS_PATH;

    // Check for repositories folder path; create if absent
    $base_repo_path = escapeshellcmd($REPOS_PATH);
    if (!is_dir($base_repo_path)) {
        $repo_mode = resolve($CONFIG['repo_folder_mode'], DEFAULT_REPO_FOLDER_MODE);
        if (mkdir($base_repo_path, $repo_mode, true)) {
            _LOG("Creating repository folder '$base_repo_path' (" . decoct($repo_mode) . ") for '$REPO'");
        } else {
            _ERROR("Error creating repository folder '$base_repo_path' for '$REPO'! Exiting.");
            exit;
        }
    }

    // Create folder if absent for each pushed branch
    foreach ($BRANCHES as $branch_name) {
        $deploy_path = escapeshellcmd($PROJECTS[$REPO][$branch_name]['deploy_path']);
        if (!is_dir($deploy_path)) {
            $project_mode = resolve($CONFIG['project_folder_mode'], DEFAULT_PROJECT_FOLDER_MODE);
            if (mkdir($deploy_path, $project_mode, true)) {
                _LOG("Creating project folder '$deploy_path' (" . decoct($project_mode) . ") for '$REPO' branch '$branch_name'");
            } else {
                _ERROR("Error creating project folder '$deploy_path' for '$REPO' branch '$branch_name'! Exiting.");
                exit;
            }
        }
    }
}

/**
 * Logs verbose information.
 * * Only if specified in config.php
 *
 * @global string $REPO
 * @global array  $CONFIG
 * @global array  $BRANCHES
 * @global array  $PROJECTS
 * @return void
 */
function log_verbose_info()
{
    global $REPO, $CONFIG, $BRANCHES, $PROJECTS;

    if ($CONFIG['verbose']) {
        _LOG_VAR('REPO', $REPO);
        _LOG_VAR('BRANCHES', $BRANCHES);
        _LOG_VAR('PROJECTS', $PROJECTS);
        _LOG_VAR('CONFIG', $CONFIG);
    }
}

/**
 * Fetch or clone repository.
 *
 * @global string $REPO
 * @global array  $CONFIG
 * @global array  $PROJECTS
 * @global string $HOST
 * @global string $REPOS_PATH
 * @return void
 */
function fetch_repository()
{
    global $REPO, $CONFIG, $PROJECTS, $HOST, $REPOS_PATH;

    // Compose current repository path
    $base_repo_path = escapeshellcmd($REPOS_PATH);
    $git_path = "$REPO.git";
    $repo_path = escapeshellcmd($REPOS_PATH . "/$git_path/");

    // If repository or repository folder are absent then clone full repository
    if (!is_dir($repo_path) || !is_file($repo_path . 'HEAD')) {
        _LOG("Absent repository for '$REPO', cloning");
        $clone_repo = escapeshellcmd($CONFIG['git_cmd'] . " clone --mirror git@$HOST:$git_path $git_path");
        system("cd $base_repo_path && $clone_repo", $status);
        if ($status !== 0) {
            _ERROR("Cannot clone repository git@$HOST:$git_path");
            exit;
        }
    }
    // Else fetch changes
    else {
        _LOG("Fetching repository '$REPO'");
        $fetch_repo = escapeshellcmd($CONFIG['git_cmd'] . ' fetch');
        system("cd $repo_path && $fetch_repo", $status);
        if ($status !== 0) {
            _ERROR("Cannot fetch repository '$REPO' in '$repo_path'!");
            exit;
        }
    }
}

/**
 * Checkout project into target folder.
 *
 * @global string $REPO
 * @global array  $CONFIG
 * @global array  $PROJECTS
 * @global array  $BRANCHES
 * @global array  $REPOS_PATH
 * @return void
 */
function checkout_project()
{
    global $REPO, $CONFIG, $PROJECTS, $BRANCHES, $REPOS_PATH;

    // Compose current repository path
    $repo_path = escapeshellcmd($REPOS_PATH . "/$REPO.git/");

    // Checkout project files
    foreach ($BRANCHES as $branch_name) {
        $deploy_path = escapeshellcmd($PROJECTS[$REPO][$branch_name]['deploy_path']);
        $checkout_repo = escapeshellcmd($CONFIG['git_cmd'] . " checkout -f $branch_name");
        system("cd $repo_path && GIT_WORK_TREE=$deploy_path $checkout_repo", $status);
        if ($status !== 0) {
            _ERROR("Cannot checkout branch '$branch_name' in repo '$REPO' into '$deploy_path'");
            _LOG_VAR("CMD: ", "cd $repo_path && GIT_WORK_TREE=$deploy_path $checkout_repo");
            _ERROR("CHECKOUT STATUS: $status");
            exit;
        }

        // Log the deployment
        $hash = rtrim(shell_exec("cd $repo_path && " . escapeshellcmd($CONFIG['git_cmd'] . " rev-parse --short $branch_name")));
        $log_line = "Branch '$branch_name' was deployed in '$deploy_path', commit #$hash";
        _LOG($log_line);
        // Notify by email if configured
        if (!empty($PROJECTS[$REPO][$branch_name]['mail_to'])) {
            $mail_to = $PROJECTS[$REPO][$branch_name]['mail_to'];
            $headers = 'From: ' . $CONFIG['mail_from'] . "\r\n";
            mail($mail_to, $subject, $log_line, $headers);
            _LOG("Sent e-mail to " . $mail_to);
        }
    }
}
