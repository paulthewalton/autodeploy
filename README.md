# Auto Deploy

A quick and easy way to set up automatic deployments from your GitHub and Bitbucket repositories.

Based on '[Automatic Bitbucket Deploy](https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/)' by Igor Lilliputten (v.151005.001 (0.0.2))

Based on '[Automated git deployment](http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/)' script by Jonathan Nicoal

## Table of Contents

* [Desired Workflow](#user-content-desired-workflow)
* [Set up](#user-content-set-up)
* [Configuration](#user-content-configuration)
  * [Repositories Path](#user-content-repositories-path)
  * [Projects](#user-content-projects)
  * [Options](#user-content-options)
* [Things to Do](#user-content-things-to-do)
* [Things to Avoid](#user-content-things-to-avoid)
* [Troubleshooting](#user-content-troubleshooting)


## Desired Workflow
1. Write some great code
2. Commit or merge into a specific branch of your repository and push
3. Bitbucket or GitHub automatically deploys changes in that branch to your live/staging/whatever environment

## Installation

SSH into your server and create an ssh key, leaving the password empty:

```shell
$ ssh username@mysite.com # log in
$ cd ~/.ssh
$ ssh-keygen -t rsa -b 4096 -C "autodeploy"
```

When prompted either accept the default key name (id_rsa) or give your key a unique name. Press enter when asked for a passphrase, which will generate a passwordless key. Usually this isn't recommended, but we need our script to be able to connect to the remote server without a passphrase.

Next, copy the contents of your public key, which can be retrieved by running:

```shell
$ cat ~/.ssh/<filename>.pub # <filename> is what you put during the generation process, or defaults to id_rsa
```

For each watched repository:
* Create a webhook and point it towards your `hook.php` file,
  ie: `https://www.yourdomain.com/deploy/hook.php`
  Make sure that your webhook is triggered on 'push'.
  To edit webhooks, go to the repository's _Settings > Webhooks_.
* Add either a Deploy key (GitHub) or an Access key (Bitbucket) to your repo, and paste in the contents of the public key that you made.

Back on your server, edit your `~/.ssh/config` file to add bitbucket.org or github.com as a host:

```shell
$ touch ~/.ssh/config # create if the file doesn't exist
$ nano ~/.ssh/config # open to edit, you don't have to use nano if you have another editor available that you prefer
```

Then, in `~/.ssh/config`:

```
Host github.com
    IdentityFile ~/.ssh/<your_private_key_file>
```

This ensures that the correct key is used when connecting by SSH to target host.

Clone this repo into a folder called "deploy" (or whatever) in the public website directory of the domain to which you want to deploy.

```shell
$ cd ~/public_html
$ git clone https://github.com/paulthewalton/autodeploy.git deploy
```

Edit `config.php` and set your `$REPOS_PATH` and `$PROJECTS`, and any of the available options.


Now you should be good to go! Test by pushing a commit to one of the watched repositories.

***

## Configuration

All configuration is done in `config.php`, split between the repositories directory (`$REPOS_PATH`), the projects array (`$PROJECTS`), and the options array (`$CONFIG`).

### Repositories Path

This is directory where you will be storing the Git repositories. It is recommended that you store your repos outside of your public website directory. I typically use a folder one level up from the public webroot, which is often the website user's home directory. *NB: This is only where the version control part of your projects will be stored (ie: commits, branches, etc.)*

```php
$REPOS_PATH = '/home/<username>/repos';
```

### Projects

Any repositories you want to subscribe to must be specified in the `$PROJECTS` array. Each entry in the array will look something like this:

```php
$PROJECTS = array(
    //...
    '<repo_full_name>' => array(
        '<branch_name>' => array(
            'deploy_path'  => '<path_to_webroot>/path/to/deploy/project',
            'mail_to'      => 'email@example.com'
        ),
       //... set up deployments for whatever branches needed
    ),
    //...
)
```

#### Repository Full Name (string)

The key for each repo in $PROJECTS **must** be the "full_name" of the repo, which can be found in the repository URL as the first 2 segments after the domains:

> ht[]()tps://github.com/**username/my-cool-code**  
> ht[]()tps://bitbucket.org/**username/my-cool-code**

```php
$PROJECTS = array(
   'username/my-cool-code' => array(
       // set up My Cool Code repository
   ),
);
```

#### Branch Name (string)

Within each repository, you must specify each branch you wish to track. The key for each branch **must** match the branch name.

```php
$PROJECTS = array(
  'username/my-cool-code' => array(
      'production' => array (
          //... set up production branch deployment
      ),
  ),
);
```

#### `deploy_path` (string)

For each branch in the repository, you will need to specify the absolute path to where you want the source code to be checked out.

```php
$PROJECTS = array(
  'username/my-cool-code' => array(
      'production' => array (
          'deploy_path' => '<path_to_webroot>/path/to/deploy/project'
          //...
      ),
  ),
);
```

#### `mail_to` (string)

If you want, you can set an email address to recieve notifications from this script anytime this branch of the repository is deployed*.

```php
$PROJECTS = array(
  'username/my-cool-code' => array(
      'production' => array (
          'deploy_path' => '<path_to_webroot>/path/to/deploy/project'
          'mail_to'     => 'me@mysite.com'
      ),
      //...
  ),
  //...
);
```

_* This might not work_

### Options

All the options are stored in the `$CONFIG` array.

```php
$CONFIG = array(
    'git_cmd' => 'git',
    'log' => true,
    'log_file' => 'deployment.log',
    'log_clear' => false,
    'verbose' => false, 
    'project_folder_mode' => 0711,
    'repo_folder_mode' => 0700,
    'mail_from' => '',
);
```

#### `repos_path` (string) **required*

This is path to where you will be storing the Git repositories. It is recommended that you store your repos outside of your public website directory. I typically use a folder one level up from the public website directory, which is often the website user's home directory. *NB: This is only where the version control part of your projects will be stored (ie: commits, branches, etc.)*

```php
$CONFIG = array (
    //...
    'repos_path'  => '/home/<username>/repos',
    //...
);
```

#### `git_cmd` (string) **

This is a string that is the Git command in your server's shell. Usually it is just `git`, but your server may use an alias.

```php
$CONFIG = array (
    //...
    'git_cmd' => 'git',
    //...
);
```

#### `log` (boolean)

To enable logging, set this to `true`.

```php
$CONFIG = array (
    //...
    'log' => true,
    //...
);
```

#### `log_file` (string)

The filename you want your logfile to have. If not set but logging is enabled, will default to `deployment.log`

```php
$CONFIG = array (
    //...
    'log_file' => 'deployment.log',
    //...
);
```

#### `log_clear` (boolean)

If you only want the log to contain the log data from the most recent execution of the script, set this to `true`.

```php
$CONFIG = array (
    //...
    'log_clear' => false,
    //...
);
```

#### `verbose` (boolean)

Setting this to `true` will enable more verbose logging.

```php
$CONFIG = array (
    //...
    'verbose' => true,
    //...
);
```

#### `project_folder_mode` (integer)

This is the folder mode for the deployed source code directories. Default is `0711`, recommend you don't go any higher than `0755`. See [http://permissions-calculator.org/](http://permissions-calculator.org/) for more info about folder permissions modes.

```php
$CONFIG = array (
    //...
    'project_folder_mode' => 0711,
    //...
);
```

#### `repo_folder_mode` (integer)

This is the folder mode for the version control directories. Default is `0700`, recommend you don't go any higher than `0711`. See [http://permissions-calculator.org/](http://permissions-calculator.org/) for more info about folder permissions modes.

```php
$CONFIG = array (
    //...
    'repo_folder_mode' => 0700,
    //...
);
```

#### `mail_from` (string)

This will be the sender email address for info emails.

```php
$CONFIG = array (
    //...
    'mail_from' => 'Automatic Deploy <noreply@example.org>',
    //...
);
```

***

## Things to Do

* **Security through obscurity**  
    Change the name of the folder containing the deployment scripts to something very hard to guess. I like to use a password generator to generate a string of 20+ characters and append it to both my chosen folder name and `hook.php`. Thus, the url

    > `www.mysite.com/deploy/hook.php`

    becomes

    > `www.mysite.com/deploy-9VZD78ebwHCA0iYJfkRe/hook-fARcXfEo7Bq1g9USQ0Pd.php`

    All you have to do is make sure that the webhook on Bitbucket and/or GitHub points towards the new filename.

## Things to Avoid

* Make sure that repositories (ie: `my_project.git`) aren't in your public website directory, as this is a potential security risk. There are workarounds to eliminate that risk, or you could just store all your .git folders in a directory not accessible by the webserver but which can be written to by the web-user.


## Troubleshooting

Things to try:

### Do the first clone manually

Sometimes you may have to do the first clone yourself. SSH into your server and navigate to the folder where you want to store the git repositories.

```shell
$ ssh username@website.tld  
$ cd /home/username/repos  
```

If the script was unable to create that folder, (very possible), create it yourself

```shell
$ mkdir /home/username/repos && cd /home/username/repos
```

Next, clone a bare copy of the repo. You may have to do this with each repo, but try it with just one first.

```shell
$ git clone --mirror git@bitbucket.org:username/my_repository.git username/my_repository.git
```

Try pushing a test commit to this repository. If that works, try pushing a test commit to one of your other watched repositories. If you need to, try cloning each of them in the same way as above, and repeat.

