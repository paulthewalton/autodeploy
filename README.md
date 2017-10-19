# Bitbucket Auto Deploy #

A quick and easy way to set up automatic deployments from your Bitbucket repository.

* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### Set up ###

TODO: Finish readme

* Edit `config.php` and set your `$REPOSITORIES_PATH`, `$PROJECTS_PATH`, `$USER_NAME`, and `$PROJECTS`
* Create a folder called "deploy" (or whatever) in the public website directory of the domain to which you want to deploy. This folder will need to contain:
    * `bitbucket-hook.php`
    * `bitbucket.php`
    * `log.php`
    * your modified `config.php`
    * your log file (default: `bitbucket.log`)
    * `index.html`
* For each watched repository on Bitbucket, create a webhook and point it towards your `bitbucket-hook.php` file,
    ie: `https://www.yourdomain.com/deploy/bitbucket-hook.php`
    Make sure that your webhook is triggered on 'push'.

* Configuration
* Dependencies
* Database configuration
* How to run tests
* Deployment instructions

### Contribution guidelines ###

* Writing tests
* Code review
* Other guidelines

### Who do I talk to? ###

* Repo owner or admin
* Other community or team contact