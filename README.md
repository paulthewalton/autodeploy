# Bitbucket Auto Deploy

***

A quick and easy way to set up automatic deployments from your Bitbucket repository.

Based on '[Automatic Bitbucket Deploy](https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/)' by Igor Lilliputten (v.151005.001 (0.0.2))

Based on '[Automated git deployment](http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/)' script by Jonathan Nicoal

# Table of Contents

[Desired Workflow](#markdown-header-desired-workflow)

[Set up](#markdown-header-set-up)

[Configuration](#markdown-header-configuration)

* [Path Variables](#markdown-header-path-variables)

    * [Repositories Path](#markdown-header-repositories-path)

    * [Projects Path](#markdown-header-projects-path)

    * [Bitbucket Username](#markdown-header-bitbucket-username)

* [Repositories (Projects Array)](#markdown-header-repositories-projects-array)

    * [Repository Name](#markdown-header-repository-name)

    * [Repository Slug](#markdown-header-repository-slug)

    * [Branches](#markdown-header-branches)

    * [Deployment Path](#markdown-header-deployment-path)

    * [Info Emails](#markdown-header-info-emails-optional)

* [Options](#markdown-header-options)

    * [Bitbucket Username](#markdown-header-bitbucket-username_1)

    * [Repositories Path](#markdown-header-repositories-path_1)

    * [Git Command](#markdown-header-git-command)

    * [Logging](#markdown-header-logging-optional)

    * [Log File](#markdown-header-log-file-optional)

    * [Log Clearing](#markdown-header-log-clearing-optional)

    * [Verbose Logging](#markdown-header-verbose-logging-optional)

    * [Folder Mode](#markdown-header-folder-mode-optional)

    * [Mail From](#markdown-header-mail-from-optional)

* [Things to Do](#markdown-header-mail-from-optional)

* [Things to Not Do](#markdown-header-things-to-avoid)

[Troubleshooting](#markdown-header-things-to-avoid)

[Contact](#markdown-header-contact)

***

# Desired Workflow
1. Write some great code
2. Commit or merge into the `master` branch of your repository
3. Test, test, test
4. Merge `master` branch into `production` branch (or whatever you named it)
5. Bitbucket automatically deploys changes in `production` branch to live site

# Set up

1. Create a folder called "deploy" (or whatever) in the public website directory of the domain to which you want to deploy. This folder will need to contain:
    * `bitbucket-hook.php`
    * `bitbucket.php`
    * `log.php`
    * your modified `config.php`
    * your log file (default: `bitbucket.log`)
    * `index.html`
    * _TODO: .htaccess (?)_
2. Edit `config.php` and set your `$REPOSITORIES_PATH`, `$PROJECTS_PATH`, `$USER_NAME`, and `$PROJECTS`
3. For each watched repository on Bitbucket, create a webhook and point it towards your `bitbucket-hook.php` file,
  ie: `https://www.yourdomain.com/deploy/bitbucket-hook.php`
  Make sure that your webhook is triggered on 'push'.
  To edit webhooks, go to the repository's Settings > Webhooks
4. You should be good to go! Test by pushing a commit to one of the watched Bitbucket repositories.

# Configuration

All configuration is done in `config.php`, split between the path building variables (`$REPOSITORIES_PATH`, `$PROJECTS_PATH`, and `$USER_NAME`), the projects array (`$PROJECTS`), and the options array (`$CONFIG`).

## Path Variables

### Repositories Path

This is path to where you will be storing the Git repositories. It is recommended that you store your repos outside of your public website directory. I typically use a folder one level up from the public website directory, which is often the website user's home directory. *NB: This is only where the version control part of your projects will be stored (ie: commits, branches, etc.)*

```php
$REPOSITORIES_PATH = '/home/<username>/repos';
```

### Projects Path

The path to where your source code will be deployed from Bitbucket. This will be the base for all individual project targets, and may just be the public website directory. For example:

```php
$PROJECTS_PATH = '/home/<username>/public_html';
```


### Bitbucket Username

The slug for the Bitbucket user or team. This is used for building URLs, so if in doubt copy it from a Bitbucket project URL.

> ht[]()tps://bitb[]()ucket.org/**username**/repository.git

If deploying repositories owned by multiple different users and/or teams, leave this as an empty string and include the user/team names when writingthe individual repository slugs in the `$PROJECTS` array.

```php
$USER_NAME = '<user_name_slug>';
```

***

## Repositories (Projects Array)

Any repositories you want to subscribe to must be specified in the `$PROJECTS` array. This is an associative array where each repository is identified by it's "pretty" name. Each entry in the array will look something like this:

```php
$PROJECTS = array(
    //...
    '<repository_name>' => array(
        'slug'     => '<repository_slug>',
        '<branch>' => array(
            'deployPath'  => $PROJECTS_PATH.'/path/to/deploy/project',
            'postHookCmd' => '',
            'mailTo'      => 'email@example.com'
        ),
    ),
    //...
)
```

### Repository Name

Each repository is represented as an array. The key to this array is a string that **must** match the "pretty" name for the Bitbucket repository.

```php
'My Repository' => array(
    //...
),
```

### Repository Slug

Each repository has a slug that can be found in the Bitbucket project URL:

> ht[]()tps://bitb[]()uck[]()et.[]()org/username/**repository**.git

This is necessary to resolve the URL to clone the repository.

```php
'My Repository' => array(
    'slug' => 'my_repository',
    //...
),
```

If deploying repositories owned by multiple different users and/or teams, leave $USER_NAME as an empty string and include the user/team names when writing the individual repository slugs here. For example:

```php
$USER_NAME = '';

//...

$PROJECTS = array (
    'Repository A' => array(
        'slug'     => 'team_name_1/repository_a',
        'myBranch' => array(...),
    ),

    'Repository B' => array(
        'slug'     =>'team_name_2/repository_b'
        'myBranch' => array(...),
    ),
    //...
)
```

### Branches

Within each repository, you must specify each branch you wish to track. Each branch is represented as another associative array in the parent repository array, the key to which **must** match the branch name. This branch either should not, or can not, be the `master` branch, and is commonly called `production`, `dist`, or something along those lines.

```php
'My Repository' => array(
    'slug'       => 'my_repository',
    'production' => array (
        //...
    ),
    //...
),
```

It should be possible to track multiple branches from one repository, though it does make things a little more complex. Be careful, patient, and thorough if you want to try doing so.

### Deployment Path

For each branch in the repository, you will need to specify the path to where you want the source code to be checked out. Use the `$PROJECTS_PATH` for convenience.

```php
'My Repository' => array(
    'slug'       => 'my_repository',
    'production' => array (
        'deployPath' => $PROJECTS_PATH.'/my-path'
        //...
    ),
    //...
),
```

If deploying multiple branches from one repo, you will probably want to be very careful with your paths to make sure your branches aren't conflicting. For example, if your repository was a website and you wanted to set up a staging subdomain, it might look something like this:

```php
$PROJECTS_PATH = '/home/username/public_html';

//...
$PROJECTS => array (
    'My Website' => array(
        'slug'       => 'my-website',
        'production' => array (
            'deployPath' => $PROJECTS_PATH
            //...
        ),
        'dev' => array (
            'deployPath' => $PROJECTS_PATH.'/staging'
            //...
        ),
    ),
)
```

### Info Emails (Optional)

If you want, you can set an email address to recieve notifications from this script anytime this branch of the repository is deployed.

```php
'My Repository'  => array(
    'slug'       => 'my_repository',
    'production' => array (
        'deployPath' => $PROJECTS_PATH.'/my-path'
        'mailTo'     => 'me@mysite.com'
    ),
    //...
),
```

***

## Options

All the options are stored in the `$CONFIG` array.

### Bitbucket Username

This just references the `$USER_NAME` variable you may have set earlier, so feel free to just set it here instead.

```php
$CONFIG = array (
    //...
    'bitbucketUsername' => $USER_NAME,
    //...
);
```

### Repositories Path

Like above, this just references `$REPOSITORIES_PATH`, so feel free to just set it here if you want.

```php
$CONFIG = array (
    //...
    'repositoriesPath'  => $REPOSITORIES_PATH,
    //...
);
```

### Git Command

This is a string that is the Git command in your server's shell. Usually it is just `git`, but your server may use an alias.

```php
$CONFIG = array (
    //...
    'gitCommand' => 'git',
    //...
);
```

### Logging (Optional)

To enable logging, set this to `true`.

```php
$CONFIG = array (
    //...
    'log' => true,
    //...
);
```

### Log File (Optional)

The filename you want your logfile to have.

```php
$CONFIG = array (
    //...
    'logFile' => 'bitbucket.log',
    //...
);
```

It should be created automatically when the script runs, but if not try creating it manually by SSH-ing into your server:

```shell
$ ssh username@website.tld
$ cd path/to/folder
$ touch filename
```

### Log Clearing (Optional)

If you only want the log to contain the log data from the most recent execution of the script, set this to `true`.

```php
$CONFIG = array (
    //...
    'logClear' => false,
    //...
);
```

### Verbose Logging (optional)

If you want to enable verbose logging, set this to `true`.

```php
$CONFIG = array (
    //...
    'verbose' => true,
    //...
);
```

### Folder Mode (Optional)

This is the folder mode for the deployed source code directories. Default is `0711`, recommend you don't go any higher than `0755`. See [http://permissions-calculator.org/](http://permissions-calculator.org/) for more info about folder permissions modes.

```php
$CONFIG = array (
    //...
    'folderMode' => 0711,
    //...
);
```

### Mail From (Optional)

This will be the sender email address for info emails.

```php
$CONFIG = array (
    //...
    'mailFrom' => 'Automatic Bitbucket Deploy <git@bitbucket.com>',
    //...
);
```

***

## Things to Do

* **Security through obscurity**  
    Change the name of the folder containing the deployment scripts to something very hard to guess. I like to use a password generator to generate a string 20+ characters long and append it to both my chosen folder name and `bitbucket-hook.php`. Thus, the url

    `www.mysite.com/deploy/bitbucket-hook.php`

    becomes

    `www.mysite.com/deploy-9VZD78ebwHCA0iYJfkRe/bitbucket-hook-fARcXfEo7Bq1g9USQ0Pd.php`

    All you have to do is make sure that the webhook on Bitbucket points towards the new filename.

## Things to Avoid

* Make sure that repositories (ie: `my_project.git`) aren't in your public website directory, as this is a potential security risk. There are workarounds to eliminate that risk, or you could just store all your .git folders in a directory not accessible by the webserver but which can be written to by the web-user.


# Troubleshooting

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
$ git clone --mirror git@bitbucket.org:username/my_repository.git
```

Try pushing a test commit to this repository. If that works, try pushing a test commit to one of your other watched repositories. If you need to, try cloning each of them in the same way as above, and repeat.

***

# Contact

[Paul Walton](https://bitbucket.org/paulthewalton/)
